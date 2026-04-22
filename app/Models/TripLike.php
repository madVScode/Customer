<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripLike extends Model
{
    protected $fillable = ['trip_id', 'user_id', 'session_id', 'ip_address', 'type'];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}