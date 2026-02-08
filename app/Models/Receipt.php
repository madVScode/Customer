<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Receipt extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'receipts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'receipt_no',
        'customer_id',
        'total_amount',
        'payment_method',
        'notes',
        'issued_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issued_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_amount',
        'formatted_date',
        'payment_method_label',
    ];

    /**
     * Get the customer that owns the receipt.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'unique_id');
    }

    /**
     * Get the formatted amount with currency symbol.
     *
     * @return string
     */
    public function getFormattedAmountAttribute()
    {
        return 'RM ' . number_format($this->total_amount, 2);
    }

    /**
     * Get the formatted date.
     *
     * @return string
     */
    public function getFormattedDateAttribute()
    {
        return $this->issued_at ? $this->issued_at->format('d/m/Y') : '';
    }

    /**
     * Get the human-readable payment method label.
     *
     * @return string
     */
    public function getPaymentMethodLabelAttribute()
    {
        $methods = [
            'cash' => 'Cash Payment',
            'online_banking' => 'Online Banking',
            'e-wallet' => 'E-Wallet',
            'credit_card' => 'Credit Card',
            'debit_card' => 'Debit Card',
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get the payment method icon.
     *
     * @return string
     */
    public function getPaymentMethodIconAttribute()
    {
        $icons = [
            'cash' => '💵',
            'online_banking' => '🏦',
            'e-wallet' => '📱',
            'credit_card' => '💳',
            'debit_card' => '💳',
        ];

        return $icons[$this->payment_method] ?? '💰';
    }

    /**
     * Scope a query to only include receipts from a specific date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $from
     * @param  string  $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('issued_at', [$from, $to]);
    }

    /**
     * Scope a query to only include receipts from today.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday($query)
    {
        return $query->whereDate('issued_at', Carbon::today());
    }

    /**
     * Scope a query to only include receipts from this month.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('issued_at', Carbon::now()->month)
                     ->whereYear('issued_at', Carbon::now()->year);
    }

    /**
     * Scope a query to only include receipts from this year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThisYear($query)
    {
        return $query->whereYear('issued_at', Carbon::now()->year);
    }

    /**
     * Scope a query to filter by payment method.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $method
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope a query to filter by customer.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $customerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope a query to search by receipt number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $receiptNo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchByReceiptNo($query, $receiptNo)
    {
        return $query->where('receipt_no', 'like', '%' . $receiptNo . '%');
    }

    /**
     * Scope a query to filter by amount range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  float  $min
     * @param  float  $max
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAmountBetween($query, $min, $max)
    {
        return $query->whereBetween('total_amount', [$min, $max]);
    }

    /**
     * Get the total amount for all receipts.
     *
     * @return float
     */
    public static function totalAmount()
    {
        return static::sum('total_amount');
    }

    /**
     * Get the total amount for today's receipts.
     *
     * @return float
     */
    public static function todayTotal()
    {
        return static::today()->sum('total_amount');
    }

    /**
     * Get the total amount for this month's receipts.
     *
     * @return float
     */
    public static function monthlyTotal()
    {
        return static::thisMonth()->sum('total_amount');
    }

    /**
     * Get the total amount for this year's receipts.
     *
     * @return float
     */
    public static function yearlyTotal()
    {
        return static::thisYear()->sum('total_amount');
    }

    /**
     * Get receipt statistics grouped by payment method.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function paymentMethodStats()
    {
        return static::selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set issued_at if not provided
        static::creating(function ($receipt) {
            if (!$receipt->issued_at) {
                $receipt->issued_at = Carbon::now();
            }
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }
}