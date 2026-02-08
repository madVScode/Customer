<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'description',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * User who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute()
    {
        return match($this->action) {
            'created' => '➕',
            'updated' => '✏️',
            'deleted' => '🗑️',
            'exported' => '📥',
            'imported' => '📤',
            'viewed' => '👁️',
            default => '📝',
        };
    }

    /**
     * Get action color
     */
    public function getActionColorAttribute()
    {
        return match($this->action) {
            'created' => '#4caf50',
            'updated' => '#ff9800',
            'deleted' => '#f44336',
            'exported' => '#2196f3',
            'imported' => '#9c27b0',
            'viewed' => '#607d8b',
            default => '#757575',
        };
    }
}