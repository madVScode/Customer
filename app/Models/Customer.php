<?php
// ============================================================
// app/Models/Customer.php
// Version : 2.4.0
// Tujuan  : Model Eloquent untuk table 'customers'
// ============================================================
// STRUKTUR:
//   [CONFIG]    $primaryKey, $fillable, $casts
//   [BOOT]      Auto-set ref_id, DOB, gender semasa create/update
//   [ACCESSOR]  getAgeAttribute() — kira umur dinamik dari DOB/IC
//   [HELPER]    calculateAgeFromIC() — kira umur dari nombor IC
//   [HELPER]    parseDOBFromIC()    — parse tarikh lahir dari IC
//   [RELATIONS] state, address ade model sendiri, company, receipts, bookings
// ============================================================
// ⚠️  NOTA PENTING — PRIMARY KEY:
//     Model ini guna 'unique_id' (UUID) sebagai PK, BUKAN 'id'
//     Kesan: Rule::unique()->ignore() MESTI nyatakan nama kolum:
//     → Rule::unique('customers','email')->ignore($uid, 'unique_id')
// ============================================================
// ⚠️  NOTA PENTING — KOLUM 'age':
//     Kolum 'age' TIDAK WUJUD dalam table customers.
//     Umur dikira secara dinamik dari kolum 'dob' melalui
//     accessor getAgeAttribute() — jangan sesekali tambah
//     'age' dalam $fillable atau dalam Customer::create([...])
// ============================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\RefIdGenerator;
use Carbon\Carbon;
use App\Models\Traits\TracksCreator;

class Customer extends Model
{
    use SoftDeletes, TracksCreator;

    // ============================================================
    // KONFIGURASI MODEL
    // ============================================================

    // PK adalah UUID (bukan auto-increment integer)
    protected $primaryKey = 'unique_id';
    protected $keyType    = 'string';
    public    $incrementing = false;

    // Kolum yang boleh diisi secara mass-assignment
    // ⚠️  'age' TIDAK ada di sini — age dikira dari 'dob'
    protected $fillable = [
        'ref_id',
        'unique_id',
        'company_id',
        'ic',
        'dob',
        'name',
        'email',
        'phone',
        'gender',
        'country_of_birth',
        'passport_no',
        'state_id',

        // Maklumat Passport
        'pp_warganegara',
        'pp_tarikh_lahir',
        'pp_gender',
        'pp_date_issued',
        'pp_date_expired',
        'pp_issuing_office',
        'created_by'
    ];

    // Cast kolum tarikh supaya jadi Carbon secara automatik
    protected $casts = [
        'deleted_at'      => 'datetime',
        'dob'             => 'date',
        'pp_tarikh_lahir' => 'date',
        'pp_date_issued'  => 'date',
        'pp_date_expired' => 'date',
        
    ];

    // Append 'age' dalam setiap JSON response
    // Ini membolehkan $customer->age berfungsi dalam API response
    protected $appends = ['age'];


    // ============================================================
    // BOOT — Auto-set nilai semasa create & update
    // ============================================================
    protected static function boot(): void
    {
        parent::boot();

        // ── Semasa CREATE: auto-set ref_id, DOB, gender ────────
        static::creating(function (Customer $model) {

            // Auto-jana Ref ID unik jika belum ada
            if (empty($model->ref_id)) {
                $model->ref_id = RefIdGenerator::customer();
            }

            // Auto-parse DOB dari IC jika DOB belum diset
            if (!empty($model->ic) && empty($model->dob)) {
                $model->dob = static::parseDOBFromIC($model->ic);
            }

            // Auto-detect gender dari digit terakhir IC
            // Ganjil = Lelaki (male), Genap = Perempuan (female)
            if (!empty($model->ic) && empty($model->gender)) {
                $raw           = preg_replace('/\D/', '', $model->ic);
                $model->gender = ((int) substr($raw, -1) % 2 === 0)
                    ? 'female'
                    : 'male';
            }
        });

        // ── Semasa UPDATE: refresh DOB & gender jika IC berubah ─
        static::updating(function (Customer $model) {
            if ($model->isDirty('ic') && !empty($model->ic)) {
                $model->dob = static::parseDOBFromIC($model->ic);

                $raw           = preg_replace('/\D/', '', $model->ic);
                $model->gender = ((int) substr($raw, -1) % 2 === 0)
                    ? 'female'
                    : 'male';
            }
        });
    }


    // ============================================================
    // ACCESSOR — getAgeAttribute()
    // Kira umur secara dinamik dari 'dob' atau IC
    // TIDAK bergantung pada kolum 'age' dalam DB
    //
    // Prioriti:
    //   1. Kira dari field 'dob' (paling tepat)
    //   2. Fallback ke IC number jika 'dob' kosong
    // ============================================================
    public function getAgeAttribute(): ?int
    {
        // Kira dari DOB yang tersimpan dalam DB
        if (!empty($this->attributes['dob'])) {
            try {
                $age = Carbon::parse($this->attributes['dob'])->age;
                if ($age >= 0 && $age <= 130) {
                    return $age;
                }
            } catch (\Exception $e) {
                // DOB tidak valid, teruskan ke fallback
            }
        }

        // Fallback: kira dari IC number
        if (!empty($this->ic)) {
            return $this->calculateAgeFromIC($this->ic);
        }

        return null;
    }


    // ============================================================
    // PRIVATE HELPER — calculateAgeFromIC()
    // Kira umur dari nombor IC Malaysia (format: YYMMDD-PB-XXXX)
    // ============================================================
    private function calculateAgeFromIC(string $ic): ?int
    {
        $raw = preg_replace('/\D/', '', $ic);
        if (strlen($raw) < 6) return null;

        $yy          = (int) substr($raw, 0, 2);
        $mm          = (int) substr($raw, 2, 2);
        $dd          = (int) substr($raw, 4, 2);
        $currentYear = (int) date('Y');
        $year        = $yy > ($currentYear % 100) ? 1900 + $yy : 2000 + $yy;

        if ($mm < 1 || $mm > 12 || $dd < 1 || $dd > 31) return null;

        try {
            $age = Carbon::createFromDate($year, $mm, $dd)->age;
            return ($age >= 0 && $age <= 130) ? $age : null;
        } catch (\Exception $e) {
            return null;
        }
    }


    // ============================================================
    // PUBLIC STATIC HELPER — parseDOBFromIC()
    // Parse tarikh lahir dari IC — digunakan dalam boot()
    //
    // @param  string $ic  — IC number
    // @return string|null — Format: YYYY-MM-DD
    // ============================================================
    public static function parseDOBFromIC(string $ic): ?string
    {
        $raw = preg_replace('/\D/', '', $ic);
        if (strlen($raw) < 6) return null;

        $yy          = (int) substr($raw, 0, 2);
        $mm          = (int) substr($raw, 2, 2);
        $dd          = (int) substr($raw, 4, 2);
        $currentYear = (int) date('Y');
        $year        = $yy > ($currentYear % 100) ? 1900 + $yy : 2000 + $yy;

        if ($mm < 1 || $mm > 12 || $dd < 1 || $dd > 31) return null;

        try {
            return Carbon::createFromDate($year, $mm, $dd)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }


    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /** Negeri customer */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Alamat customer
     * FK: customer_addresses.customer_id → customers.unique_id
     */
    public function address()
    {
        return $this->hasOne(CustomerAddress::class, 'customer_id', 'unique_id');
    }

    /** Company customer */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /** Semua resit customer */
    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'customer_id', 'unique_id');
    }

    /** Semua booking customer */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'customer_id', 'unique_id');
    }
}