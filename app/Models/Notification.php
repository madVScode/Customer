<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'triggered_by',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * User who receives this notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * User who triggered this notification
     */
    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }

    /**
     * Notification icon (computed)
     */
    public function getIconAttribute()
    {
        return match ($this->type) {
            'customer_created' => '👤➕',
            'customer_updated' => '👤✏️',
            'customer_deleted' => '👤🗑️',
            'receipt_created' => '🧾➕',
            'receipt_updated' => '🧾✏️',
            'receipt_deleted' => '🧾🗑️',
            'data_exported' => '📥',
            'user_login' => '🔐',
            'user_logout' => '🚪',
            default => '🔔',
        };
    }

    /**
     * Notification color (computed)
     */
    public function getColorAttribute()
    {
        if (str_contains($this->type, 'created')) return '#4caf50';
        if (str_contains($this->type, 'updated')) return '#ff9800';
        if (str_contains($this->type, 'deleted')) return '#f44336';
        if (str_contains($this->type, 'exported')) return '#2196f3';

        return '#757575';
    }
}
