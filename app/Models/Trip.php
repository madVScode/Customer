<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Trip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'ref_id',
        'country',
        'state',
        'location',
        'national_code',
        'date_from',
        'date_to',
        'price_adult',
        'price_child',
        'price_baby',
        'deposit',
        'pic1',
        'pic2',
        'pic3',
        'is_protected',
    ];

    protected $casts = [
        'date_from'    => 'date',
        'date_to'      => 'date',
        'price_adult'  => 'decimal:2',
        'price_child'  => 'decimal:2',
        'price_baby'   => 'decimal:2',
        'deposit'      => 'decimal:2',
        'is_protected' => 'boolean',
    ];

    // ✅ CRITICAL FIX: Auto-calculate duration attribute
    protected $appends = ['duration'];

    /**
     * ✅ Get duration in days (auto-calculated from date_from and date_to)
     */
    public function getDurationAttribute()
    {
        if (!$this->date_from || !$this->date_to) {
            return null;
        }

        $dateFrom = Carbon::parse($this->date_from);
        $dateTo   = Carbon::parse($this->date_to);
        
        return $dateFrom->diffInDays($dateTo) + 1;
    }

    /**
     * ✅ Get duration label (e.g., "5 hari")
     */
    public function getDurationLabelAttribute()
    {
        $duration = $this->duration;
        return $duration ? $duration . ' hari' : null;
    }

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function itineraries()
    {
        return $this->hasMany(Itinerary::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }
}