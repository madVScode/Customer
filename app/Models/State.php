<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $table = 'states';

    // =====================
    // PRIMARY KEY
    // =====================
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    // =====================
    // DEFAULT VALUE
    // =====================
    public const DEFAULT_STATE_NAME = 'Tidak Diketahui';

    // =====================
    // FILLABLE
    // =====================
    protected $fillable = [
        'name',
    ];

    // =====================
    // RELATIONSHIPS
    // =====================
    public function customers()
    {
        return $this->hasMany(
            \App\Models\Customer::class,
            'state_id', // FK di customers
            'id'        // PK di states
        );
    }
}
