<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TracksCreator;

class Receipt extends Model
{
    use SoftDeletes, TracksCreator;

    protected $fillable = [
        'receipt_no',
        'customer_id',
        'company_id',
        'trip_id',
        'booking_id',
        'total_amount',
        'deposit_amount',
        'pax_adult',
        'pax_child',
        'pax_baby',
        'payment_method',
        'notes',
        'issued_at',
        'created_by'
    ];

    protected $casts = [
        'issued_at'      => 'datetime',
        'total_amount'   => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'pax_adult'      => 'integer',
        'pax_child'      => 'integer',
        'pax_baby'       => 'integer',
    ];

    // =====================================================
    // Relationships
    // =====================================================
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'unique_id')
                    ->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
    
}