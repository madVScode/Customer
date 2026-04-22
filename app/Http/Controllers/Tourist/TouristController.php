<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Trip;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Booking;
use App\Services\WhatsAppService;
use App\Models\TripLike;
use App\Models\TripComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TouristController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    // ─── HELPER: Jana URL gambar dari path relatif ────────────────────────────
    // ✅ FIX: Bypass getTripImageUrl — guna asset() terus
    // Support semua format: jpg, jpeg, png, webp, gif
    private function resolveImageUrl(?string $path): ?string
    {
        if (empty($path)) return null;

        // Kalau dah ada full URL (http/https) — guna terus
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Path relatif (contoh: "trips/images/xxx.jpg") — tambah storage prefix
        return asset('storage/' . ltrim($path, '/'));
    }

    // ─── HELPER: Inject semua image URLs ke trip object ──────────────────────
    private function injectTripImages(Trip $trip): Trip
{
    $trip->image_url   = $this->resolveImageUrl($trip->pic1);
    $trip->image_url_1 = $this->resolveImageUrl($trip->pic1);
    $trip->image_url_2 = $this->resolveImageUrl($trip->pic2);
    $trip->image_url_3 = $this->resolveImageUrl($trip->pic3);
    $trip->pic1_url    = $trip->image_url_1;
    $trip->pic2_url    = $trip->image_url_2;
    $trip->pic3_url    = $trip->image_url_3;

    // ✅ Kira duration sama seperti TripController
    if ($trip->date_from && $trip->date_to) {
        $dateFrom       = \Carbon\Carbon::parse($trip->date_from);
        $dateTo         = \Carbon\Carbon::parse($trip->date_to);
        $trip->duration = $dateFrom->diffInDays($dateTo) + 1;
    }

    return $trip;
}

    // ─── HALAMAN UTAMA (PUBLIC) ───────────────────────────────────────────────
    public function index(Request $request)
    {
        $companies = Company::orderBy('name')->get();

        // ✅ Tunjuk SEMUA trip — upcoming dan past, filter client-side
        $trips = Trip::with('company')
            ->orderBy('date_from', 'desc')
            ->get();

        $trips->each(fn($trip) => $this->injectTripImages($trip));

        return view('tourist.index', compact('trips', 'companies'));
    }

    // ─── REGISTER ────────────────────────────────────────────────────────────
    public function showRegister()
    {
        // ✅ FIX: Tiada company selection — tourist daftar bebas
        return view('tourist.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:8|confirmed',
            'phone'      => 'required|string|max:20',
        ], [
            'name.required'      => 'Nama diperlukan.',
            'email.required'     => 'Emel diperlukan.',
            'email.unique'       => 'Emel ini sudah digunakan.',
            'password.required'  => 'Kata laluan diperlukan.',
            'password.min'       => 'Kata laluan mesti sekurang-kurangnya 8 aksara.',
            'password.confirmed' => 'Pengesahan kata laluan tidak sepadan.',
            'phone.required'     => 'No. telefon diperlukan.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'unique_id' => Str::uuid(),
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'phone'     => $request->phone,
            'role'      => User::ROLE_TOURIST,
        ]);

        Auth::login($user);

        return redirect()->route('tourist.dashboard')
            ->with('success', 'Selamat datang, ' . $user->name . '! Akaun anda berjaya didaftar.');
    }

    // ─── LOGIN ────────────────────────────────────────────────────────────────
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'tourist') {
            return redirect()->route('tourist.dashboard');
        }
        return view('tourist.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('role', User::ROLE_TOURIST)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Emel atau kata laluan tidak sah.'])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('tourist.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('tourist.index');
    }

    // ─── DASHBOARD ───────────────────────────────────────────────────────────
    public function dashboard(Request $request)
    {
        $user    = Auth::user();
        $company = $user->company;

        // ✅ FIX: Tunjuk SEMUA trip — upcoming boleh booking, past view only
        $query = Trip::with('company');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('location', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        $trips = $query->orderBy('date_from', 'desc')->get();
        $trips->each(fn($trip) => $this->injectTripImages($trip));

        // ✅ FIX: Guna date_from — trip yang dah berlepas TIDAK boleh booking
        // Walaupun date_to belum lepas, kalau trip dah bertolak = tidak boleh tempah
        $today         = now()->toDateString();
        $upcomingTrips = $trips->filter(fn($t) => $t->date_from >= $today)->sortBy('date_from')->values();
        $pastTrips     = $trips->filter(fn($t) => $t->date_from < $today)->sortByDesc('date_from')->values();

        $bookings = Booking::with(['trip.company'])
            ->whereHas('customer', function ($q) use ($user) {
                $q->where('email', $user->email);
            })
            ->orderByDesc('created_at')
            ->get();

        return view('tourist.dashboard', compact('user', 'company', 'trips', 'upcomingTrips', 'pastTrips', 'bookings'));
    }

    // ─── TRIP DETAIL ─────────────────────────────────────────────────────────
    public function tripDetail($id)
{
    $trip = Trip::with(['company'])->findOrFail($id);

    try {
        $trip->load(['itineraries' => fn($q) => $q->orderBy('day_number')]);
    } catch (\Exception $e) {}

    $this->injectTripImages($trip);

    // ✅ Inject full URL untuk gambar itinerary — sama cara dengan trip images
    if ($trip->itineraries && $trip->itineraries->count()) {
        $trip->itineraries->each(function ($itin) {
            $itin->img_url = $this->resolveImageUrl($itin->img);
        });
    }

    return view('tourist.trip-detail', compact('trip'));
}

    // ─── BOOKING FORM ─────────────────────────────────────────────────────────────
public function showBooking($tripId)
{
    $user = Auth::user();
    $trip = Trip::with('company')->findOrFail($tripId);
    $this->injectTripImages($trip);

    // Ambil customer sedia ada
    $customer = Customer::where('email', $user->email)
        ->whereNull('deleted_at')
        ->first();

    // Ambil address
    $address = null;
    if ($customer) {
        $address = CustomerAddress::where('customer_id', $customer->unique_id)->first();
    }


    // First time atau tidak
    $isFirstTime = is_null($customer);

    return view('tourist.booking', compact('trip', 'user', 'customer', 'address', 'isFirstTime'));
}

    // ─── PROSES BOOKING ───────────────────────────────────────────────────────────
public function createBooking(Request $request)
{
    $validator = Validator::make($request->all(), [
        // Trip
        'trip_id'       => 'required|exists:trips,id',
        'total_amount'  => 'required|numeric|min:1',
        'pax_adult'     => 'required|integer|min:1',
        'pax_child'     => 'nullable|integer|min:0',
        'pax_baby'      => 'nullable|integer|min:0',
        'notes'         => 'nullable|string|max:500',

        // Ketua pelancong — wajib
        'tourist_name'  => 'required|string|max:255',
        'tourist_ic'    => 'required|string|max:20',
        'tourist_phone' => 'required|string|max:20',

        // Alamat — wajib untuk first-time
        'address_line'  => 'required|string|max:255',
        'postcode'      => 'required|string|max:5',
        'state_id'      => 'required|exists:states,id',

        // Optional customer fields
        'country_of_birth' => 'nullable|string|max:100',
        'passport_no'      => 'nullable|string|max:30',
        'pp_warganegara'   => 'nullable|string|max:100',
        'pp_tarikh_lahir'  => 'nullable|date',
        'pp_gender'        => 'nullable|in:M,F',
        'pp_date_issued'   => 'nullable|date',
        'pp_date_expired'  => 'nullable|date',
        'pp_issuing_office'=> 'nullable|string|max:255',

        // Pelancong tambahan (optional)
        'extra_travelers'             => 'nullable|array|max:10',
        'extra_travelers.*.name'      => 'required_with:extra_travelers|string|max:255',
        'extra_travelers.*.ic'        => 'required_with:extra_travelers|string|max:20',
        'extra_travelers.*.type'      => 'nullable|in:adult,child,baby',
        'extra_travelers.*.gender'    => 'nullable|string|max:20',
        'extra_travelers.*.dob'       => 'nullable|date',
        'extra_travelers.*.passport_no' => 'nullable|string|max:30',
    ], [
        'tourist_name.required'  => 'Nama diperlukan.',
        'tourist_ic.required'    => 'No. IC diperlukan.',
        'tourist_phone.required' => 'No. telefon diperlukan.',
        'address_line.required'  => 'Alamat diperlukan.',
        'postcode.required'      => 'Poskod diperlukan.',
        'state_id.required'      => 'Negeri diperlukan.',
        'pax_adult.min'          => 'Sekurang-kurangnya 1 dewasa diperlukan.',
        'extra_travelers.*.name.required_with' => 'Nama pelancong tambahan diperlukan.',
        'extra_travelers.*.ic.required_with'   => 'No. IC pelancong tambahan diperlukan.',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $user = Auth::user();
    $trip = Trip::with('company')->findOrFail($request->trip_id);

    // ✅ Server-side: trip sudah berlepas
    if ($trip->date_from < now()->toDateString()) {
        return back()->withErrors(['trip_id' => 'Trip ini sudah berlepas dan tidak boleh ditempah.'])->withInput();
    }

    $icClean = preg_replace('/[^0-9]/', '', $request->tourist_ic);

    // ✅ Single source of truth — upsert customer record
    $customer = Customer::updateOrCreate(
        ['email' => $user->email],
        [
            'unique_id'         => Customer::where('email', $user->email)->value('unique_id') ?? Str::uuid(),
            'name'              => $request->tourist_name,
            'ic'                => $icClean,
            'phone'             => $request->tourist_phone,
            'company_id'        => $trip->company_id,
            'ref_id'            => Customer::where('email', $user->email)->value('ref_id') ?? ('TRS-' . strtoupper(Str::random(6))),
            'state_id'          => $request->state_id,
            'address_line'      => $request->address_line,
            'postcode'          => $request->postcode,
            'country_of_birth'  => $request->country_of_birth,
            'passport_no'       => $request->passport_no,
            'pp_warganegara'    => $request->pp_warganegara,
            'pp_tarikh_lahir'   => $request->pp_tarikh_lahir,
            'pp_gender'         => $request->pp_gender,
            'pp_date_issued'    => $request->pp_date_issued,
            'pp_date_expired'   => $request->pp_date_expired,
            'pp_issuing_office' => $request->pp_issuing_office,
        ]
    );
    CustomerAddress::updateOrCreate(
    ['customer_id' => $customer->unique_id],
    [
        'address_line' => $request->address_line,
        'postcode'     => $request->postcode,
        'state_id'     => $request->state_id,
    ]
);

    // ✅ Susun semua traveler_details — ketua + tambahan
    $travelerDetails = [];

    // Ketua pelancong (dari customer record)
    $travelerDetails[] = [
        'order'       => 1,
        'type'        => 'adult',
        'is_lead'     => true,
        'name'        => $request->tourist_name,
        'ic'          => $request->tourist_ic,
        'dob'         => Customer::parseDOBFromIC($icClean),
        'gender'      => ((int) substr($icClean, -1) % 2 === 0) ? 'Perempuan' : 'Lelaki',
        'passport_no' => $request->passport_no,
        'pp_expire'   => $request->pp_date_expired,
        'nationality' => $request->pp_warganegara,
    ];

    // Pelancong tambahan
    if ($request->filled('extra_travelers')) {
        foreach ($request->extra_travelers as $i => $et) {
            $etIcClean = preg_replace('/[^0-9]/', '', $et['ic'] ?? '');
            $travelerDetails[] = [
                'order'       => $i + 2,
                'type'        => $et['type']        ?? 'adult',
                'is_lead'     => false,
                'name'        => $et['name']         ?? '',
                'ic'          => $et['ic']            ?? '',
                'dob'         => $et['dob']           ?? (strlen($etIcClean) >= 6 ? Customer::parseDOBFromIC($etIcClean) : null),
                'gender'      => $et['gender']        ?? null,
                'passport_no' => $et['passport_no']  ?? null,
                'pp_expire'   => $et['pp_expire']     ?? null,
                'nationality' => $et['nationality']   ?? null,
            ];
        }
    }

    $bookingNo = 'TRS-' . date('ymd') . '-' . strtoupper(Str::random(4));

    $booking = Booking::create([
        'unique_id'         => Str::uuid(),
        'booking_no'        => $bookingNo,
        'customer_id'       => $customer->unique_id,
        'trip_id'           => $trip->id,
        'total_amount'      => $request->total_amount,
        'pax_adult'         => $request->pax_adult,
        'pax_child'         => $request->pax_child ?? 0,
        'pax_baby'          => $request->pax_baby  ?? 0,
        'payment_status'    => 'unpaid',
        'deposit_status'    => 'not_paid',
        'booking_status'    => 'pending',
        'notes'             => $request->notes,
        'traveler_details'  => $travelerDetails,
        'details_completed' => true, // ✅ Dah isi masa booking
        'source'            => 'tourist_portal',
    ]);

    // WhatsApp notification
    $this->whatsapp->sendBookingNotification([
        'booking_no'     => $bookingNo,
        'customer_name'  => $request->tourist_name,
        'customer_phone' => $request->tourist_phone,
        'customer_ic'    => $request->tourist_ic,
        'trip_location'  => $trip->location,
        'trip_country'   => $trip->country   ?? '-',
        'trip_date_from' => $trip->date_from  ?? '-',
        'trip_date_to'   => $trip->date_to    ?? '-',
        'total_amount'   => $request->total_amount,
        'payment_status' => 'Belum Bayar',
        'company_name'   => $trip->company->name ?? '-',
        'notes'          => $request->notes,
    ]);

    return redirect()->route('tourist.booking.success', $booking->id)
        ->with('success', 'Booking berjaya dihantar!');
}

    // ─── BOOKING SUCCESS ─────────────────────────────────────────────────────
    public function bookingSuccess($bookingId)
    {
        $booking  = Booking::with(['trip.company', 'customer'])->findOrFail($bookingId);
        $waResult = session('wa_result');

        $waAdminLink = $this->whatsapp->getAdminWaLink(
            '🎉 Booking No: ' . $booking->booking_no . ' - Saya ingin mengesahkan booking saya.'
        );

        return view('tourist.booking-success', compact('booking', 'waResult', 'waAdminLink'));
    }
    // ─── SIMPAN MAKLUMAT PELANCONG ─────────────────────────────────────────────
public function saveTravelerDetails(Request $request, $bookingId)
{
    $user    = Auth::user();
    $booking = Booking::whereHas('customer', function ($q) use ($user) {
        $q->where('email', $user->email);
    })->where('booking_status', 'confirmed')->findOrFail($bookingId);

    $travelers = $request->input('travelers', []);

    // Validate each traveler has at least a name
    foreach ($travelers as $i => $t) {
        if (empty($t['name'])) {
            return back()->withErrors(["travelers.{$i}.name" => "Nama pelancong " . ($i + 1) . " diperlukan."])->withInput();
        }
    }

    $booking->update([
        'traveler_details'  => $travelers,
        'details_completed' => true,
    ]);

    return redirect()->route('tourist.my-bookings')
        ->with('success', '✅ Maklumat pelancong berjaya disimpan! Anda boleh cetak PDF sekarang.');
}

// ─── PDF RECEIPT ───────────────────────────────────────────────────────────
public function bookingPdf($bookingId)
{
    $user    = Auth::user();
    $booking = Booking::with(['trip.company', 'customer'])
        ->whereHas('customer', function ($q) use ($user) {
            $q->where('email', $user->email);
        })->findOrFail($bookingId);

    if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tourist.booking-pdf', compact('booking'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('Resit_' . $booking->booking_no . '.pdf');
    }

    return view('tourist.booking-pdf', compact('booking'));
}


    // ─── MY BOOKINGS ─────────────────────────────────────────────────────────
    public function myBookings()
    {
        $user = Auth::user();

        $bookings = Booking::with(['trip.company', 'customer'])
            ->whereHas('customer', function ($q) use ($user) {
                $q->where('email', $user->email);
            })
            ->orderByDesc('created_at')
            ->get();

        return view('tourist.my-bookings', compact('bookings'));
    }

    // ─── LIKE / DISLIKE ───────────────────────────────────────────────────────
    public function toggleLike(Request $request, $tripId)
    {
        $trip = Trip::findOrFail($tripId);
        $type = $request->input('type'); // 'like' or 'dislike'

        if (!in_array($type, ['like', 'dislike'])) {
            return response()->json(['error' => 'Invalid type'], 422);
        }

        $userId = Auth::id();

        // Cari vote sedia ada
        $existing = TripLike::where('trip_id', $tripId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            if ($existing->type === $type) {
                // Klik sama — batalkan vote
                $existing->delete();
                $myVote = null;
            } else {
                // Tukar vote
                $existing->update(['type' => $type]);
                $myVote = $type;
            }
        } else {
            TripLike::create([
                'trip_id'    => $tripId,
                'user_id'    => $userId,
                'ip_address' => $request->ip(),
                'type'       => $type,
            ]);
            $myVote = $type;
        }

        // Kira semula
        $likes    = TripLike::where('trip_id', $tripId)->where('type', 'like')->count();
        $dislikes = TripLike::where('trip_id', $tripId)->where('type', 'dislike')->count();

        return response()->json([
            'likes'    => $likes,
            'dislikes' => $dislikes,
            'myVote'   => $myVote,
        ]);
    }

    // ─── DAPATKAN LIKES (untuk semua trip sekaligus) ───────────────────────────
    public function getLikes(Request $request)
    {
        $tripIds = $request->input('trip_ids', []);
        $userId  = Auth::id();
        $result  = [];

        foreach ($tripIds as $tripId) {
            $likes    = TripLike::where('trip_id', $tripId)->where('type', 'like')->count();
            $dislikes = TripLike::where('trip_id', $tripId)->where('type', 'dislike')->count();
            $myVote   = TripLike::where('trip_id', $tripId)
                            ->where('user_id', $userId)
                            ->value('type');

            $result[$tripId] = [
                'likes'    => $likes,
                'dislikes' => $dislikes,
                'myVote'   => $myVote,
            ];
        }

        return response()->json($result);
    }

    // ─── KOMEN ────────────────────────────────────────────────────────────────
    public function getComments($tripId)
    {
        $comments = TripComment::where('trip_id', $tripId)
            ->with('user:id,name')
            ->latest()
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'user_name'  => $c->user->name ?? 'Pengguna',
                'comment'    => $c->comment,
                'rating'     => $c->rating,
                'created_at' => $c->created_at->diffForHumans(),
                'is_mine'    => $c->user_id === Auth::id(),
            ]);

        return response()->json($comments);
    }

    public function postComment(Request $request, $tripId)
    {
        $trip = Trip::findOrFail($tripId);

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|min:3|max:500',
            'rating'  => 'nullable|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $comment = TripComment::create([
            'trip_id' => $tripId,
            'user_id' => Auth::id(),
            'comment' => trim($request->comment),
            'rating'  => $request->rating,
        ]);

        $comment->load('user:id,name');

        return response()->json([
            'id'         => $comment->id,
            'user_name'  => $comment->user->name,
            'comment'    => $comment->comment,
            'rating'     => $comment->rating,
            'created_at' => $comment->created_at->diffForHumans(),
            'is_mine'    => true,
        ], 201);
    }

    public function deleteComment($commentId)
    {
        $comment = TripComment::where('id', $commentId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $comment->delete();
        return response()->json(['deleted' => true]);
    }
    public function visitorPing(Request $request)
{
    $sessionId = $request->input('session_id');

    if (!$sessionId || strlen($sessionId) > 100) {
        return response()->json(['error' => 'Invalid'], 400);
    }

    $now = \Carbon\Carbon::now();

    DB::table('visitor_pings')->upsert(
        [
            'session_id' => $sessionId,
            'last_ping'  => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        ['session_id'],
        ['last_ping', 'updated_at']
    );

    $active = DB::table('visitor_pings')
        ->where('last_ping', '>=', $now->copy()->subSeconds(20))
        ->count();

    $total = DB::table('visitor_pings')->count();

    // Housekeeping — buang rekod lama (10% chance)
    if (rand(1, 10) === 1) {
        DB::table('visitor_pings')
            ->where('last_ping', '<', $now->copy()->subHours(24))
            ->delete();
    }

    return response()->json([
        'active' => $active,
        'total'  => $total,
    ]);
}

}