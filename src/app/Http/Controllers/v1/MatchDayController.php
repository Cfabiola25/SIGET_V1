<?php

namespace App\Http\Controllers\v1;

use App\Events\v1\MatchEventOccurred;
use App\Events\v1\MatchTimerUpdated;
use App\Http\Controllers\Controller;
use App\Models\v1\MatchEvent;
use App\Models\v1\MatchGame;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchDayController extends Controller
{
    public function console(MatchGame $match): View
    {
        $this->authorizeRefereeOrAdmin($match);

        $match->load([
            'homeTeam.players.profile',
            'awayTeam.players.profile',
            'lineups.player.profile',
            'events.player',
            'events.subInPlayer',
            'tournament.rules',
            'venue',
        ]);

        return view('v1.matches.console', compact('match'));
    }

    public function updateTimer(Request $request, MatchGame $match): JsonResponse
    {
        $this->authorizeRefereeOrAdmin($match);
        abort_if($match->isLocked(), 403, 'El partido ha sido cerrado y firmado oficialmente. No se pueden alterar eventos ni cronómetro.');

        $validated = $request->validate([
            'action' => ['required', 'in:start,pause,start_1h,start_2h,halftime,end_match'],
        ]);

        $action = $validated['action'];
        $now = now();

        match ($action) {
            'start', 'start_1h' => $match->update([
                'is_timer_running' => true,
                'timer_started_at' => $now,
                'current_period' => 'first_half',
                'status' => 'scheduled',
            ]),
            'pause' => $match->update([
                'elapsed_seconds' => $match->getCurrentClockSeconds(),
                'is_timer_running' => false,
                'timer_started_at' => null,
            ]),
            'halftime' => $match->update([
                'elapsed_seconds' => max($match->getCurrentClockSeconds(), 45 * 60),
                'is_timer_running' => false,
                'timer_started_at' => null,
                'current_period' => 'halftime',
            ]),
            'start_2h' => $match->update([
                'elapsed_seconds' => max($match->getCurrentClockSeconds(), 45 * 60),
                'is_timer_running' => true,
                'timer_started_at' => $now,
                'current_period' => 'second_half',
            ]),
            'end_match' => $match->update([
                'elapsed_seconds' => $match->getCurrentClockSeconds(),
                'is_timer_running' => false,
                'timer_started_at' => null,
                'current_period' => 'ended',
            ]),
        };

        $match->refresh();

        event(new MatchTimerUpdated($match, $action));

        return response()->json([
            'success' => true,
            'current_period' => $match->current_period,
            'is_timer_running' => $match->is_timer_running,
            'elapsed_seconds' => $match->getCurrentClockSeconds(),
            'formatted_clock' => $match->formatted_clock,
            'status' => $match->status,
        ]);
    }

    public function recordEvent(Request $request, MatchGame $match): JsonResponse
    {
        $this->authorizeRefereeOrAdmin($match);
        abort_if($match->isLocked(), 403, 'El partido ha sido cerrado y firmado oficialmente. No se pueden alterar eventos.');

        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'event_type' => ['required', 'in:goal,yellow_card,red_card,substitution,injury'],
            'player_id' => ['nullable', 'exists:players,id'],
            'sub_in_player_id' => ['nullable', 'exists:players,id'],
            'minute' => ['nullable', 'integer', 'min:0', 'max:130'],
            'second' => ['nullable', 'integer', 'min:0', 'max:59'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        abort_unless($validated['team_id'] == $match->home_team_id || $validated['team_id'] == $match->away_team_id, 422);

        // Si no se suministra minuto o segundo, se calculan del cronómetro en tiempo real
        $currentSec = $match->getCurrentClockSeconds();
        $minute = $validated['minute'] ?? (int) floor($currentSec / 60);
        $second = $validated['second'] ?? ($currentSec % 60);

        $period = match ($match->current_period) {
            'second_half' => '2H',
            'halftime' => 'HT',
            default => '1H',
        };

        // Si es Gol, actualizamos el marcador del partido
        if ($validated['event_type'] === 'goal') {
            if ($validated['team_id'] == $match->home_team_id) {
                $match->increment('home_score');
            } else {
                $match->increment('away_score');
            }
        }

        $event = MatchEvent::create([
            'match_id' => $match->id,
            'team_id' => $validated['team_id'],
            'player_id' => $validated['player_id'] ?? null,
            'sub_in_player_id' => $validated['sub_in_player_id'] ?? null,
            'event_type' => $validated['event_type'],
            'period' => $period,
            'minute' => $minute,
            'second' => $second,
            'notes' => $validated['notes'] ?? null,
        ]);

        $match->refresh();

        event(new MatchEventOccurred($match, $event));

        return response()->json([
            'success' => true,
            'home_score' => $match->home_score,
            'away_score' => $match->away_score,
            'event' => [
                'id' => $event->id,
                'team_id' => $event->team_id,
                'player_name' => $event->player?->name ?? 'N/A',
                'sub_in_player_name' => $event->subInPlayer?->name,
                'event_type' => $event->event_type,
                'icon' => $event->icon,
                'label' => $event->label,
                'formatted_time' => $event->formatted_time,
                'notes' => $event->notes,
            ],
            'formatted_clock' => $match->formatted_clock,
        ]);
    }

    public function liveFeed(MatchGame $match): JsonResponse
    {
        $match->load([
            'homeTeam',
            'awayTeam',
            'events.player',
            'events.subInPlayer',
            'events.team',
        ]);

        return response()->json([
            'match_id' => $match->id,
            'home_team' => $match->homeTeam->name,
            'away_team' => $match->awayTeam->name,
            'home_score' => $match->home_score,
            'away_score' => $match->away_score,
            'status' => $match->status,
            'current_period' => $match->current_period,
            'is_timer_running' => $match->is_timer_running,
            'formatted_clock' => $match->formatted_clock,
            'elapsed_seconds' => $match->getCurrentClockSeconds(),
            'events' => $match->events->map(fn ($e) => [
                'id' => $e->id,
                'team_id' => $e->team_id,
                'team_name' => $e->team->name,
                'player_name' => $e->player?->name ?? 'N/A',
                'sub_in_player_name' => $e->subInPlayer?->name,
                'event_type' => $e->event_type,
                'icon' => $e->icon,
                'label' => $e->label,
                'formatted_time' => $e->formatted_time,
                'notes' => $e->notes,
            ]),
        ]);
    }

    private function authorizeRefereeOrAdmin(MatchGame $match): void
    {
        $user = request()->user();
        abort_unless(
            $user && (
                $user->isSuperAdmin() ||
                ($user->isAdmin() && $match->tournament->admin_id === $user->id)
            ),
            403
        );
    }
}
