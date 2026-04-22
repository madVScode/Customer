<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $table = 'addresses';

    protected $fillable = [
        'customer_id',
        'address_line',
        'postcode',
        // tiada state_id — tidak wujud dalam table
    ];

    protected $casts = [
        'customer_id' => 'string',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'unique_id');
    }
}