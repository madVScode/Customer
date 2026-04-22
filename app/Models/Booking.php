<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TracksCreator;

class Booking extends Model
{
    use HasFactory, SoftDeletes, TracksCreator;

    protected $fillable = [
        'booking_no','customer_id','trip_id','company_id',
        'payment_status','deposit_status','total_amount',
        'booking_status','notes',
        'pax_adult','pax_child','pax_baby',
        'traveler_details','details_completed',
        'approved_at','rejection_reason',
        'source','created_by',
    ];

    protected $casts = [
        'total_amount'      => 'decimal:2',
        'traveler_details'  => 'array',   // ✅ JSON column — auto decode/encode
        'details_completed' => 'boolean',
        'approved_at'       => 'datetime',
    ];

    protected $appends = ['total_pax','status_label','payment_label'];

    // ── Relations ──────────────────────────────────────────────
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'unique_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // ── Accessors ──────────────────────────────────────────────
    public function getTotalPaxAttribute(): int
    {
        return ($this->pax_adult ?? 0) + ($this->pax_child ?? 0) + ($this->pax_baby ?? 0);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->booking_status) {
            'pending'   => 'Menunggu',
            'confirmed' => 'Disahkan',
            'cancelled' => 'Dibatal',
            'completed' => 'Selesai',
            default     => ucfirst($this->booking_status ?? '-'),
        };
    }

    public function getPaymentLabelAttribute(): string
    {
        return match($this->payment_status) {
            'unpaid'  => 'Belum Bayar',
            'partial' => 'Deposit',
            'paid'    => 'Lunas',
            default   => ucfirst($this->payment_status ?? '-'),
        };
    }

    // ── Static helpers ─────────────────────────────────────────
    public static function generateBookingNo(): string
    {
        return 'BKG-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}