<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    // use HasRoles;

    // protected $guard_name = 'admin';

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
}