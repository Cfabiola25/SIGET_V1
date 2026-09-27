<?php

namespace App\Services;

use App\Models\v1\Standings;
use App\Models\v1\Tournament;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StandingService
{
    public function recalculate(Tournament $tournament): Collection
    {
        return DB::transaction(function () use ($tournament): Collection {
            $stats = $tournament->teams()
                ->get()
                ->mapWithKeys(fn ($team) => [$team->id => [
                    'matches_played' => 0,
                    'wins' => 0,
                    'draws' => 0,
                    'losses' => 0,
                    'goals_for' => 0,
                    'goals_against' => 0,
                    'points' => 0,
                ]]);

            $tournament->matches()
                ->where('status', 'played')
                ->get()
                ->each(function ($match) use ($stats): void {
                    $home = $stats->get($match->home_team_id);
                    $away = $stats->get($match->away_team_id);

                    if ($home === null || $away === null) {
                        return;
                    }

                    $home['matches_played']++;
                    $away['matches_played']++;
                    $home['goals_for'] += $match->home_score;
                    $home['goals_against'] += $match->away_score;
                    $away['goals_for'] += $match->away_score;
                    $away['goals_against'] += $match->home_score;

                    if ($match->home_score > $match->away_score) {
                        $home['wins']++;
                        $home['points'] += 3;
                        $away['losses']++;
                    } elseif ($match->home_score < $match->away_score) {
                        $away['wins']++;
                        $away['points'] += 3;
                        $home['losses']++;
                    } else {
                        $home['draws']++;
                        $away['draws']++;
                        $home['points']++;
                        $away['points']++;
                    }

                    $stats->put($match->home_team_id, $home);
                    $stats->put($match->away_team_id, $away);
                });

            $stats->each(function (array $values, int $teamId) use ($tournament): void {
                Standings::updateOrCreate(
                    ['tournament_id' => $tournament->id, 'team_id' => $teamId],
                    $values,
                );
            });

            return $tournament->standings()->with('team')->orderByDesc('points')->get();
        });
    }
}
