<?php

namespace App\Events\v1;

use App\Models\v1\MatchGame;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchTimerUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MatchGame $match,
        public string $action
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('matches.'.$this->match->id);
    }

    public function broadcastAs(): string
    {
        return 'match.timer.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'match_id' => $this->match->id,
            'action' => $this->action,
            'current_period' => $this->match->current_period,
            'is_timer_running' => $this->match->is_timer_running,
            'elapsed_seconds' => $this->match->getCurrentClockSeconds(),
            'clock' => $this->match->formatted_clock,
            'status' => $this->match->status,
        ];
    }
}
