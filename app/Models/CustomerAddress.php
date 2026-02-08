<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    use HasFactory;

    // Jika nama file & table mismatch, set table explicitly
    protected $table = 'addresses'; // pastikan table sama dengan migration

    // Fillable fields untuk mass assignment
    protected $fillable = [
        'customer_id',
        'address_line',
        'postcode',
    ];

    // Cast customer_id sebagai string kerana UUID
    protected $casts = [
        'customer_id' => 'string',
    ];

    /**
     * Relationship: Address belongs to a Customer
     * foreign key customer_id maps to unique_id on customers table
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'unique_id');
    }

    /**
     * Optional: helper to get full formatted address
     */
    public function getFullAddressAttribute()
    {
        return $this->address_line . ', ' . $this->postcode;
    }
}
