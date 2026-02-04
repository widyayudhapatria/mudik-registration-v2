<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StatisticsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $statistics;

    public function __construct(array $statistics)
    {
        $this->statistics = $statistics;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('statistics');
    }

    public function broadcastAs(): string
    {
        return 'StatisticsUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'statistics' => $this->statistics,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}