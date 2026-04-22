<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Models\Company;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class CustomerController extends Controller
{
    // ============================================================
    // HELPER — Ambil ID semua company milik user yang sedang login
    // ============================================================
    private function getUserCompanyIds(): array
{
    /** @var User $user */
    $user = Auth::user();

    if ($user->isAdminSystem()) {
        return Company::pluck('id')->toArray();
    }

    if ($user->isOwnerBusiness() || $user->role === 'tester') {
        return $user->company_id ? [$user->company_id] : [];
    }

    return [];
}

    // ============================================================
    // HELPER — Format data customer untuk JSON response
    // ============================================================
    private function formatCustomer(Customer $c): array
{
    return [
        // ── Identity ───────────────────────────────────────
        'id'               => $c->unique_id,
        'unique_id'        => $c->unique_id,
        'ref_id'           => $c->ref_id,
        'company_id'       => $c->company_id,
        'ic'               => $c->ic,
        'dob'              => $c->dob?->toDateString(),
        'age'              => $c->age,
        'gender'           => $c->gender,

        // ── Personal ───────────────────────────────────────
        'name'             => $c->name,
        'email'            => $c->email,
        'phone'            => $c->phone,
        'country_of_birth' => $c->country_of_birth,

        // ── Lokasi ─────────────────────────────────────────
        'state_id'         => $c->state_id,
        'state'            => $c->state ? [
            'id'   => $c->state->id,
            'name' => $c->state->name,
        ] : null,

        'address'          => $c->address ? [
            'id'           => $c->address->id,
            'address_line' => $c->address->address_line,
            'postcode'     => $c->address->postcode,
        ] : null,

        // ── Passport ───────────────────────────────────────
        'passport_no'       => $c->passport_no,
        'pp_warganegara'    => $c->pp_warganegara,
        'pp_tarikh_lahir'   => $c->pp_tarikh_lahir?->toDateString(),
        'pp_gender'         => $c->pp_gender,
        'pp_date_issued'    => $c->pp_date_issued?->toDateString(),
        'pp_date_expired'   => $c->pp_date_expired?->toDateString(),
        'pp_issuing_office' => $c->pp_issuing_office,
        'pp_place_born'     => $c->pp_place_born ?? null,

        // ── Booking Summary ────────────────────────────────
        // ✅ guna withCount kalau ada
       'total_bookings' => $c->bookings_count,

        // ✅ limit supaya ringan
        'bookings' => $c->bookings
            ->sortByDesc('created_at')
            ->take(5)
            ->map(function ($b) {
                return [
                    'id'             => $b->id,
                    'booking_no'     => $b->booking_no,
                    'booking_status' => $b->booking_status,
                    'payment_status' => $b->payment_status,
                    'total_amount'   => $b->total_amount,

                    // pax
                    'pax_adult'      => $b->pax_adult,
                    'pax_child'      => $b->pax_child,
                    'pax_baby'       => $b->pax_baby,

                    // 🔥 penting untuk EDIT BOOKING
                    'traveler_details' => $b->traveler_details
                        ? (is_array($b->traveler_details)
                            ? $b->traveler_details
                            : json_decode($b->traveler_details, true))
                        : [],

                    'created_at'     => $b->created_at,

                    // trip info
                    'trip' => $b->trip ? [
                        'id'        => $b->trip->id,
                        'location'  => $b->trip->location,
                        'country'   => $b->trip->country ?? null,
                        'date_from' => $b->trip->date_from,
                        'date_to'   => $b->trip->date_to ?? null,
                        'company'   => $b->trip->company ? [
                            'id'   => $b->trip->company->id,
                            'name' => $b->trip->company->name,
                        ] : null,
                    ] : null,
                ];
            })
            ->values(),
    ];
}

    // ============================================================
    // HELPER — Eager load standard untuk customer
    // ============================================================
    private function withRelations(): array
    {
        return [
            'state',                  // state dari customers.state_id
            'address',                // addresses table (tiada state)
            'bookings.trip.company',  // booking → trip → company
        ];
    }

    // ============================================================
    // INDEX — GET /customers
    // ============================================================
    public function index(): JsonResponse
{
    $companyIds = $this->getUserCompanyIds();

    $customers = Customer::with($this->withRelations())
        ->withCount('bookings') // ✅ TAMBAH SINI
    ->whereIn('company_id', $companyIds)
    ->whereNull('deleted_at')
    ->orderBy('name')
    ->get()
    ->map(fn($c) => $this->formatCustomer($c));

    return response()->json([
        'success' => true,
        'data'    => $customers,
    ]);
}

    // ============================================================
    // SEARCH — GET /customers/search
    // ============================================================
    public function search(Request $request): JsonResponse
    {
        $companyIds = $this->getUserCompanyIds();

        $query = Customer::with($this->withRelations())
        ->withCount('bookings')
            ->whereIn('company_id', $companyIds)
            ->whereNull('deleted_at');

        // ── Filter: Company ────────────────────────────────────
        if ($request->filled('company_id')) {
            $allowed = array_intersect([$request->company_id], $companyIds);
            if (!empty($allowed)) {
                $query->where('company_id', $request->company_id);
            }
        }

        // ── Filter: Carian umum (q) ────────────────────────────
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name',  'like', "%{$search}%")
                  ->orWhere('ic',    'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // ── Filter: Spesifik ───────────────────────────────────
        if ($request->filled('name'))   $query->where('name',     'like', '%' . $request->name  . '%');
        if ($request->filled('ic'))     $query->where('ic',       'like', '%' . $request->ic    . '%');
        if ($request->filled('email'))  $query->where('email',    'like', '%' . $request->email . '%');
        if ($request->filled('gender')) $query->where('gender',   $request->gender);
        if ($request->filled('state'))  $query->where('state_id', $request->state);

        // ── Filter: Umur ───────────────────────────────────────
        if ($request->filled('age_min') && is_numeric($request->age_min)) {
            $query->where('dob', '<=', Carbon::now()->subYears((int) $request->age_min)->toDateString());
        }
        if ($request->filled('age_max') && is_numeric($request->age_max)) {
            $query->where('dob', '>=', Carbon::now()->subYears((int) $request->age_max + 1)->addDay()->toDateString());
        }

        $customers = $query
            ->orderBy('name')
            ->limit($request->limit ?? 50)
            ->get()
            ->map(fn($c) => $this->formatCustomer($c));

        return response()->json([
            'success' => true,
            'data'    => $customers,
        ]);
    }
    public function noBooking(Request $request)
{
    try {
        $companyIds = $this->getUserCompanyIds();

        $query = Customer::withTrashed()
            ->whereDoesntHave('bookings')
            ->with('state')
            // ✅ Hadkan kepada company yang dibenarkan untuk user ini
            ->whereIn('company_id', $companyIds);

        // ✅ Filter by company_id jika dipilih — kalau tiada customer, return kosong
        if ($request->filled('company_id')) {
            // Pastikan company_id yang dipilih dalam senarai yang dibenarkan
            if (!in_array($request->company_id, $companyIds)) {
                return response()->json(['success' => true, 'data' => []]);
            }
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('name'))
            $query->where('name', 'like', '%' . $request->name . '%');
        if ($request->filled('ic'))
            $query->where('ic', 'like', '%' . $request->ic . '%');
        if ($request->filled('email'))
            $query->where('email', 'like', '%' . $request->email . '%');
        if ($request->filled('gender'))
            $query->where('gender', $request->gender);
        if ($request->filled('state'))
            $query->where('state_id', $request->state);

        $customers = $query->orderBy('created_at', 'desc')->limit(200)->get();

        return response()->json(['success' => true, 'data' => $customers]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Ralat: ' . $e->getMessage(),
        ], 500);
    }
}

    // ============================================================
    // SHOW — GET /customers/{unique_id}
    // ============================================================
    public function show(string $unique_id): JsonResponse
    {
        $companyIds = $this->getUserCompanyIds();

        $customer = Customer::with($this->withRelations())
        ->withCount('bookings')
            ->where('unique_id', $unique_id)
            ->whereIn('company_id', $companyIds)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak dijumpai.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatCustomer($customer),
        ]);
    }

    // ============================================================
    // STORE — POST /customers
    // ============================================================
    public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'ic'                => 'required|string|unique:customers,ic',
        'name'              => 'required|string|max:255',
        'email'             => 'required|email|unique:customers,email',
        'phone'             => 'required|string|max:20',
        'state_id'          => 'required|exists:states,id',
        'address_line'      => 'required|string',
        'postcode'          => 'required|string|max:10',
        'company_id'        => 'nullable|exists:companies,id',

        // passport (optional)
        'country_of_birth'  => 'nullable|string|max:255',
        'passport_no'       => 'nullable|string|max:50',
        'pp_warganegara'    => 'nullable|string|max:100',
        'pp_tarikh_lahir'   => 'nullable|date',
        'pp_gender'         => 'nullable|in:M,F',
        'pp_date_issued'    => 'nullable|date',
        'pp_date_expired'   => 'nullable|date',
        'pp_issuing_office' => 'nullable|string|max:255',
    ]);

    /** @var User $user */
    $user = Auth::user();

    // ── Tentukan company ─────────────────────────────
    if ($user->isOwnerBusiness()) {
        $companyId = $user->company_id;
    } elseif ($user->isAdminSystem()) {
        $companyId = $validated['company_id'] ?? null;
    } else {
        $companyId = $validated['company_id'] ?? env('DEMO_COMPANY_ID', 1);
    }

    // ── Clean IC ────────────────────────────────────
    $rawIc = preg_replace('/\D/', '', $validated['ic']);

    // ── Create Customer ─────────────────────────────
    $customer = Customer::create([
        'unique_id'         => (string) Str::uuid(),
        'ic'                => $rawIc,
        'name'              => $validated['name'],
        'email'             => $validated['email'],
        'phone'             => $validated['phone'],
        'state_id'          => $validated['state_id'],
        'company_id'        => $companyId,

        // passport
        'country_of_birth'  => $validated['country_of_birth']  ?? null,
        'passport_no'       => $validated['passport_no']       ?? null,
        'pp_warganegara'    => $validated['pp_warganegara']    ?? null,
        'pp_tarikh_lahir'   => $validated['pp_tarikh_lahir']   ?? null,
        'pp_gender'         => $validated['pp_gender']         ?? null,
        'pp_date_issued'    => $validated['pp_date_issued']    ?? null,
        'pp_date_expired'   => $validated['pp_date_expired']   ?? null,
        'pp_issuing_office' => $validated['pp_issuing_office'] ?? null,
    ]);

    // ── Address ─────────────────────────────────────
    CustomerAddress::create([
        'customer_id'  => $customer->unique_id,
        'address_line' => $validated['address_line'],
        'postcode'     => $validated['postcode'],
    ]);

    // ── Notification ────────────────────────────────
    NotificationController::notifyStaff(
        $user->id,
        'customer_created',
        'Customer Baharu Ditambah',
        $user->name . ' menambah customer: ' . $customer->name,
        ['customer_id' => $customer->unique_id],
         companyId:  $customer->company_id,      
    companyName: $customer->company->name  
);

    // ── Response (FIX IMPORTANT) ────────────────────
    $customer->load($this->withRelations())
             ->loadCount('bookings'); // ✅ penting

    return response()->json([
        'success' => true,
        'data'    => $this->formatCustomer($customer),
        'message' => 'Customer berjaya ditambah',
    ], 201);
}

    // ============================================================
    // UPDATE — PUT /customers/{unique_id}
    // ============================================================
    public function update(Request $request, string $id): JsonResponse
    {
        $companyIds = $this->getUserCompanyIds();

        $customer = Customer::where('unique_id', $id)
            ->whereIn('company_id', $companyIds)
            ->firstOrFail();

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => ['required', 'email', Rule::unique('customers', 'email')->ignore($customer->unique_id, 'unique_id')],
            'phone'             => 'required|string|max:20',
            'state_id'          => 'required|exists:states,id',
            'address_line'      => 'required|string',
            'postcode'          => 'required|string|max:10',
            'country_of_birth'  => 'nullable|string|max:255',
            'passport_no'       => 'nullable|string|max:50',
            'pp_warganegara'    => 'nullable|string|max:100',
            'pp_tarikh_lahir'   => 'nullable|date',
            'pp_gender'         => 'nullable|in:M,F',
            'pp_date_issued'    => 'nullable|date',
            'pp_date_expired'   => 'nullable|date',
            'pp_issuing_office' => 'nullable|string|max:255',
        ]);

        $customer->update([
    'name'              => $validated['name'],
    'email'             => $validated['email'],
    'phone'             => $validated['phone'],
    'state_id'          => $validated['state_id'],

    'country_of_birth'  => $validated['country_of_birth']  ?? null,
    'passport_no'       => $validated['passport_no']       ?? null,
    'pp_warganegara'    => $validated['pp_warganegara']    ?? null,
    'pp_tarikh_lahir'   => $validated['pp_tarikh_lahir']   ?? null,
    'pp_gender'         => $validated['pp_gender']         ?? null,
    'pp_date_issued'    => $validated['pp_date_issued']    ?? null,
    'pp_date_expired'   => $validated['pp_date_expired']   ?? null,
    'pp_issuing_office' => $validated['pp_issuing_office'] ?? null,
]);
// ── Update / Create Address ──────────────────────────
CustomerAddress::updateOrCreate(
    ['customer_id' => $customer->unique_id],
    [
        'address_line' => $validated['address_line'],
        'postcode'     => $validated['postcode'],
    ]
);

        /** @var User $user */
        $user = Auth::user();

        NotificationController::notifyStaff(
            $user->id,
            'customer_updated',
            'Customer Dikemaskini',
            $user->name . ' mengemaskini customer: ' . $customer->name,
            ['customer_id' => $customer->unique_id],
           companyId: $customer->company_id,      
    companyName: $customer->company->name  
);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'data'    => $this->formatCustomer($customer->fresh()->load($this->withRelations())),
        ]);
    }

    // ============================================================
    // DESTROY — soft delete
    // ============================================================
    public function destroy(string $unique_id): JsonResponse
    {
        $companyIds = $this->getUserCompanyIds();

        $customer = Customer::whereIn('company_id', $companyIds)
            ->where('unique_id', $unique_id)
            ->firstOrFail();

        if ($customer->is_protected) {
            return response()->json([
                'success' => false,
                'message' => '🔒 Data demo ini tidak boleh dipadam.',
            ], 403);
        }

        $customerName = $customer->name;
        $customer->delete();

        /** @var User $user */
        $user = Auth::user();

        NotificationController::notifyStaff(
            $user->id,
            'customer_deleted',
            'Customer Dipadam',
            $user->name . ' memadam customer: ' . $customerName,
            ['customer_id' => $unique_id],
                companyId: $customer->company_id,
    companyName: $customer->company->name
        );

        return response()->json([
            'success' => true,
            'message' => 'Customer dipadam',
        ]);
    }

    // ============================================================
    // RESTORE — pulihkan soft delete
    // ============================================================
    public function restore(string $id): JsonResponse
    {
        $companyIds = $this->getUserCompanyIds();

        $customer = Customer::withTrashed()
            ->where('unique_id', $id)
            ->whereIn('company_id', $companyIds)
            ->firstOrFail();

        $customer->restore();

        /** @var User $user */
        $user = Auth::user();

        NotificationController::notifyStaff(
            $user->id,
            'customer_restored',
            'Customer Dipulihkan',
            $user->name . ' memulihkan customer: ' . $customer->name,
            ['customer_id' => $customer->unique_id],
                companyId: $customer->company_id,
    companyName: $customer->company->name
        );

        return response()->json([
            'success' => true,
            'message' => 'Customer restored successfully',
        ]);
    }

    // ============================================================
    // CREATE view — GET /customers/create
    // ============================================================
    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        $companies = Company::query();
        if ($user->isOwnerBusiness()) {
            $companies = $companies->where('user_id', $user->id);
        }
        $companies = $companies->get();

        $states = \App\Models\State::all();

        return view('dashboard.customers.create', [
            'companies' => $companies,
            'states'    => $states,
        ]);
    }

    // ============================================================
    // Customer list by company (for dropdown)
    // ============================================================
    public function customersByCompany(Request $request): JsonResponse
    {
        $companyId = $request->query('company_id');

        /** @var User $user */
        $user = Auth::user();

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'message' => 'Company ID required',
            ], 400);
        }

        if ($user->isOwnerBusiness()) {
            $ownedIds = Company::where('user_id', $user->id)->pluck('id')->toArray();
            if (!in_array($companyId, $ownedIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized company access',
                ], 403);
            }
        }

        $customers = Customer::where('company_id', $companyId)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->limit(50)
            ->get(['unique_id', 'name', 'ic']);

        return response()->json([
            'success' => true,
            'data'    => $customers,
        ]);
    }
    public function count()
{
    $companyIds = $this->getUserCompanyIds();
    $count = Customer::whereIn('company_id', $companyIds)
        ->whereNull('deleted_at')
        ->count();
    return response()->json(['success' => true, 'count' => $count]);
}
public function bulkImport(Request $request): JsonResponse
{
    $validated = $request->validate([
        'company_id'             => 'required|exists:companies,id',
        'customers'              => 'required|array',
        'customers.*.name'       => 'required|string|max:255',
        'customers.*.ic'         => 'required|string',
        'customers.*.email'      => 'nullable|email',
        'customers.*.phone'      => 'nullable|string|max:20',
        'customers.*.state_id'   => 'nullable|exists:states,id',
        'customers.*.address_line' => 'nullable|string',
        'customers.*.postcode'   => 'nullable|string|max:10',
    ]);

    $companyIds = $this->getUserCompanyIds();

    // Pastikan company_id yang diminta dalam senarai yang dibenarkan
    if (!in_array($validated['company_id'], $companyIds)) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized company access',
        ], 403);
    }

    $companyId = $validated['company_id'];
    $company   = Company::find($companyId);
    $count     = 0;
    $skipped   = 0;

    foreach ($validated['customers'] as $customerData) {
        $rawIc = preg_replace('/\D/', '', $customerData['ic']);

        // Skip jika IC sudah wujud
        if (Customer::where('ic', $rawIc)->exists()) {
            $skipped++;
            continue;
        }

        $customer = Customer::create([
            'unique_id'  => (string) Str::uuid(),
            'ic'         => $rawIc,
            'name'       => $customerData['name'],
            'email'      => $customerData['email']    ?? null,
            'phone'      => $customerData['phone']    ?? null,
            'state_id'   => $customerData['state_id'] ?? null,
            'company_id' => $companyId,
        ]);

        // Buat address kalau ada
        if (!empty($customerData['address_line'])) {
            CustomerAddress::create([
                'customer_id'  => $customer->unique_id,
                'address_line' => $customerData['address_line'],
                'postcode'     => $customerData['postcode'] ?? '',
            ]);
        }

        $count++;
    }

    /** @var User $user */
    $user = Auth::user();

    NotificationController::notifyStaff(
        $user->id,
        'customer_created',
        'Bulk Import Customer',
        $user->name . ' mengimport ' . $count . ' customer baru',
        [
            'import_count' => $count,
            'company_id'   => $companyId,
        ],
        companyId:   $companyId,
        companyName: $company->name
    );

    return response()->json([
        'success' => true,
        'message' => "{$count} customer berjaya diimport" . ($skipped > 0 ? ", {$skipped} diskip (IC duplikat)" : ''),
        'count'   => $count,
        'skipped' => $skipped,
    ], 201);
}
}