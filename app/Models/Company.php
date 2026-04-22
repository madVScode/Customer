<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Helpers\RefIdGenerator;

class Company extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing  = false;
    protected $keyType    = 'string';
    protected $appends    = ['logo_url'];

    protected $fillable = [
        'id', 'ref_id', 'user_id', 'name','phone', 'registration_no', 'address', 'logo',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // ✅ FIX: Auto-generate id (UUID) jika kosong
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            if (empty($model->ref_id)) {
                $model->ref_id = RefIdGenerator::company();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'unique_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'company_id', 'id');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class, 'company_id', 'id');
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo
            ? asset('storage/' . $this->logo)
            : asset('images/default-company.png');
    }
}