<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScanPerformed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $scanData;

    public function __construct(array $scanData)
    {
        $this->scanData = $scanData;
    }

    public function broadcastOn(): Channel
    {
        $channelName = config('mudik.pusher.scan_channel', 'scan-monitoring');
        
        return new Channel($channelName);
    }

    public function broadcastAs(): string
    {
        return config('mudik.pusher.scan_event', 'ScanPerformed');
    }

    public function broadcastWith(): array
    {
        return [
            'scan' => $this->scanData,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}