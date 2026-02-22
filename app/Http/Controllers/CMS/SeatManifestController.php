<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\SeatAllocation;
use App\Models\SeatCounter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeatManifestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request): View
    {
        $destinations = Destination::where('is_active', true)->ordered()->get();

        $selectedDestinationId = $request->get('destination_id');
        $selectedBusNumber = $request->get('bus_number');

        // Get available bus numbers for selected destination
        $availableBuses = collect();
        if ($selectedDestinationId) {
            $availableBuses = SeatAllocation::where('destination_id', $selectedDestinationId)
                ->whereNotNull('bus_number')
                ->distinct()
                ->orderBy('bus_number')
                ->pluck('bus_number');
        }

        $query = SeatAllocation::with([
            'participant',
            'registration',
            'destination',
        ])
        ->whereNotNull('bus_number') // exclude NO-SEAT
        ->orderBy('bus_number')
        ->orderBy('seat_number');

        if ($selectedDestinationId) {
            $query->where('destination_id', $selectedDestinationId);
        }

        if ($selectedBusNumber) {
            $query->where('bus_number', $selectedBusNumber);
        }

        $seatAllocations = $query->get();

        // Summary stats
        $summary = null;
        if ($selectedDestinationId) {
            $counter = SeatCounter::where('destination_id', $selectedDestinationId)->first();
            $destination = $destinations->firstWhere('id', $selectedDestinationId);
            $summary = [
                'destination' => $destination,
                'total_seats' => $seatAllocations->count(),
                'total_buses' => $availableBuses->count(),
                'current_bus' => $counter?->current_bus_number ?? 0,
                'no_seat_count' => SeatAllocation::where('destination_id', $selectedDestinationId)
                    ->where('seat_code', 'NO-SEAT')->count(),
            ];
        }

        return view('cms.seat-manifest.index', [
            'destinations' => $destinations,
            'selectedDestinationId' => $selectedDestinationId,
            'selectedBusNumber' => $selectedBusNumber,
            'availableBuses' => $availableBuses,
            'seatAllocations' => $seatAllocations,
            'summary' => $summary,
        ]);
    }
}