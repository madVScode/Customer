<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    // =====================
    // PRIMARY KEY (UUID)
    // =====================
    protected $primaryKey = 'unique_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // =====================
    // FILLABLE
    // =====================
    protected $fillable = [
        'unique_id',
        'ic',
        'name',
        'email',
        'phone',
        'dob',
        'gender',
        'state_id',
    ];

    protected $dates = ['deleted_at'];

    // =====================
    // AUTO GENERATE UUID
    // =====================
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->unique_id)) {
                $model->unique_id = (string) Str::uuid();
            }
        });
    }

    // =====================
    // AUTO UMUR (DINAMIK)
    // =====================
    public function getAgeAttribute()
    {
        return $this->dob
            ? Carbon::parse($this->dob)->age
            : null;
    }

    // =====================
    // AUTO DOB DARI IC
    // =====================
    public static function extractDobFromIc($ic)
    {
        $ic = preg_replace('/\D/', '', $ic);

        if (strlen($ic) < 6) {
            return null;
        }

        $year  = substr($ic, 0, 2);
        $month = substr($ic, 2, 2);
        $day   = substr($ic, 4, 2);

        $currentYear = date('y');
        $fullYear = ($year <= $currentYear)
            ? '20' . $year
            : '19' . $year;

        return "$fullYear-$month-$day";
    }

    // =====================
    // AUTO GENDER DARI IC
    // =====================
    public static function extractGenderFromIc($ic)
    {
        $ic = preg_replace('/\D/', '', $ic);

        if (strlen($ic) < 12) {
            return null;
        }

        $lastDigit = (int) substr($ic, -1);
        return ($lastDigit % 2 === 0) ? 'female' : 'male';
    }

    // =====================
    // RELATIONSHIPS
    // =====================
    public function state()
    {
        return $this->belongsTo(
            State::class,
            'state_id', // FK di customers
            'id'        // PK di states
        );
    }

    public function address()
    {
        return $this->hasOne(
            CustomerAddress::class,
            'customer_id', // FK di customer_addresses
            'unique_id'    // PK di customers
        );
    }
}
