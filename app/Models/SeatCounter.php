<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatCounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'destination_id',
        'current_bus_number',
        'current_seat_number',
        'total_seats_allocated',
        'last_allocation_at',
    ];

    protected $casts = [
        'current_bus_number' => 'integer',
        'current_seat_number' => 'integer',
        'total_seats_allocated' => 'integer',
        'last_allocation_at' => 'datetime',
    ];

    /**
     * Get the destination for this counter.
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    /**
     * Increment seat counter and handle bus overflow.
     *
     * @return array ['bus_number' => int, 'seat_number' => int]
     */
    public function incrementSeat(): array
    {
        $this->current_seat_number++;

        // Check if seat exceeds max seats → Move to next bus
        if ($this->current_seat_number > config('mudik.counter_seat.bus_max_seats')) {
            $this->current_bus_number++;
            $this->current_seat_number = 1;
        }

        $this->total_seats_allocated++;

        return [
            'bus_number' => $this->current_bus_number,
            'seat_number' => $this->current_seat_number,
        ];
    }
}
