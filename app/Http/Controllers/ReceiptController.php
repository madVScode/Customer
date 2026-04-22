<?php
namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Trip;
use App\Models\Booking;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReceiptController extends Controller
{
    // ✅ admin_system/tester → semua, owner_business → company sendiri
    private function getUserCompanyIds(): array
{
    $user = Auth::user();

    if ($user->role === 'admin_system') {
        return Company::pluck('id')->toArray();
    }

    // ✅ Tester — hadkan kepada demo company sahaja
    // Data baru yang tester cipta dalam demo company boleh delete (is_protected=0)
    // Hanya seeded demo data (is_protected=1) yang tidak boleh delete
    if ($user->role === 'tester') {
    return $user->company_id ? [$user->company_id] : [];
}

    if ($user->role === 'owner_business' && !empty($user->company_id)) {
        return [$user->company_id];
    }

    return Company::where('user_id', $user->id)->pluck('id')->toArray();
}

    public function index()
    {
        $companyIds = $this->getUserCompanyIds();

        $receipts = Receipt::with(['customer', 'customer.state', 'company', 'trip', 'booking'])
            ->whereIn('company_id', $companyIds)
            ->whereNull('deleted_at')
            ->orderBy('issued_at', 'desc')
            ->get()
            ->map(fn($r) => $this->formatReceipt($r));

        return response()->json(['success' => true, 'data' => $receipts]);
    }

    public function customersWithBookings(Request $request)
{
    $companyIds = $this->getUserCompanyIds();
    $companyId  = $request->query('company_id');

    if (!$companyId || !in_array($companyId, $companyIds)) {
        return response()->json(['success' => false, 'data' => []]);
    }

    $customers = Customer::where('company_id', $companyId)
        ->whereNull('deleted_at')
        ->whereHas('bookings', function ($q) {
            $q->whereNull('deleted_at')
              ->where('booking_status', 'confirmed'); // ✅ confirmed sahaja
        })
        ->with(['bookings' => function ($q) {
            $q->whereNull('deleted_at')
              ->where('booking_status', 'confirmed') // ✅ confirmed sahaja
              ->with('trip')
              ->orderBy('created_at', 'desc');
        }])
        ->get()
        ->map(function ($customer) {
            return [
                'unique_id' => $customer->unique_id,
                'name'      => $customer->name,
                'ic'        => $customer->ic,
                'phone'     => $customer->phone,
                'bookings'  => $customer->bookings->map(function ($b) {
                    return [
                        'id'         => $b->id,
                        'booking_no' => $b->booking_no ?? $b->ref_id ?? ('BK-' . $b->id),
                        'trip_id'    => $b->trip_id,
                        'trip_name'  => $b->trip?->location ?? '—',
                        'trip_ref'   => $b->trip?->ref_id   ?? '—',
                        'date_from'  => $b->trip?->date_from ?? null,
                        'date_to'    => $b->trip?->date_to   ?? null,
                        'status'     => $b->booking_status   ?? 'confirmed',
                        'pax_adult'  => $b->pax_adult  ?? 0,
                        'pax_child'  => $b->pax_child  ?? 0,
                        'pax_baby'   => $b->pax_baby   ?? 0,
                    ];
                })->values(),
            ];
        });

    return response()->json(['success' => true, 'data' => $customers]);
}

    public function store(Request $request)
    {
        $companyIds = $this->getUserCompanyIds();

        $validated = $request->validate([
            'customer_id'    => 'required|string',
            'company_id'     => 'required|string|in:' . implode(',', $companyIds),
            'trip_id'        => 'required|integer|exists:trips,id',
            'booking_id'     => 'nullable|integer|exists:bookings,id',
            'receipt_no'     => 'required|string|unique:receipts,receipt_no',
            'issued_at'      => 'required|date',
            'total_amount'   => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'pax_adult'      => 'nullable|integer|min:0',
            'pax_child'      => 'nullable|integer|min:0',
            'pax_baby'       => 'nullable|integer|min:0',
            'payment_method' => 'required|string|in:cash,online_banking,e-wallet,credit_card,debit_card',
            'notes'          => 'nullable|string',
        ]);

        $customer = Customer::where('unique_id', $validated['customer_id'])
            ->where('company_id', $validated['company_id'])
            ->firstOrFail();

        $hasBooking = Booking::where('customer_id', $validated['customer_id'])
            ->whereNull('deleted_at')
            ->exists();

        if (!$hasBooking) {
            return response()->json([
                'success' => false,
                'message' => 'Customer mesti ada booking sebelum resit boleh dibuat.',
            ], 422);
        }

        $trip = Trip::where('id', $validated['trip_id'])
            ->where('company_id', $validated['company_id'])
            ->firstOrFail();

        $receipt = Receipt::create([
            'receipt_no'     => $validated['receipt_no'],
            'customer_id'    => $validated['customer_id'],
            'company_id'     => $validated['company_id'],
            'trip_id'        => $validated['trip_id'],
            'booking_id'     => $validated['booking_id'] ?? null,
            'total_amount'   => $validated['total_amount'],
            'deposit_amount' => $validated['deposit_amount'] ?? 0,
            'pax_adult'      => $validated['pax_adult'] ?? 0,
            'pax_child'      => $validated['pax_child'] ?? 0,
            'pax_baby'       => $validated['pax_baby'] ?? 0,
            'payment_method' => $validated['payment_method'],
            'notes'          => $validated['notes'] ?? null,
            'issued_at'      => $validated['issued_at'],
        ]);

        $receipt->load(['customer', 'customer.state', 'company', 'trip', 'booking']);
        NotificationController::notifyStaff(
    excludeUserId: Auth::id(),
    type: 'receipt_created',
    title: 'Resit Baru Dibuat',
    message: "Resit #{$receipt->receipt_no} telah dibuat",
    data: [
        'receipt_id' => $receipt->id,
        'receipt_no' => $receipt->receipt_no,
    ]
);

        return response()->json([
            'success' => true,
            'message' => 'Resit berjaya dibuat',
            'data'    => $this->formatReceipt($receipt),
        ]);
    }

    public function search(Request $request)
    {
        $companyIds = $this->getUserCompanyIds();

        $query = Receipt::with(['customer', 'company', 'trip', 'booking'])
            ->whereIn('company_id', $companyIds)
            ->whereNull('deleted_at');

        if ($request->filled('receipt_no'))     $query->where('receipt_no', 'like', '%' . $request->receipt_no . '%');
        if ($request->filled('customer_name'))  $query->whereHas('customer', fn($q) => $q->where('name', 'like', '%' . $request->customer_name . '%'));
        if ($request->filled('trip_id'))        $query->where('trip_id', $request->trip_id);
        if ($request->filled('company_id'))     $query->where('company_id', $request->company_id);
        if ($request->filled('date_from'))      $query->whereDate('issued_at', '>=', $request->date_from);
        if ($request->filled('date_to'))        $query->whereDate('issued_at', '<=', $request->date_to);
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);
        if ($request->filled('amount_min'))     $query->where('total_amount', '>=', $request->amount_min);
        if ($request->filled('amount_max'))     $query->where('total_amount', '<=', $request->amount_max);

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('issued_at', 'desc')->get()->map(fn($r) => $this->formatReceipt($r))
        ]);
    }

    public function show($id)
    {
        $companyIds = $this->getUserCompanyIds();
        $receipt    = Receipt::whereIn('company_id', $companyIds)->findOrFail($id);
        $receipt->load(['customer', 'customer.state', 'company', 'trip', 'booking']);
        return response()->json(['success' => true, 'data' => $this->formatReceipt($receipt)]);
    }

    public function destroy($id)
    {
        $companyIds = $this->getUserCompanyIds();
        $receipt    = Receipt::whereIn('company_id', $companyIds)->findOrFail($id);
        if ($receipt->is_protected) {
    return response()->json([
        'success' => false,
        'message' => '🔒 Resit demo ini tidak boleh dipadam.',
    ], 403);
}
        // Simpan sebelum delete
$receiptNo = $receipt->receipt_no;
$receiptId = $receipt->id;

$receipt->delete();

// ✅ TAMBAH SINI
NotificationController::notifyStaff(
    excludeUserId: Auth::id(),
    type: 'receipt_deleted',
    title: 'Resit Dipadam',
    message: "Resit #{$receiptNo} telah dipadam",
    data: [
        'receipt_id' => $receiptId,
        'receipt_no' => $receiptNo,
    ]
);
        return response()->json(['success' => true, 'message' => 'Resit dipadam']);
    }

    public function restore($id)
    {
        $companyIds = $this->getUserCompanyIds();
        $receipt    = Receipt::whereIn('company_id', $companyIds)->withTrashed()->findOrFail($id);
        $receipt->restore();
        NotificationController::notifyStaff(
    excludeUserId: Auth::id(),
    type: 'receipt_restored',
    title: 'Resit Dipulihkan',
    message: "Resit #{$receipt->receipt_no} telah dipulihkan",
    data: [
        'receipt_id' => $receipt->id,
        'receipt_no' => $receipt->receipt_no,
    ]
);
        return response()->json(['success' => true, 'message' => 'Resit dipulihkan']);
    }

    public function statistics()
    {
        $companyIds = $this->getUserCompanyIds();

        $total   = Receipt::whereIn('company_id', $companyIds)->whereNull('deleted_at')->sum('total_amount');
        $count   = Receipt::whereIn('company_id', $companyIds)->whereNull('deleted_at')->count();
        $methods = Receipt::whereIn('company_id', $companyIds)->whereNull('deleted_at')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('payment_method')->get();

        return response()->json(['success' => true, 'data' => compact('total', 'count', 'methods')]);
    }

    public function getByCustomer($customerId)
    {
        $companyIds = $this->getUserCompanyIds();
        $receipts   = Receipt::where('customer_id', $customerId)
            ->whereIn('company_id', $companyIds)
            ->whereNull('deleted_at')
            ->with(['customer', 'company', 'trip'])
            ->orderBy('issued_at', 'desc')
            ->get()
            ->map(fn($r) => $this->formatReceipt($r));

        return response()->json(['success' => true, 'data' => $receipts]);
    }

    private function formatReceipt(Receipt $receipt): array
    {
        $customer = $receipt->customer;
        $company  = $receipt->company;
        $trip     = $receipt->trip;

        return [
            'id'             => $receipt->id,
            'receipt_no'     => $receipt->receipt_no,
            'issued_at'      => $receipt->issued_at,
            'total_amount'   => $receipt->total_amount,
            'deposit_amount' => $receipt->deposit_amount,
            'pax_adult'      => $receipt->pax_adult,
            'pax_child'      => $receipt->pax_child,
            'pax_baby'       => $receipt->pax_baby,
            'payment_method' => $receipt->payment_method,
            'notes'          => $receipt->notes,
            'customer' => $customer ? [
                'unique_id'   => $customer->unique_id,
                'name'        => $customer->name,
                'ic'          => $customer->ic,
                'age'         => $customer->age ?? null,
                'email'       => $customer->email,
                'phone'       => $customer->phone,
                'passport_no' => $customer->passport_no,
                'state'       => $customer->state?->name,
            ] : null,
            'company' => $company ? [
                'id'              => $company->id,
                'name'            => $company->name,
                'registration_no' => $company->registration_no,
                'address'         => $company->address,
                'logo_url'        => $this->resolveLogoUrl($company),
            ] : null,
            'trip' => $trip ? [
                'id'            => $trip->id,
                'ref_id'        => $trip->ref_id,
                'location'      => $trip->location,
                'country'       => $trip->country,
                'national_code' => $trip->national_code,
                'date_from'     => $trip->date_from,
                'date_to'       => $trip->date_to,
                'duration'      => $trip->duration,
                'price_adult'   => $trip->price_adult,
                'price_child'   => $trip->price_child,
                'price_baby'    => $trip->price_baby,
                'deposit'       => $trip->deposit,
            ] : null,
            'booking' => $receipt->booking ? [
    'id'               => $receipt->booking->id,
    'booking_no'       => $receipt->booking->booking_no,
    'traveler_details' => $receipt->booking->traveler_details ?? [],
] : null,
        ];
    }

    private function resolveLogoUrl(Company $company): ?string
    {
        if (empty($company->logo)) return null;
        if (str_starts_with($company->logo, 'http')) return $company->logo;
        if (Storage::disk('public')->exists($company->logo)) return asset('storage/' . $company->logo);
        $clean = ltrim(str_replace('public/', '', $company->logo), '/');
        if (Storage::disk('public')->exists($clean)) return asset('storage/' . $clean);
        return null;
    }
}