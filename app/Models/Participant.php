<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'registration_id',
        'full_name',
        'nik_kia',
        'birth_date',
        'is_child_under_4',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'is_child_under_4' => 'boolean',
    ];

    /**
     * Get the registration that owns the participant.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Get the seat allocation for this participant.
     */
    public function seatAllocation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SeatAllocation::class);
    }

    /**
     * Calculate participant's age.
     */
    public function getAge(): int
    {
        return Carbon::parse($this->birth_date)->age;
    }

    /**
     * Check if participant is under 4 years old.
     */
    public function isUnderFourYears(): bool
    {
        return $this->getAge() < 4;
    }

    /**
     * Auto-set is_child_under_4 flag based on birth date.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->birth_date) {
                $model->is_child_under_4 = Carbon::parse($model->birth_date)->age < 4;
            }
        });
    }

    /**
     * Scope to filter children under 4.
     */
    public function scopeUnderFour($query)
    {
        return $query->where('is_child_under_4', true);
    }

    /**
     * Scope to filter by registration.
     */
    public function scopeForRegistration($query, int $registrationId)
    {
        return $query->where('registration_id', $registrationId);
    }
}
