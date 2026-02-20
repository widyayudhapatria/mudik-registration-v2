<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationImport extends Model
{
    use HasFactory;

    protected $table = 'registration_imports';

    protected $fillable = [
        'admin_id',
        'destination_id',
        'filename',
        'total_registrations',
        'total_participants',
        'successful',
        'failed',
        'error_details',
        'status',
        'notes',
    ];

    protected $casts = [
        'error_details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the admin who performed the import
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the destination for this import
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
