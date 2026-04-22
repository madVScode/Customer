<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Trip;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    private function baseBookingQuery(array $companyIds)
{
    return Booking::whereHas('trip', function ($q) use ($companyIds) {
        $q->whereIn('company_id', $companyIds);
    });
}
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

    public function index(): JsonResponse
    {
        $companyIds = $this->getUserCompanyIds();

        $bookings = Booking::with(['customer', 'trip', 'trip.company'])
            ->whereHas('trip', fn($q) => $q->whereIn('company_id', $companyIds))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $bookings]);
    }

    public function search(Request $request): JsonResponse
{
    $companyIds = $this->getUserCompanyIds();

    $query = Booking::with(['customer.state', 'trip.company'])
        ->whereHas('trip', fn($q) => $q->whereIn('company_id', $companyIds));

    // ── Filter: Nama pelanggan ──
    if ($request->filled('name'))
        $query->whereHas('customer', fn($q) =>
            $q->where('name', 'like', '%' . $request->name . '%'));

    // ── Filter: No. IC ──
    if ($request->filled('ic'))
        $query->whereHas('customer', fn($q) =>
            $q->where('ic', 'like', '%' . $request->ic . '%'));

    // ── Filter: Emel ──
    if ($request->filled('email'))
        $query->whereHas('customer', fn($q) =>
            $q->where('email', 'like', '%' . $request->email . '%'));

    // ── Filter: Jantina ──
    if ($request->filled('gender'))
        $query->whereHas('customer', fn($q) =>
            $q->where('gender', $request->gender));

    // ── Filter: Negeri ──
    if ($request->filled('state'))
        $query->whereHas('customer', fn($q) =>
            $q->where('state_id', $request->state));

    // ── Filter: Syarikat ──
    if ($request->filled('company_id'))
        $query->whereHas('trip', fn($q) =>
            $q->where('company_id', $request->company_id)
              ->whereIn('company_id', $companyIds)); // kekal security check

    // ── Filter: Trip ──
    if ($request->filled('trip_id'))
        $query->where('trip_id', $request->trip_id);

    // ── Filter: Status Tempahan ──
    if ($request->filled('booking_status'))
        $query->where('booking_status', $request->booking_status);

    // ── Filter: Status Bayaran ──
    if ($request->filled('payment_status'))
        $query->where('payment_status', $request->payment_status);

    return response()->json([
        'success' => true,
        'data'    => $query->orderBy('created_at', 'desc')->get()
    ]);
}

    public function store(Request $request): JsonResponse
{
    $companyIds = $this->getUserCompanyIds();

    $validated = $request->validate([
        'company_id'       => 'required|exists:companies,id',
        'customer_id' => 'required|exists:customers,unique_id',
        'trip_id'          => 'required|integer|exists:trips,id',
        'total_amount'     => 'required|numeric|min:0',
        'payment_status'   => 'required|in:unpaid,partial,paid',
        'deposit_status'   => 'required|in:not_paid,paid',
        'booking_status'   => 'required|in:pending,confirmed,cancelled,completed',
        'notes'            => 'nullable|string|max:1000',
        'pax_adult'        => 'nullable|integer|min:1',
        'pax_child'        => 'nullable|integer|min:0',
        'pax_baby'         => 'nullable|integer|min:0',

        // ✅ traveler validation
        'traveler_details' => 'nullable|array',
'traveler_details.*.name' => 'required|string',
'traveler_details.*.ic'   => 'nullable|string',
'traveler_details.*.dob'  => 'nullable|date',
'traveler_details.*.gender' => 'nullable|in:M,F,male,female',
'traveler_details.*.passport_no' => 'nullable|string|max:50',
'traveler_details.*.nationality' => 'nullable|string|max:100',
'traveler_details.*.pp_expire' => 'nullable|date',
'traveler_details.*.type' => 'nullable|in:adult,child,baby',
    ]);

    // ✅ Layer 1: Company check
    if (!in_array($validated['company_id'], $companyIds)) {
    return response()->json(['success'=>false,'message'=>'Unauthorized'],403);
}
    $companyId = $validated['company_id'];

    // ✅ Layer 2: Trip check
    $trip = Trip::where('id', $validated['trip_id'])
    ->where('company_id', $companyId)
    ->first();

if (!$trip) {
    return response()->json([
        'success' => false,
        'message' => 'Trip tidak sah atau bukan milik company ini.'
    ], 422);
}

    // ✅ Layer 3: Customer check
    $customer = Customer::where('unique_id', $validated['customer_id'])
        ->where('company_id', $companyId)
        ->whereNull('deleted_at')
        ->first();

    if (!$customer) {
        return response()->json([
            'success' => false,
            'message' => 'Customer tidak sepadan dengan company.',
        ], 422);
    }

    // ✅ CLEAN travelers (optional but power)
    $travelers = collect($validated['traveler_details'] ?? [])
    ->map(function ($t) {
        return [
            'name' => $t['name'] ?? '',
            'ic'   => $t['ic'] ?? null,
            'dob'  => $t['dob'] ?? null,
            'type' => $t['type'] ?? 'adult',
            'gender' => $t['gender'] ?? null,
            'passport_no' => $t['passport_no'] ?? null,
            'nationality' => $t['nationality'] ?? null,
            'pp_expire' => $t['pp_expire'] ?? null,
        ];
    })->values()->toArray();

    // ✅ CREATE booking
    $booking = Booking::create([
        'booking_no'        => 'BKG-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
        'customer_id'       => $customer->unique_id,
        'trip_id'           => $trip->id,
        'total_amount'      => $validated['total_amount'],
        'payment_status'    => $validated['payment_status'],
        'deposit_status'    => $validated['deposit_status'],
        'booking_status'    => $validated['booking_status'],
        'notes'             => $validated['notes'] ?? null,
        'pax_adult'         => $validated['pax_adult'] ?? 1,
        'pax_child'         => $validated['pax_child'] ?? 0,
        'pax_baby'          => $validated['pax_baby'] ?? 0,

        // ✅ simpan terus array
        'traveler_details'  => $travelers,
        'details_completed' => count($travelers) > 0,

        'source'            => 'admin',
        'created_by'        => Auth::id(),
    ]);
    NotificationController::notifyStaff(
    excludeUserId: Auth::id(),
    type: 'booking_created',
    title: 'Booking Baru Dibuat',
    message: "Booking #{$booking->booking_no} telah dibuat",
    data: [
        'booking_id' => $booking->id,
        'booking_no' => $booking->booking_no,
    ]
);

    return response()->json([
        'success' => true,
        'data'    => $booking->load(['customer', 'trip', 'trip.company']),
        'message' => 'Booking berjaya dibuat',
    ], 201);
}

    public function update(Request $request, $id): JsonResponse
    {
        $companyIds = $this->getUserCompanyIds();

        $booking = $this->baseBookingQuery($companyIds)
    ->where('id', $id)
    ->firstOrFail();

        $validated = $request->validate([
            'payment_status' => 'required|in:unpaid,partial,paid',
            'deposit_status' => 'required|in:not_paid,paid',
            'booking_status' => 'required|in:pending,confirmed,cancelled,completed',
            'total_amount'   => 'required|numeric|min:0',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $booking->update($validated);
        NotificationController::notifyStaff(
    excludeUserId: Auth::id(),
    type: 'booking_updated',
    title: 'Booking Dikemaskini',
    message: "Booking #{$booking->booking_no} telah dikemaskini",
    data: [
        'booking_id' => $booking->id,
        'booking_no' => $booking->booking_no,
    ]
);

        return response()->json([
            'success' => true,
            'data'    => $booking->fresh()->load(['customer', 'trip', 'trip.company']),
            'message' => 'Booking dikemaskini',
        ]);
    }
    // ─── UPDATE TRAVELER DETAILS ───────────────────────────────────────────────
public function updateTravelers(Request $request, $id): JsonResponse
{
    $companyIds = $this->getUserCompanyIds();

    $booking = $this->baseBookingQuery($companyIds)
        ->where('id', $id)
        ->firstOrFail();

    $request->validate([
        'traveler_details'               => 'nullable|array|max:20',
        'traveler_details.*.name'        => 'required|string|max:255',
        'traveler_details.*.ic'          => 'nullable|string|max:20',
        'traveler_details.*.type'        => 'nullable|in:adult,child,baby',
        'traveler_details.*.gender'      => 'nullable|in:male,female,M,F',
        'traveler_details.*.dob'         => 'nullable|date',
        'traveler_details.*.passport_no' => 'nullable|string|max:50',
        'traveler_details.*.nationality' => 'nullable|string|max:100',
        'traveler_details.*.pp_expire'   => 'nullable|date',
        'details_completed'              => 'nullable|boolean',
    ]);

    $travelers = collect($request->traveler_details ?? [])
        ->map(function ($t) {
            // ✅ Whitelist — buang fields luar
            return [
                'name'        => $t['name']        ?? '',
                'ic'          => $t['ic']           ?? '',
                'type'        => $t['type']         ?? 'adult',
                'gender'      => $t['gender']       ?? '',
                'dob'         => $t['dob']          ?? '',
                'passport_no' => $t['passport_no']  ?? '',
                'nationality' => $t['nationality']  ?? '',
                'pp_expire'   => $t['pp_expire']    ?? '',
            ];
        })
        ->filter(fn($t) => !empty($t['name']))  // ✅ buang yang nama kosong
        ->values()
        ->toArray();

    $booking->update([
        'traveler_details'  => $travelers,
        'details_completed' => count($travelers) > 0 ? 1 : 0,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Maklumat pelancong berjaya dikemaskini.',
        'data'    => $booking->fresh()->load(['customer', 'trip.company']),
    ]);
}
    // ─── APPROVE ──────────────────────────────────────────────────────────────
public function approve(Request $request, $id): JsonResponse
{
    $companyIds = $this->getUserCompanyIds();

    $booking = $this->baseBookingQuery($companyIds)
    ->where('id', $id)
    ->firstOrFail();

    if ($booking->booking_status === 'confirmed') {
        return response()->json(['success' => false, 'message' => 'Booking sudah disahkan.'], 422);
    }

    $booking->update([
        'booking_status'   => 'confirmed',
        'approved_at'      => now(),
        'rejection_reason' => null,
    ]);
    NotificationController::notifyStaff(
    excludeUserId: Auth::id(),
    type: 'booking_approved',
    title: 'Booking Diluluskan',
    message: "Booking #{$booking->booking_no} telah diluluskan",
    data: [
        'booking_id' => $booking->id,
        'booking_no' => $booking->booking_no,
    ]
);

    return response()->json([
        'success' => true,
        'message' => '✅ Booking #' . $booking->booking_no . ' berjaya diluluskan.',
        'data'    => $booking->fresh()->load(['customer', 'trip.company']),
    ]);
}

// ─── REJECT ───────────────────────────────────────────────────────────────
public function reject(Request $request, $id): JsonResponse
{
    $companyIds = $this->getUserCompanyIds();

    $booking = $this->baseBookingQuery($companyIds)
    ->where('id', $id)
    ->firstOrFail();

    $request->validate(['reason' => 'nullable|string|max:500']);

    $booking->update([
        'booking_status'   => 'cancelled',
        'rejection_reason' => $request->reason ?: 'Ditolak oleh admin.',
        'approved_at'      => null,
    ]);
    NotificationController::notifyStaff(
    excludeUserId: Auth::id(),
    type: 'booking_rejected',
    title: 'Booking Ditolak',
    message: "Booking #{$booking->booking_no} telah ditolak",
    data: [
        'booking_id' => $booking->id,
        'booking_no' => $booking->booking_no,
    ]
);

    return response()->json([
        'success' => true,
        'message' => '❌ Booking #' . $booking->booking_no . ' telah ditolak.',
        'data'    => $booking->fresh()->load(['customer', 'trip.company']),
    ]);
}

// ─── DASHBOARD STATS ──────────────────────────────────────────────────────
public function dashStats(): JsonResponse
{
    $companyIds = $this->getUserCompanyIds();

    $base = Booking::whereHas('trip', fn($q) => $q->whereIn('company_id', $companyIds));

    return response()->json([
        'success' => true,
        'data'    => [
            'total'     => (clone $base)->count(),
            'pending'   => (clone $base)->where('booking_status', 'pending')->count(),
            'confirmed' => (clone $base)->where('booking_status', 'confirmed')->count(),
            'today'     => (clone $base)->whereDate('created_at', today())->count(),
            'revenue'   => (clone $base)->where('payment_status', 'paid')->sum('total_amount'),
            'need_details' => (clone $base)
                ->where('booking_status', 'confirmed')
                ->where('details_completed', false)
                ->count(),
        ],
    ]);
}

    public function destroy($id): JsonResponse
    {
        $companyIds = $this->getUserCompanyIds();

        $booking = $this->baseBookingQuery($companyIds)
    ->where('id', $id)
    ->firstOrFail();
            if ($booking->is_protected) {
    return response()->json([
        'success' => false,
        'message' => '🔒 Booking demo ini tidak boleh dipadam.',
    ], 403);
}

    $bookingNo = $booking->booking_no;
    $bookingId = $booking->id;

    $booking->delete();

    // ✅ Sekarang variable sudah ada
    NotificationController::notifyStaff(
        excludeUserId: Auth::id(),
        type: 'booking_deleted',
        title: 'Booking Dipadam',
        message: "Booking #{$bookingNo} telah dipadam",
        data: [
            'booking_id' => $bookingId,
            'booking_no' => $bookingNo,
        ]
    );

    return response()->json(['success' => true, 'message' => 'Booking dipadam']);
}
    
}