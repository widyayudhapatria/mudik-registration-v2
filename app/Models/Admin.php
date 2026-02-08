<?php

namespace App\Models;

use App\Enums\Permissions\MudikPermissions;
use App\Enums\Permissions\MudikRoleList;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'can_scan',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'can_scan' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function can($abilities, $arguments = []): bool
    {
        // If this is a policy check (not a string), use parent implementation
        if (is_array($abilities) || (!is_string($abilities) && !($abilities instanceof MudikPermissions))) {
            return parent::can($abilities, $arguments);
        }

        // Convert MudikPermissions enum to string if needed
        if ($abilities instanceof MudikPermissions) {
            $abilities = $abilities->value;
        }

        // Get all permissions for this admin's role
        $permissions = $this->getAllPermissions();

        // Check if permission exists in admin's permissions
        return in_array($abilities, $permissions);
    }

    public function getAllPermissions(): array
    {
        // Cache permissions for 1 hour to improve performance
        return Cache::remember(
            "admin.{$this->id}.permissions",
            now()->addHour(),
            function () {
                // Get permissions from role mapping
                try {
                    return MudikRoleList::from($this->role)->permissions();
                } catch (\ValueError $e) {
                    // If role doesn't exist in enum, return empty array
                    return [];
                }
            }
        );
    }

    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->can($permission)) {
                return false;
            }
        }
        return true;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }
        return false;
    }

    public function clearPermissionCache(): void
    {
        Cache::forget("admin.{$this->id}.permissions");
    }

    /**
     * Get the registrations approved by this admin.
     */
    public function approvedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'approved_by');
    }

    /**
     * Get the registrations rejected by this admin.
     */
    public function rejectedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'rejected_by');
    }

    /**
     * Get the QR codes scanned by this admin.
     */
    public function scannedQrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class, 'scanned_by');
    }

    /**
     * Get the scan logs for this admin.
     */
    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }

    /**
     * Check if admin is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if admin is validator.
     */
    public function isValidator(): bool
    {
        return $this->role === 'validator';
    }

    /**
     * Check if admin is scanner.
     */
    public function isScanner(): bool
    {
        return $this->role === 'scanner';
    }

    /**
     * Check if admin can approve/reject registrations.
     */
    public function canValidate(): bool
    {
        return in_array($this->role, ['super_admin', 'validator']);
    }

    /**
     * Check if admin can scan QR codes.
     */
    public function canScanQr(): bool
    {
        return $this->can_scan || $this->isSuperAdmin();
    }

    protected static function booted()
    {
        // Clear permission cache when admin role is updated
        static::updated(function ($admin) {
            if ($admin->wasChanged('role')) {
                $admin->clearPermissionCache();
            }
        });

        // Clear permission cache when admin is deleted
        static::deleted(function ($admin) {
            $admin->clearPermissionCache();
        });

        // Update last_login_at on successful authentication
        static::retrieved(function ($admin) {
            // This is handled by AuthController login method
        });
    }
}