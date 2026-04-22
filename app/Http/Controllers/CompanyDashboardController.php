<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\Receipt;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CompanyDashboardController extends Controller
{
    // ── Helper: Dapatkan company dari session ─────────────────────────────────
    private function getCompany(): Company
    {
        $auth = session('company_auth');
        return Company::findOrFail($auth['company_id']);
    }

    // ── Dashboard utama ───────────────────────────────────────────────────────
    public function index()
    {
        $company   = $this->getCompany();
        $states    = DB::table('states')->orderBy('name')->get();
        $countries = Country::orderBy('name')->get();

        return view('company.dashboard', compact('company', 'states', 'countries'));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STATS
    // ══════════════════════════════════════════════════════════════════════════
    public function stats()
    {
        $company = $this->getCompany();
        $cid     = $company->id;

        return response()->json([
            'success' => true,
            'data'    => [
                'total_customers'    => Customer::where('company_id', $cid)->whereNull('deleted_at')->count(),
                'total_trips'        => Trip::where('company_id', $cid)->count(),
                'total_bookings'     => Booking::whereHas('trip', fn($q) => $q->where('company_id', $cid))->whereNull('deleted_at')->count(),
                'confirmed_bookings' => Booking::whereHas('trip', fn($q) => $q->where('company_id', $cid))->where('booking_status', 'confirmed')->whereNull('deleted_at')->count(),
                'total_receipts'     => Receipt::where('company_id', $cid)->whereNull('deleted_at')->count(),
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CUSTOMERS — Hanya customer di bawah company ini
    // ══════════════════════════════════════════════════════════════════════════
    public function customers(Request $request)
    {
        try {
            $company = $this->getCompany();
            if (!$company) {
                return response()->json(['success' => false, 'message' => 'Sesi syarikat tidak dijumpai. Sila log masuk semula.'], 401);
            }

            // Step 1: UUID customers milik syarikat ini secara langsung
            $directIds = Customer::where('company_id', $company->id)
                            ->whereNull('deleted_at')
                            ->pluck('unique_id')
                            ->toArray();

            // Step 2: UUID customers yang ada booking pada trip syarikat ini
            $bookedIds = [];
            try {
                $tripIds   = Trip::where('company_id', $company->id)->pluck('id')->toArray();
                $bookedIds = Booking::whereIn('trip_id', $tripIds)
                                ->whereNotNull('customer_id')
                                ->pluck('customer_id')
                                ->toArray();
            } catch (\Throwable $e) {
                // Biarkan kosong jika booking query gagal
            }

            // Gabung & buang duplikat
            $allIds = array_unique(array_merge($directIds, $bookedIds));

            if (empty($allIds)) {
                return response()->json(['success' => true, 'data' => []]);
            }

            // Step 3: Query customers
            $query = Customer::whereNull('deleted_at')
                              ->whereIn('unique_id', $allIds);

            if ($request->filled('name'))     $query = $query->where('name',     'like', '%'.$request->name.'%');
            if ($request->filled('ic'))       $query = $query->where('ic',       'like', '%'.$request->ic.'%');
            if ($request->filled('email'))    $query = $query->where('email',    'like', '%'.$request->email.'%');
            if ($request->filled('gender'))   $query = $query->where('gender',   $request->gender);
            if ($request->filled('state_id')) $query = $query->where('state_id', $request->state_id);

            $customers = $query->orderBy('name')->get()->map(function($c) {
                // Dapatkan nama negeri — guna DB langsung jika relationship tiada
                $stateName = null;
                if ($c->state_id) {
                    try {
                        $stateName = $c->state ? $c->state->name : null;
                    } catch (\Throwable $e) {
                        $stateName = DB::table('states')->where('id', $c->state_id)->value('name');
                    }
                }
                return [
                    'id'           => $c->unique_id,
                    'unique_id'    => $c->unique_id,
                    'name'         => $c->name,
                    'ic'           => $c->ic,
                    'email'        => $c->email,
                    'phone'        => $c->phone,
                    'age'          => $c->age,
                    'gender'       => $c->gender,
                    'state'        => $stateName,
                    'state_id'     => $c->state_id,
                    'address_line' => $c->address_line,
                    'postcode'     => $c->postcode,
                    'passport_no'  => $c->passport_no,
                    'company_id'   => $c->company_id,
                ];
            });

            return response()->json(['success' => true, 'data' => $customers]);

        } catch (\Throwable $e) {
            Log::error('CompanyDashboard::customers — ' . $e->getMessage(), [
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'DEBUG: ' . $e->getMessage() . ' (Line ' . $e->getLine() . ')',
                'debug'   => [
                    'file'    => basename($e->getFile()),
                    'line'    => $e->getLine(),
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    public function storeCustomer(Request $request)
    {
        $company = $this->getCompany();

        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:customers,email',
            'phone'            => 'required|string|max:20',
            'ic'               => 'required|string|max:20',
            'state_id'         => 'required',
            'address_line'     => 'required|string|max:500',
            'postcode'         => 'required|digits:5',
            'country_of_birth' => 'required|string',
            'passport_no'      => 'required|string|max:20',
            'pp_warganegara'   => 'required|string',
            'pp_tarikh_lahir'  => 'required|date',
            'pp_gender'        => 'required|in:M,F',
            'pp_date_issued'   => 'required|date',
            'pp_date_expired'  => 'required|date|after:pp_date_issued',
            'pp_issuing_office'=> 'required|string|max:255',
        ]);

        // Ekstrak digit sahaja dari IC untuk pengiraan
        $icRaw  = $request->ic ? preg_replace('/\D/', '', $request->ic) : null;
        $age    = null;
        $gender = null;
        $dob    = null;

        if ($icRaw && strlen($icRaw) >= 12) {
            $yr  = (int)substr($icRaw, 0, 2);
            $yr4 = $yr <= (int)date('y') ? 2000 + $yr : 1900 + $yr;
            $mo  = substr($icRaw, 2, 2);
            $dy  = substr($icRaw, 4, 2);

            // Validate tarikh dahulu sebelum assign
            if (checkdate((int)$mo, (int)$dy, $yr4)) {
                $dob    = "$yr4-$mo-$dy";
                $age    = (int)date('Y') - $yr4;
                // Digit terakhir ganjil = Lelaki, genap = Perempuan
                $gender = ((int)$icRaw[11] % 2 === 1) ? 'male' : 'female';
            }
        }

        // unique_id = UUID  |  ref_id = PAX-YYYY-NNNNN (sequential)
        $year   = date('Y');
        $prefix = 'PAX-' . $year . '-';

        // Cari nombor tertinggi dari ref_id ATAU unique_id (cover data lama)
        $lastFromRef    = \App\Models\Customer::where('ref_id', 'like', $prefix . '%')
                              ->orderByRaw('CAST(SUBSTRING(ref_id, -5) AS UNSIGNED) DESC')
                              ->value('ref_id');
        $lastFromUnique = \App\Models\Customer::where('unique_id', 'like', $prefix . '%')
                              ->orderByRaw('CAST(SUBSTRING(unique_id, -5) AS UNSIGNED) DESC')
                              ->value('unique_id');

        $numRef    = $lastFromRef    ? (int) substr($lastFromRef,    -5) : 0;
        $numUnique = $lastFromUnique ? (int) substr($lastFromUnique, -5) : 0;
        $nextNum   = max($numRef, $numUnique) + 1;
        $refId     = $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

        $customer = Customer::create([
            'company_id'       => $company->id,
            'unique_id'        => (string) \Illuminate\Support\Str::uuid(),
            'ref_id'           => $refId,
            'name'             => $request->name,
            'email'            => $request->email,
            'phone'            => $request->phone,
            'ic'               => $request->ic ?: null,
            'dob'              => $dob ?? ($request->dob ?: null),
            'age'              => $age,
            'gender'           => strtolower($gender ?? $request->gender ?? 'male'),
            'state_id'         => $request->state_id ?: null,
            'address_line'     => $request->address_line ?: null,
            'postcode'         => $request->postcode ?: null,
            'country_of_birth' => $request->country_of_birth ?: null,
            'passport_no'      => $request->passport_no ?: null,
            'pp_warganegara'   => $request->pp_warganegara ?: null,
            'pp_tarikh_lahir'  => $request->pp_tarikh_lahir ?: null,
            'pp_gender'        => $request->pp_gender ?: null,
            'pp_date_issued'   => $request->pp_date_issued ?: null,
            'pp_date_expired'  => $request->pp_date_expired ?: null,
            'pp_issuing_office'=> $request->pp_issuing_office ?: null,
        ]);

        return response()->json(['success' => true, 'data' => $customer, 'message' => 'Pelanggan berjaya ditambah']);
    }

    public function updateCustomer(Request $request, $id)
    {
        $company  = $this->getCompany();
        $customer = Customer::where('unique_id', $id)->where('company_id', $company->id)->firstOrFail();

        // Validate email unique — ignore rekod semasa (guna unique_id sebagai PK)
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->unique_id . ',unique_id',
            'phone' => 'required|string|max:20',
        ]);

        $customer->update([
            'name'         => $request->name         ?? $customer->name,
            'email'        => $request->email        ?? $customer->email,
            'phone'        => $request->phone        ?? $customer->phone,
            'ic'           => $request->ic           ?? $customer->ic,
            'gender'       => $request->gender       ?? $customer->gender,
            'state_id'     => $request->state_id     ?: $customer->state_id,
            'address_line' => $request->address_line ?? $customer->address_line,
            'postcode'     => $request->postcode     ?? $customer->postcode,
        ]);

        return response()->json(['success' => true, 'message' => 'Pelanggan dikemaskini']);
    }

    public function destroyCustomer($id)
    {
        $company  = $this->getCompany();
        $customer = Customer::where('unique_id', $id)->where('company_id', $company->id)->firstOrFail();
        $customer->delete();

        return response()->json(['success' => true, 'message' => 'Pelanggan dipadam']);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TRIPS — Hanya trip di bawah company ini
    // ══════════════════════════════════════════════════════════════════════════
    public function trips(Request $request)
    {
        $company = $this->getCompany();
        $query   = Trip::where('company_id', $company->id);

        if ($request->filled('country'))   $query->where('country', 'like', '%'.$request->country.'%');
        if ($request->filled('date_from')) $query->where('date_from', '>=', $request->date_from);

        $trips = $query->orderBy('date_from', 'desc')->get()->map(fn($t) => $this->formatTrip($t));

        return response()->json(['success' => true, 'data' => $trips]);
    }

    public function showTrip($id)
    {
        $company = $this->getCompany();
        $trip    = Trip::where('id', $id)->where('company_id', $company->id)->firstOrFail();

        return response()->json(['success' => true, 'data' => $this->formatTrip($trip)]);
    }

    private function formatTrip(Trip $trip): array
    {
        $from     = Carbon::parse($trip->date_from);
        $to       = Carbon::parse($trip->date_to);
        $duration = $from->diffInDays($to) + 1;

        $images = array_filter([
            $trip->pic1 ? Storage::url($trip->pic1) : null,
            $trip->pic2 ? Storage::url($trip->pic2) : null,
            $trip->pic3 ? Storage::url($trip->pic3) : null,
        ]);

        $bookingCount = \App\Models\Booking::where('trip_id', $trip->id)
            ->whereNull('deleted_at')
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->count();

        return [
            'id'             => $trip->id,
            'company_id'     => $trip->company_id,
            'country'        => $trip->country,
            'state'          => $trip->state,
            'location'       => $trip->location,
            'national_code'  => $trip->national_code,
            'date_from'      => $trip->date_from,
            'date_to'        => $trip->date_to,
            'duration'       => $duration . ' hari',
            'price_adult'    => $trip->price_adult,
            'price_child'    => $trip->price_child,
            'price_baby'     => $trip->price_baby,
            'deposit'        => $trip->deposit,
            'images'         => array_values($images),
            'booking_count'  => $bookingCount,
        ];
    }

    public function storeTrip(Request $request)
    {
        $company = $this->getCompany();

        $request->validate([
            'country'      => 'required|string',
            'location'     => 'required|string',
            'national_code'=> 'required|string|max:3',
            'date_from'    => 'required|date',
            'date_to'      => 'required|date|after_or_equal:date_from',
            'price_adult'  => 'required|numeric|min:0',
            'price_child'  => 'required|numeric|min:0',
            'price_baby'   => 'required|numeric|min:0',
            'deposit'      => 'required|numeric|min:0',
        ]);

        $pics = [];
        foreach (['images.0', 'images.1', 'images.2'] as $idx => $key) {
            $field = 'images';
            $files = $request->file('images') ?? [];
            if (isset($files[$idx])) {
                $pics['pic'.($idx+1)] = $files[$idx]->store('trips', 'public');
            }
        }

        $trip = Trip::create(array_merge($pics, [
            'company_id'    => $company->id,
            'country'       => $request->country,
            'state'         => $request->state,
            'location'      => $request->location,
            'national_code' => strtoupper($request->national_code),
            'date_from'     => $request->date_from,
            'date_to'       => $request->date_to,
            'price_adult'   => $request->price_adult,
            'price_child'   => $request->price_child,
            'price_baby'    => $request->price_baby,
            'deposit'       => $request->deposit,
        ]));

        // Itinerary — generate ref_id for each day
        if ($request->itinerary) {
            $dayCounter = 1;
            foreach ($request->itinerary as $dayNum => $item) {
                if (empty($item['title']) && empty($item['description'])) continue;

                // ref_id format: ITN-YYYY-NNNNN (sequential across all itineraries)
                $itnPrefix = 'ITN-' . date('Y') . '-';
                $lastItn   = DB::table('itineraries')
                                ->where('ref_id', 'like', $itnPrefix . '%')
                                ->orderByRaw('CAST(SUBSTRING(ref_id, -5) AS UNSIGNED) DESC')
                                ->value('ref_id');
                $itnNum    = $lastItn ? ((int) substr($lastItn, -5)) + 1 : 1;
                $itnRefId  = $itnPrefix . str_pad($itnNum, 5, '0', STR_PAD_LEFT);

                $trip->itineraries()->create([
                    'day_number'  => (int) $dayNum,
                    'day_date'    => !empty($item['day_date'])  ? $item['day_date']  : null,
                    'ref_id'      => $itnRefId,
                    'title'       => $item['title']       ?? '',
                    'description' => $item['description'] ?? '',
                    'time_from'   => !empty($item['time_from']) ? $item['time_from'] : null,
                    'time_to'     => !empty($item['time_to'])   ? $item['time_to']   : null,
                ]);
                $dayCounter++;
            }
        }

        return response()->json(['success' => true, 'data' => $trip, 'message' => 'Trip berjaya ditambah']);
    }

    public function updateTrip(Request $request, $id)
    {
        $company = $this->getCompany();
        $trip    = Trip::where('id', $id)->where('company_id', $company->id)->firstOrFail();

        $request->validate([
            'location'      => 'required|string|max:255',
            'country'       => 'required|string|max:255',
            'state'         => 'nullable|string|max:255',
            'national_code' => 'required|string|max:10',
            'date_from'     => 'required|date',
            'date_to'       => 'required|date|after_or_equal:date_from',
            'price_adult'   => 'required|numeric|min:0',
            'price_child'   => 'nullable|numeric|min:0',
            'price_baby'    => 'nullable|numeric|min:0',
            'deposit'       => 'nullable|numeric|min:0',
        ]);

        // Update core fields
        $trip->location      = $request->location;
        $trip->country       = $request->country;
        $trip->state         = $request->state;
        $trip->national_code = strtoupper($request->national_code);
        $trip->date_from     = $request->date_from;
        $trip->date_to       = $request->date_to;
        $trip->price_adult   = $request->price_adult;
        $trip->price_child   = $request->price_child;
        $trip->price_baby    = $request->price_baby;
        $trip->deposit       = $request->deposit;

        // Handle photos
        $files = $request->file('images') ?? [];
        foreach ([0, 1, 2] as $idx) {
            $picCol = 'pic' . ($idx + 1);
            if (isset($files[$idx])) {
                // Padam lama
                if ($trip->$picCol) Storage::disk('public')->delete($trip->$picCol);
                $trip->$picCol = $files[$idx]->store('trips', 'public');
            } elseif ($request->input('delete_pic' . ($idx + 1)) === '1') {
                if ($trip->$picCol) Storage::disk('public')->delete($trip->$picCol);
                $trip->$picCol = null;
            }
        }

        $trip->save();

        return response()->json([
            'success' => true,
            'data'    => $this->formatTrip($trip),
            'message' => 'Trip berjaya dikemaskini',
        ]);
    }

    public function destroyTrip($id)
    {
        $company = $this->getCompany();
        $trip    = Trip::where('id', $id)->where('company_id', $company->id)->firstOrFail();

        // Halang delete jika masih ada booking aktif
        $activeBookings = \App\Models\Booking::where('trip_id', $trip->id)
            ->whereNull('deleted_at')
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->count();

        if ($activeBookings > 0) {
            return response()->json(['success' => false, 'message' => 'Trip tidak boleh dipadam — masih ada ' . $activeBookings . ' booking aktif.'], 422);
        }

        $trip->delete();
        return response()->json(['success' => true, 'message' => 'Trip dipadam']);
    }

    // Senarai customer yang booking trip ini
    public function tripBookings($id)
    {
        $company = $this->getCompany();
        $trip    = Trip::where('id', $id)->where('company_id', $company->id)->firstOrFail();

        $bookings = \App\Models\Booking::with('customer')
            ->where('trip_id', $trip->id)
            ->whereNull('deleted_at')
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->get()
            ->map(fn($b) => [
                'booking_id'     => $b->id,
                'booking_no'     => $b->booking_no ?? ('BK-' . str_pad($b->id, 6, '0', STR_PAD_LEFT)),
                'customer_id'    => $b->customer?->unique_id,
                'customer_name'  => $b->customer?->name,
                'customer_ic'    => $b->customer?->ic,
                'booking_status' => $b->booking_status,
                'amount'         => $b->total_amount ?? $b->amount ?? 0,
            ]);

        return response()->json(['success' => true, 'data' => $bookings, 'trip_name' => $trip->location ?? $trip->country]);
    }

    // Remove customer dari trip (delete booking)
    public function removeTripBooking($tripId, $bookingId)
    {
        $company = $this->getCompany();
        $trip    = Trip::where('id', $tripId)->where('company_id', $company->id)->firstOrFail();
        $booking = \App\Models\Booking::where('id', $bookingId)->where('trip_id', $trip->id)->firstOrFail();
        $booking->delete();

        return response()->json(['success' => true, 'message' => 'Customer dikeluarkan dari trip']);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // BOOKINGS — Hanya booking di bawah company ini
    // ══════════════════════════════════════════════════════════════════════════
    public function bookings(Request $request)
    {
        $company = $this->getCompany();
        $query   = Booking::with(['customer', 'trip'])
                          ->whereHas('trip', fn($q) => $q->where('company_id', $company->id))
                          ->whereNull('deleted_at');

        if ($request->filled('customer_name'))  $query->whereHas('customer', fn($q) => $q->where('name', 'like', '%'.$request->customer_name.'%'));
        if ($request->filled('booking_status')) $query->where('booking_status', $request->booking_status);
        if ($request->filled('payment_status')) $query->where('payment_status', $request->payment_status);

        $bookings = $query->orderBy('created_at', 'desc')->get()->map(fn($b) => [
            'id'             => $b->id,
            'booking_no'     => $b->booking_no ?? ('BK-'.str_pad($b->id, 6, '0', STR_PAD_LEFT)),
            'customer_name'  => $b->customer?->name,
            'trip_name'      => $b->trip?->location ?? $b->trip?->country,
            'amount'         => $b->total_amount ?? $b->amount ?? 0,
            'payment_status' => $b->payment_status,
            'deposit_status' => $b->deposit_status,
            'booking_status' => $b->booking_status,
            'notes'          => $b->notes,
        ]);

        return response()->json(['success' => true, 'data' => $bookings]);
    }

    public function storeBooking(Request $request)
    {
        $company = $this->getCompany();

        // Pastikan customer & trip milik company ini
        $customer = Customer::where('unique_id', $request->customer_id)->where('company_id', $company->id)->firstOrFail();
        $trip     = Trip::where('id', $request->trip_id)->where('company_id', $company->id)->firstOrFail();

        $booking = Booking::create([
            'customer_id'    => $customer->unique_id,
            'trip_id'        => $trip->id,
            'booking_no'     => $request->booking_no,
            'total_amount'   => $request->amount,
            'payment_status' => $request->payment_status ?? 'unpaid',
            'deposit_status' => $request->deposit_status ?? 'not_paid',
            'booking_status' => $request->booking_status ?? 'pending',
            'notes'          => $request->notes,
        ]);

        return response()->json(['success' => true, 'data' => $booking, 'message' => 'Booking berjaya dibuat']);
    }

    public function updateBooking(Request $request, $id)
    {
        $company = $this->getCompany();
        $booking = Booking::whereHas('trip', fn($q) => $q->where('company_id', $company->id))
                          ->where('id', $id)->firstOrFail();

        $booking->update([
            'payment_status' => $request->payment_status ?? $booking->payment_status,
            'deposit_status' => $request->deposit_status ?? $booking->deposit_status,
            'booking_status' => $request->booking_status ?? $booking->booking_status,
            'total_amount'   => $request->amount         ?? $booking->total_amount,
            'notes'          => $request->notes          ?? $booking->notes,
        ]);

        return response()->json(['success' => true, 'message' => 'Booking dikemaskini']);
    }

    public function destroyBooking($id)
    {
        $company = $this->getCompany();
        $booking = Booking::whereHas('trip', fn($q) => $q->where('company_id', $company->id))
                          ->where('id', $id)->firstOrFail();
        $booking->delete();

        return response()->json(['success' => true, 'message' => 'Booking dipadam']);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RECEIPTS — Hanya resit di bawah company ini
    // ══════════════════════════════════════════════════════════════════════════
    public function receipts(Request $request)
    {
        $company = $this->getCompany();
        $query   = Receipt::with(['customer', 'trip'])
                          ->where('company_id', $company->id)
                          ->whereNull('deleted_at');

        if ($request->filled('receipt_no'))      $query->where('receipt_no', 'like', '%'.$request->receipt_no.'%');
        if ($request->filled('customer_name'))   $query->whereHas('customer', fn($q) => $q->where('name', 'like', '%'.$request->customer_name.'%'));
        if ($request->filled('date_from'))       $query->where('issued_at', '>=', $request->date_from);
        if ($request->filled('date_to'))         $query->where('issued_at', '<=', $request->date_to);
        if ($request->filled('payment_method'))  $query->where('payment_method', $request->payment_method);
        if ($request->filled('amount_min'))      $query->where('amount', '>=', $request->amount_min);
        if ($request->filled('amount_max'))      $query->where('amount', '<=', $request->amount_max);

        $receipts = $query->orderBy('issued_at', 'desc')->get()->map(fn($rc) => [
            'id'             => $rc->id,
            'receipt_no'     => $rc->receipt_no,
            'receipt_date'   => $rc->issued_at ? \Carbon\Carbon::parse($rc->issued_at)->format('d/m/Y') : null,
            'customer_name'  => $rc->customer?->name,
            'trip_name'      => $rc->trip?->location ?? $rc->trip?->country,
            'amount'         => $rc->total_amount,  // ✅ FIX: column is total_amount
            'deposit_amount' => $rc->deposit_amount,
            'payment_method' => $rc->payment_method,
            'description'    => $rc->description ?? $rc->notes,
            'company_name'   => $rc->company?->name ?? session('company_auth.company_name'),
            'company_logo'   => $rc->company?->logo ? Storage::url($rc->company->logo) : null,
            'company_reg'    => $rc->company?->registration_no ?? session('company_auth.registration_no'),
        ]);

        return response()->json(['success' => true, 'data' => $receipts]);
    }

    public function showReceipt($id)
    {
        $company = $this->getCompany();
        $receipt = Receipt::with(['customer', 'trip', 'company'])
                          ->where('id', $id)->where('company_id', $company->id)->firstOrFail();

        return response()->json(['success' => true, 'data' => [
            'id'             => $receipt->id,
            'receipt_no'     => $receipt->receipt_no,
            'receipt_date'   => $receipt->issued_at ? \Carbon\Carbon::parse($receipt->issued_at)->format('d/m/Y') : null,
            'customer_name'  => $receipt->customer?->name,
            'trip_name'      => $receipt->trip?->location ?? $receipt->trip?->country,
            'amount'         => $receipt->total_amount,  // ✅ FIX: column is total_amount
            'deposit_amount' => $receipt->deposit_amount,
            'payment_method' => $receipt->payment_method,
            'description'    => $receipt->description ?? $receipt->notes,
            'company_name'   => $receipt->company?->name,
            'company_logo'   => $receipt->company?->logo ? Storage::url($receipt->company->logo) : null,
            'company_reg'    => $receipt->company?->registration_no,
        ]]);
    }

    public function storeReceipt(Request $request)
    {
        $company  = $this->getCompany();
        $customer = Customer::where('unique_id', $request->customer_id)->where('company_id', $company->id)->firstOrFail();
        $trip     = Trip::where('id', $request->trip_id)->where('company_id', $company->id)->firstOrFail();

        // ✅ FIX: cast ke float supaya tak simpan null/string kosong
        $amount        = (float) ($request->amount        ?? 0);
        $depositAmount = (float) ($request->deposit_amount ?? 0);

        $receipt = Receipt::create([
            'company_id'     => $company->id,
            'customer_id'    => $customer->unique_id,
            'trip_id'        => $trip->id,
            'receipt_no'     => $request->receipt_no,
            'issued_at'      => $request->receipt_date,
            'total_amount'   => $amount,  // ✅ FIX: DB column is total_amount
            'deposit_amount' => $depositAmount,
            'payment_method' => $request->payment_method,
            'description'    => $request->description,
        ]);

        return response()->json(['success' => true, 'data' => $receipt, 'message' => 'Resit berjaya dibuat']);
    }

    public function destroyReceipt($id)
    {
        $company = $this->getCompany();
        $receipt = Receipt::where('id', $id)->where('company_id', $company->id)->firstOrFail();
        $receipt->delete();

        return response()->json(['success' => true, 'message' => 'Resit dipadam']);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SETTINGS
    // ══════════════════════════════════════════════════════════════════════════
    public function updateProfile(Request $request)
    {
        $company = $this->getCompany();

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('company-logos', 'public');
            $company->logo = $path;

            // Update session
            $auth = session('company_auth');
            $auth['logo'] = $path;
            session(['company_auth' => $auth]);
        }

        if ($request->filled('address')) {
            $company->address = $request->address;
        }

        $company->save();

        return response()->json(['success' => true, 'message' => 'Profil dikemaskini']);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        $company           = $this->getCompany();
        $company->password = Hash::make($request->password);
        $company->save();

        return response()->json(['success' => true, 'message' => 'Kata laluan berjaya ditukar']);
    }
}