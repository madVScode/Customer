<?php

// ============================================================
// app/Http/Controllers/TripController.php  — Version 2.3.1
// ✅ Itinerary: update, day_date, img upload
// ✅ URL gambar penuh (url + Storage::url) untuk akses cross-device
// ============================================================

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Company;
use App\Models\Itinerary;
use App\Models\Trip;
use App\Http\Controllers\NotificationController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TripController extends Controller
{
    /* ─────────────────────────────────────────────────────────
     * Helper: Dapatkan senarai company ID berdasarkan role
     * ───────────────────────────────────────────────────────── */
    private function getCompanyIds(): array
    {
        $user = Auth::user();

        // Admin system nampak semua company
        if ($user->role === 'admin_system') {
            return Company::pluck('id')->toArray();
        }

        // Tester hanya nampak demo company (is_protected)
        // ✅ Tester guna company_id sendiri
if ($user->role === 'tester') {
    return $user->company_id ? [$user->company_id] : [];
}

        // Owner business tengok company sendiri
        if ($user->role === 'owner_business' && !empty($user->company_id)) {
            return [$user->company_id];
        }

        // Fallback: user biasa
        return Company::where('user_id', $user->id)->pluck('id')->toArray();
    }

    /* ─────────────────────────────────────────────────────────
     * Index: Senarai semua trip
     * ───────────────────────────────────────────────────────── */
    public function index(Request $request)
    {
        $query = Trip::with(['company', 'itineraries' => fn($q) => $q->orderBy('day_number')])
            ->whereIn('company_id', $this->getCompanyIds());

        // Tester sudah dihadkan melalui getCompanyIds() — tiada filter tambahan diperlukan
        if ($request->filled('company_id')) $query->where('company_id', $request->company_id);
        if ($request->filled('country'))    $query->where('country', 'like', '%' . $request->country . '%');
        if ($request->filled('date_from'))  $query->where('date_from', '>=', $request->date_from);

        $trips = $query->orderBy('date_from', 'desc')
            ->get()
            ->map(fn($t) => $this->formatTrip($t));

        return response()->json(['success' => true, 'data' => $trips]);
    }

    /* ─────────────────────────────────────────────────────────
     * Show: Butiran satu trip
     * ───────────────────────────────────────────────────────── */
    public function show($id)
    {
        $trip = Trip::with(['company', 'itineraries' => fn($q) => $q->orderBy('day_number')])
            ->whereIn('company_id', $this->getCompanyIds())
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $this->formatTrip($trip)]);
    }

    /* ─────────────────────────────────────────────────────────
     * Store: Tambah trip baru
     * ───────────────────────────────────────────────────────── */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id'    => 'required',
            'country'       => 'required|string|max:255',
            'state'         => 'nullable|string|max:255',
            'location'      => 'required|string|max:255',
            'national_code' => 'required|string|max:10',
            'date_from'     => 'required|date',
            'date_to'       => 'required|date|after_or_equal:date_from',
            'price_adult'   => 'required|numeric|min:0',
            'price_child'   => 'required|numeric|min:0',
            'price_baby'    => 'required|numeric|min:0',
            'deposit'       => 'required|numeric|min:0',
            'pic1'          => 'required|mimes:jpg,jpeg,png,webp|max:5120',
            'pic2'          => 'nullable|mimes:jpg,jpeg,png,webp|max:5120',
            'pic3'          => 'nullable|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Admin boleh assign trip ke mana-mana company
        // Owner business hanya boleh assign ke company sendiri
        $user = Auth::user();

if (in_array($user->role, ['owner_business', 'tester'])) {
    // Pastikan tester/owner hanya boleh create trip untuk company sendiri
    Company::where('id', $request->company_id)
           ->where('id', $user->company_id)
           ->firstOrFail();
} else {
    Company::findOrFail($request->company_id);
}

        $validated['pic1'] = $request->file('pic1')->store('trips/images', 'public');
        if ($request->hasFile('pic2')) $validated['pic2'] = $request->file('pic2')->store('trips/images', 'public');
        if ($request->hasFile('pic3')) $validated['pic3'] = $request->file('pic3')->store('trips/images', 'public');

        $trip = Trip::create($validated);

        // Simpan itineraries
        foreach (($request->itinerary ?? $request->itineraries ?? []) as $idx => $day) {
            if (empty(trim($day['title'] ?? ''))) continue;

            $itin = $trip->itineraries()->create([
                'day_number'  => (int) ($day['day_number'] ?? ($idx + 1)),
                'day_date'    => !empty($day['day_date']) ? Carbon::parse($day['day_date'])->format('Y-m-d') : null,
                'title'       => $day['title'],
                'description' => $day['description'] ?? null,
                'time_from'   => !empty($day['time_from']) ? $day['time_from'] : null,
                'time_to'     => !empty($day['time_to'])   ? $day['time_to']   : null,
            ]);

            // Upload gambar itinerary (optional)
            $imgKey = 'itineraries.' . $idx . '.img';
            if ($request->hasFile($imgKey)) {
                $itin->img = $request->file($imgKey)->store('itineraries/images', 'public');
                $itin->save();
            }
        }

        $trip->load(['company', 'itineraries' => fn($q) => $q->orderBy('day_number')]);
        NotificationController::notifyStaff(
    excludeUserId: Auth::id(),
    type: 'trip_created',
    title: 'Trip Baru Ditambah',
    message: "Trip {$trip->location}, {$trip->country} telah ditambah",
    data: [
        'trip_id'   => $trip->id,
        'location'  => $trip->location,
        'date_from' => $trip->date_from,
    ]
);

        return response()->json([
            'success' => true,
            'data'    => $this->formatTrip($trip),
            'message' => 'Trip berjaya ditambah',
        ]);
    }

    /* ─────────────────────────────────────────────────────────
     * Update: Kemaskini trip
     * ───────────────────────────────────────────────────────── */
    public function update(Request $request, $id)
    {
        $trip = Trip::whereIn('company_id', $this->getCompanyIds())->findOrFail($id);

        $validated = $request->validate([
            'location'      => 'required|string|max:255',
            'country'       => 'required|string|max:255',
            'state'         => 'nullable|string|max:255',
            'national_code' => 'required|string|max:10',
            'date_from'     => 'required|date',
            'date_to'       => 'required|date|after_or_equal:date_from',
            'price_adult'   => 'required|numeric|min:0',
            'price_child'   => 'required|numeric|min:0',
            'price_baby'    => 'required|numeric|min:0',
            'deposit'       => 'required|numeric|min:0',
            'pic1'          => 'nullable|mimes:jpg,jpeg,png,webp|max:5120',
            'pic2'          => 'nullable|mimes:jpg,jpeg,png,webp|max:5120',
            'pic3'          => 'nullable|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Handle gambar trip (upload baru atau padam)
        $picUpdates = [];
        foreach (['pic1', 'pic2', 'pic3'] as $pic) {
            if ($request->hasFile($pic)) {
                if ($trip->$pic) Storage::disk('public')->delete($trip->$pic);
                $picUpdates[$pic] = $request->file($pic)->store('trips/images', 'public');
            } elseif ($request->input('delete_' . $pic) === '1') {
                if ($trip->$pic) Storage::disk('public')->delete($trip->$pic);
                $picUpdates[$pic] = null;
            }
            unset($validated[$pic]);
        }

        $trip->update($validated);

        if (!empty($picUpdates)) {
            foreach ($picUpdates as $col => $val) {
                $trip->$col = $val;
            }
            $trip->save();
        }

        // Handle itineraries
        $itineraryData = $request->itinerary ?? $request->itineraries ?? [];

        if (!empty($itineraryData)) {
            $existingIds  = $trip->itineraries()->pluck('id')->toArray();
            $submittedIds = [];

            foreach ($itineraryData as $idx => $day) {
                if (empty(trim($day['title'] ?? ''))) continue;

                $itinId = !empty($day['id']) ? (int) $day['id'] : null;

                $fields = [
                    'day_number'  => (int) ($day['day_number'] ?? ($idx + 1)),
                    'day_date'    => !empty($day['day_date']) ? Carbon::parse($day['day_date'])->format('Y-m-d') : null,
                    'title'       => $day['title'],
                    'description' => $day['description'] ?? null,
                    'time_from'   => !empty($day['time_from']) ? $day['time_from'] : null,
                    'time_to'     => !empty($day['time_to'])   ? $day['time_to']   : null,
                ];

                // Upload gambar itinerary baru (optional)
                $imgKey = 'itineraries.' . $idx . '.img';
                if ($request->hasFile($imgKey)) {
                    if ($itinId) {
                        $existing = Itinerary::find($itinId);
                        if ($existing && $existing->img) {
                            Storage::disk('public')->delete($existing->img);
                        }
                    }
                    $fields['img'] = $request->file($imgKey)->store('itineraries/images', 'public');
                }

                if ($itinId && in_array($itinId, $existingIds)) {
                    // Kemaskini itinerary sedia ada
                    Itinerary::where('id', $itinId)->where('trip_id', $trip->id)->update($fields);
                    $submittedIds[] = $itinId;
                } else {
                    // Tambah itinerary baru
                    $newItin        = $trip->itineraries()->create($fields);
                    $submittedIds[] = $newItin->id;
                }
            }

            // Padam itinerary yang dibuang oleh user
            $toDelete = array_diff($existingIds, $submittedIds);
            if (!empty($toDelete)) {
                Itinerary::whereIn('id', $toDelete)->each(function ($i) {
                    if ($i->img) Storage::disk('public')->delete($i->img);
                });
                Itinerary::whereIn('id', $toDelete)->delete();
            }
        }

        $trip->load(['company', 'itineraries' => fn($q) => $q->orderBy('day_number')]);
        NotificationController::notifyStaff(
    excludeUserId: Auth::id(),
    type: 'trip_created',
    title: 'Trip Baru Ditambah',
    message: "Trip {$trip->location}, {$trip->country} telah ditambah",
    data: [
        'trip_id'   => $trip->id,
        'location'  => $trip->location,
        'date_from' => $trip->date_from,
    ]
);

        return response()->json([
            'success' => true,
            'data'    => $this->formatTrip($trip),
            'message' => 'Trip berjaya dikemaskini',
        ]);
    }

    /* ─────────────────────────────────────────────────────────
     * Destroy: Padam trip
     * ───────────────────────────────────────────────────────── */
    public function destroy($id)
    {
        $trip = Trip::whereIn('company_id', $this->getCompanyIds())->findOrFail($id);

        if ($trip->is_protected) {
            return response()->json([
                'success' => false,
                'message' => '🔒 Trip demo ini tidak boleh dipadam.',
            ], 403);
        }

        $active = Booking::where('trip_id', $trip->id)
            ->whereNull('deleted_at')
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->count();

        if ($active > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Trip tidak boleh dipadam — masih ada ' . $active . ' booking aktif.',
            ], 422);
        }

        // Padam gambar itinerary
        $trip->itineraries->each(function ($i) {
            if ($i->img) Storage::disk('public')->delete($i->img);
        });

        // Padam gambar trip
        foreach (['pic1', 'pic2', 'pic3'] as $p) {
            if ($trip->$p) Storage::disk('public')->delete($trip->$p);
        }

        // Simpan info sebelum delete (tambah sebelum $trip->delete())
$tripLocation = $trip->location;
$tripCountry  = $trip->country;
$tripId       = $trip->id;

$trip->delete();

// ✅ TAMBAH SINI
NotificationController::notifyStaff(
    excludeUserId: Auth::id(),
    type: 'trip_deleted',
    title: 'Trip Dipadam',
    message: "Trip {$tripLocation}, {$tripCountry} telah dipadam",
    data: [
        'trip_id'  => $tripId,
        'location' => $tripLocation,
    ]
);

        return response()->json(['success' => true, 'message' => 'Trip dipadam']);
    }

    /* ─────────────────────────────────────────────────────────
     * Trip Bookings: Senarai booking aktif untuk satu trip
     * ───────────────────────────────────────────────────────── */
    public function tripBookings($id)
    {
        $trip = Trip::whereIn('company_id', $this->getCompanyIds())->findOrFail($id);

        $bookings = Booking::with('customer')
            ->where('trip_id', $trip->id)
            ->whereNull('deleted_at')
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->get()
            ->map(fn($b) => [
                'id'             => $b->id,
                'booking_no'     => $b->booking_no,
                'booking_status' => $b->booking_status,
                'payment_status' => $b->payment_status,
                'deposit_status' => $b->deposit_status,
                'total_amount'   => $b->total_amount,
                'customer'       => [
                    'id'    => $b->customer?->unique_id,
                    'name'  => $b->customer?->name,
                    'ic'    => $b->customer?->ic,
                    'phone' => $b->customer?->phone,
                ],
            ]);

        return response()->json([
            'success'   => true,
            'data'      => $bookings,
            'trip_name' => $trip->location ?? $trip->country,
            'trip_id'   => $trip->id,
        ]);
    }

    /* ─────────────────────────────────────────────────────────
     * Remove Trip Booking: Batalkan booking dari trip
     * ───────────────────────────────────────────────────────── */
    public function removeTripBooking($tripId, $bookingId)
    {
        $trip    = Trip::whereIn('company_id', $this->getCompanyIds())->findOrFail($tripId);
        $booking = Booking::where('id', $bookingId)->where('trip_id', $trip->id)->firstOrFail();

        $booking->update(['booking_status' => 'cancelled']);
        NotificationController::notifyStaff(
    excludeUserId: Auth::id(),
    type: 'booking_cancelled',
    title: 'Booking Dibatalkan',
    message: "Booking #{$booking->booking_no} telah dibatalkan dari trip {$trip->location}",
    data: [
        'booking_id' => $booking->id,
        'trip_id'    => $trip->id,
    ]
);

        return response()->json(['success' => true, 'message' => 'Booking dikeluarkan dari trip']);
    }

    /* ─────────────────────────────────────────────────────────
     * Helper: Format data trip untuk response JSON
     * ───────────────────────────────────────────────────────── */
    private function formatTrip(Trip $trip): array
    {
        $dateFrom     = Carbon::parse($trip->date_from);
        $dateTo       = Carbon::parse($trip->date_to);
        $duration     = $dateFrom->diffInDays($dateTo) + 1;
        $bookingCount = Booking::where('trip_id', $trip->id)
            ->whereNull('deleted_at')
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->count();

        return [
            'id'             => $trip->id,
            'company_id'     => $trip->company_id,

            'company' => $trip->company ? [
                'id'   => $trip->company->id,
                'name' => $trip->company->name,

                // ✅ URL penuh supaya logo company boleh diakses dari device lain
                'logo' => $trip->company->logo
                          ? url(Storage::url($trip->company->logo))
                          : null,
            ] : null,

            'country'        => $trip->country,
            'state'          => $trip->state,
            'location'       => $trip->location,
            'national_code'  => $trip->national_code,
            'date_from'      => $trip->date_from,
            'date_to'        => $trip->date_to,
            'duration'       => $duration,
            'duration_label' => $duration . ' hari',
            'price_adult'    => $trip->price_adult,
            'price_child'    => $trip->price_child,
            'price_baby'     => $trip->price_baby,
            'deposit'        => $trip->deposit,

            // ✅ URL penuh untuk gambar trip
            'pic1_url' => $trip->pic1 ? url(Storage::url($trip->pic1)) : null,
            'pic2_url' => $trip->pic2 ? url(Storage::url($trip->pic2)) : null,
            'pic3_url' => $trip->pic3 ? url(Storage::url($trip->pic3)) : null,

            'itineraries' => ($trip->itineraries ?? collect())->map(fn($i) => [
                'id'          => $i->id,
                'day_number'  => $i->day_number,
                'day_date'    => $i->day_date ? Carbon::parse($i->day_date)->format('Y-m-d') : null,
                'title'       => $i->title,
                'description' => $i->description,
                'time_from'   => $i->time_from,
                'time_to'     => $i->time_to,

                // ✅ URL penuh untuk gambar itinerary
                'img_url' => $i->img ? url(Storage::url($i->img)) : null,
            ])->values(),

            'booking_count' => $bookingCount,
            'deleted_at'    => $trip->deleted_at,
        ];
    }
}