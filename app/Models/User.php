<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // ─── ROLE CONSTANTS ─────────────────────────────────────────────────────
    const ROLE_TESTER         = 'tester';          // ID 0 — demo mode, no DB writes
    const ROLE_ADMIN_SYSTEM   = 'admin_system';    // ID 1 — full access
    const ROLE_OWNER_BUSINESS = 'owner_business';  // ID 2 — limited business access
    const ROLE_TOURIST        = 'tourist';         // ID 3 — tourist portal only

    // ─── FILLABLE ────────────────────────────────────────────────────────────
    protected $fillable = [
        'unique_id',
        'name',
        'email',
        'google_id',
        'facebook_id',
        'password',
        'role',
        'is_online',
        'last_seen',
        'last_seen_at',
        'avatar',
        'company_id',   // untuk tourist — company yang dipilih
        'phone',        // untuk tourist — no telefon
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'last_seen_at'      => 'datetime',
        'is_online'         => 'boolean',
    ];

    // ─── BOOT ────────────────────────────────────────────────────────────────
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            if (empty($user->unique_id)) {
                $user->unique_id = Str::uuid();
            }
        });
    }

    // ─── ROLE HELPERS ────────────────────────────────────────────────────────

    public function isTester(): bool
    {
        return $this->role === self::ROLE_TESTER;
    }

    public function isAdminSystem(): bool
    {
        return $this->role === self::ROLE_ADMIN_SYSTEM;
    }

    public function isOwnerBusiness(): bool
    {
        return $this->role === self::ROLE_OWNER_BUSINESS;
    }

    public function isTourist(): bool
    {
        return $this->role === self::ROLE_TOURIST;
    }

    public function canAccessAdmin(): bool
    {
        return in_array($this->role, [
            self::ROLE_TESTER,
            self::ROLE_ADMIN_SYSTEM,
            self::ROLE_OWNER_BUSINESS,
        ]);
    }

    public function getRoleNameAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_TESTER         => 'Tester (Demo)',
            self::ROLE_ADMIN_SYSTEM   => 'Admin System',
            self::ROLE_OWNER_BUSINESS => 'Owner Business',
            self::ROLE_TOURIST        => 'Tourist',
            default                   => ucfirst($this->role),
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        // Kalau ada avatar tersimpan dalam storage
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        // Default: SVG avatar dengan inisial nama
        $initials = strtoupper(substr($this->name ?? '?', 0, 1));
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
             . '<rect width="100" height="100" rx="50" fill="#667eea"/>'
             . '<text x="50" y="65" text-anchor="middle" font-family="Arial,sans-serif" '
             . 'font-size="45" font-weight="bold" fill="white">' . $initials . '</text>'
             . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function getRoleColorAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_TESTER         => '#ff9800',
            self::ROLE_ADMIN_SYSTEM   => '#9c27b0',
            self::ROLE_OWNER_BUSINESS => '#2196f3',
            self::ROLE_TOURIST        => '#4caf50',
            default                   => '#666',
        };
    }

    public function getPermissionsAttribute(): array
    {
        return match ($this->role) {
            self::ROLE_TESTER, self::ROLE_ADMIN_SYSTEM => [
                'companies', 'customers', 'trips', 'bookings', 'receipts',
                'settings', 'users', 'reports',
            ],
            self::ROLE_OWNER_BUSINESS => [
                'companies', 'customers', 'trips', 'bookings', 'receipts', 'settings',
            ],
            self::ROLE_TOURIST => [
                'tourist_trips', 'tourist_bookings',
            ],
            default => [],
        };
    }

    public function can_do(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }

    // ─── ONLINE STATUS HELPERS ───────────────────────────────────────────────

    /**
     * Update online status
     *
     * @param bool $isOnline
     */
    public function updateOnlineStatus(bool $isOnline = true): void
    {
        $this->is_online = $isOnline;
        $this->last_seen_at = now();
        $this->save();
    }

    /**
     * Check if user is online
     */
    public function isOnline(): bool
    {
        return $this->is_online;
    }

    // ─── RELATIONSHIPS ───────────────────────────────────────────────────────

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }
}