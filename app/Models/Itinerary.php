<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\RefIdGenerator;
use Illuminate\Support\Facades\Storage;

class Itinerary extends Model
{
    protected $fillable = [
        'ref_id', 'trip_id', 'day_number', 'day_date',
        'title', 'description', 'time_from', 'time_to', 'img',
    ];
    protected $casts = [
    'day_date'  => 'date:Y-m-d',
    'time_from' => 'string',
    'time_to'   => 'string',
];

    protected $appends = ['img_url'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->ref_id)) {
                $model->ref_id = RefIdGenerator::itinerary();
            }
        });
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    // ── Accessor: full URL untuk img ──────────────────────────
    public function getImgUrlAttribute(): ?string
{
    if (!$this->img) return null;
    if (str_starts_with($this->img, 'http')) return $this->img;
    return asset('storage/' . ltrim($this->img, '/'));
}
}