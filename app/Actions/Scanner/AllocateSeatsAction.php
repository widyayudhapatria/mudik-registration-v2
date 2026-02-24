<?php

namespace App\Actions\Scanner;

use App\Models\Admin;
use App\Models\Registration;
use App\Models\ScanLog;
use App\Models\SeatAllocation;
use App\Models\SeatCounter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class AllocateSeatsAction
{
    use AsAction;

    /**
     * Allocate seats for registration participants.
     *
     * @param Registration $registration
     * @param ScanLog $scanLog
     * @param Admin $admin
     * @return Collection<SeatAllocation>
     */

    public function handle(Registration $registration, ScanLog $scanLog, Admin $admin): Collection
    {
        return DB::transaction(function () use ($registration, $scanLog, $admin) {
            // Lock destination counter FOR UPDATE (prevent race condition)
            $counter = SeatCounter::where('destination_id', $registration->destination_id)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                // Create counter if not exists
                $counter = SeatCounter::create([
                    'destination_id' => $registration->destination_id,
                    'current_bus_number' => 1,
                    'current_seat_number' => 0,
                ]);
            }

            $seatAllocations = collect();

            // Loop through participants
            foreach ($registration->participants as $participant) {
                if ($participant->is_child_under_4) {
                    // No seat for child <4
                    $seatAllocations->push(SeatAllocation::create([
                        'registration_id' => $registration->id,
                        'participant_id' => $participant->id,
                        'destination_id' => $registration->destination_id,
                        'scan_log_id' => $scanLog->id,
                        'bus_number' => null,
                        'seat_number' => null,
                        'seat_code' => 'NO-SEAT',
                        'assigned_at' => now(),
                        'assigned_by' => $admin->id,
                    ]));
                    continue;
                }

                // Increment seat counter and handle bus overflow
                $seatInfo = $counter->incrementSeat();

                $seatCode = sprintf(
                    '%d-%s-%d',
                    $seatInfo['bus_number'],
                    $registration->destination->code,
                    $seatInfo['seat_number']
                );

                // Create seat allocation
                $seatAllocations->push(SeatAllocation::create([
                    'registration_id' => $registration->id,
                    'participant_id' => $participant->id,
                    'destination_id' => $registration->destination_id,
                    'scan_log_id' => $scanLog->id,
                    'bus_number' => $seatInfo['bus_number'],
                    'seat_number' => $seatInfo['seat_number'],
                    'seat_code' => $seatCode,
                    'assigned_at' => now(),
                    'assigned_by' => $admin->id,
                ]));
            }

            // Save counter
            $counter->last_allocation_at = now();
            $counter->save();

            Log::info('Seats allocated successfully', [
                'registration_id' => $registration->id,
                'destination_id' => $registration->destination_id,
                'seats_allocated' => $seatAllocations->filter(fn($s) => !$s->isNoSeat())->count(),
                'no_seat_count' => $seatAllocations->filter(fn($s) => $s->isNoSeat())->count(),
                'total_participants' => $registration->participants->count(),
            ]);

            return $seatAllocations;
        });
    }
}
