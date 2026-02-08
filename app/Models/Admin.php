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

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'can_scan',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'can_scan' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function hasPermission(string|MudikPermissions $permission): bool
    {
        // Convert enum to string if needed
        if ($permission instanceof MudikPermissions) {
            $permission = $permission->value;
        }

        // Get all permissions for this admin's role
        $permissions = $this->getAllPermissions();

        // Check if permission exists
        return in_array($permission, $permissions);
    }

    public function getAllPermissions(): array
    {
        return Cache::remember(
            "admin.{$this->id}.permissions",
            now()->addHour(),
            function () {
                try {
                    return MudikRoleList::from($this->role)->permissions();
                } catch (\ValueError $e) {
                    return [];
                }
            }
        );
    }

    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function clearPermissionCache(): void
    {
        Cache::forget("admin.{$this->id}.permissions");
    }

    public function approvedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'approved_by');
    }

    public function rejectedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'rejected_by');
    }

    public function scannedQrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class, 'scanned_by');
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isValidator(): bool
    {
        return $this->role === 'validator';
    }

    public function isScanner(): bool
    {
        return $this->role === 'scanner';
    }

    public function canValidate(): bool
    {
        return in_array($this->role, ['super_admin', 'validator']);
    }

    public function canScanQr(): bool
    {
        return $this->can_scan || $this->isSuperAdmin();
    }

    protected static function booted()
    {
        static::updated(function ($admin) {
            if ($admin->wasChanged('role')) {
                $admin->clearPermissionCache();
            }
        });

        static::deleted(function ($admin) {
            $admin->clearPermissionCache();
        });
    }
}