<?php

namespace App\Events\v1;

use App\Models\v1\MatchEvent;
use App\Models\v1\MatchGame;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchEventOccurred implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MatchGame $match,
        public MatchEvent $event
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('matches.'.$this->match->id);
    }

    public function broadcastAs(): string
    {
        return 'match.event.occurred';
    }

    public function broadcastWith(): array
    {
        return [
            'match_id' => $this->match->id,
            'home_score' => $this->match->home_score,
            'away_score' => $this->match->away_score,
            'status' => $this->match->status,
            'current_period' => $this->match->current_period,
            'clock' => $this->match->formatted_clock,
            'event' => [
                'id' => $this->event->id,
                'team_id' => $this->event->team_id,
                'team_name' => $this->event->team->name,
                'player_name' => $this->event->player?->name ?? 'N/A',
                'sub_in_player_name' => $this->event->subInPlayer?->name,
                'jersey_number' => $this->event->player?->jersey_number,
                'event_type' => $this->event->event_type,
                'icon' => $this->event->icon,
                'label' => $this->event->label,
                'minute' => $this->event->minute,
                'second' => $this->event->second,
                'formatted_time' => $this->event->formatted_time,
                'notes' => $this->event->notes,
            ],
        ];
    }
}
