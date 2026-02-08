<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Notification;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_online',
        'last_seen',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen' => 'datetime',
        'is_online' => 'boolean',
        // 'password' => 'hashed', // aktifkan jika Laravel 10+
    ];

    /* ===================== RELATIONSHIPS ===================== */

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function triggeredNotifications()
    {
        return $this->hasMany(Notification::class, 'triggered_by');
    }

    /* ===================== ROLE CHECKS ===================== */

    public function isAdminManage(): bool
    {
        return $this->role === 'admin_manage';
    }

    public function isAdminRecord(): bool
    {
        return $this->role === 'admin_record';
    }

    public function getRoleNameAttribute(): string
    {
        return match ($this->role) {
            'admin_manage' => 'Admin Manage Data',
            'admin_record' => 'Admin Simpan Rekod',
            default => 'Unknown',
        };
    }

    /* ===================== ONLINE STATUS ===================== */

    public function updateOnlineStatus(bool $isOnline = true): bool
    {
        return $this->update([
            'is_online' => $isOnline,
            'last_seen' => now(),
        ]);
    }

    public function isRecentlyActive(): bool
    {
        return $this->last_seen
            ? $this->last_seen->diffInMinutes(now()) <= 5
            : false;
    }

    public function getOnlineStatusAttribute(): string
    {
        if ($this->is_online && $this->isRecentlyActive()) {
            return 'online';
        }

        if ($this->last_seen && $this->last_seen->diffInMinutes(now()) <= 30) {
            return 'away';
        }

        return 'offline';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->online_status) {
            'online' => '#4caf50',
            'away' => '#ff9800',
            'offline' => '#9e9e9e',
            default => '#9e9e9e',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->online_status) {
            'online' => '🟢',
            'away' => '🟡',
            'offline' => '⚫',
            default => '⚫',
        };
    }

    /* ===================== NOTIFICATIONS ===================== */

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()
            ->where('is_read', false)
            ->count();
    }

    /* ===================== AVATAR ===================== */

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name='
                . urlencode($this->name)
                . '&color=ffffff&background=4a90e2&size=128';
    }

    /* ===================== SCOPES ===================== */

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function scopeAdminManage($query)
    {
        return $query->where('role', 'admin_manage');
    }

    public function scopeAdminRecord($query)
    {
        return $query->where('role', 'admin_record');
    }
}
