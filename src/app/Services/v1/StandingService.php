<?php

namespace App\Services\v1;

use App\Models\v1\MatchGame;
use App\Models\v1\Standings;
use App\Models\v1\Tournament;
use App\Models\v1\TournamentRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Recalcula la tabla de posiciones de un torneo a partir de los partidos jugados,
 * aplicando la puntuación definida en el reglamento del torneo (tournament_rules).
 */
class StandingService
{
    /** Puntuación por defecto cuando el torneo no tiene reglamento registrado. */
    private const DEFAULT_POINTS = ['win' => 3, 'draw' => 1, 'loss' => 0];

    public function recalculate(Tournament $tournament): Collection
    {
        return DB::transaction(function () use ($tournament): Collection {
            $points = $this->pointsFor($tournament);
            $table = $tournament->teams()->pluck('id')
                ->mapWithKeys(fn (int $teamId): array => [$teamId => $this->emptyRow()]);

            $tournament->matches()->where('status', 'played')->get()
                ->each(fn (MatchGame $match) => $this->applyMatch($table, $match, $points));

            $this->persist($tournament, $table);

            return $tournament->standings()->with('team')->orderByDesc('points')->get();
        });
    }

    /**
     * @return array{win: int, draw: int, loss: int}
     */
    private function pointsFor(Tournament $tournament): array
    {
        $rules = $tournament->rules;

        if (! $rules instanceof TournamentRule) {
            return self::DEFAULT_POINTS;
        }

        return [
            'win' => $rules->points_for_win ?? self::DEFAULT_POINTS['win'],
            'draw' => $rules->points_for_draw ?? self::DEFAULT_POINTS['draw'],
            'loss' => $rules->points_for_loss ?? self::DEFAULT_POINTS['loss'],
        ];
    }

    private function emptyRow(): array
    {
        return [
            'matches_played' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'points' => 0,
        ];
    }

    /**
     * Suma el resultado del partido a ambos equipos. Los partidos con equipos ajenos
     * al torneo se ignoran.
     */
    private function applyMatch(Collection $table, MatchGame $match, array $points): void
    {
        if (! $table->has($match->home_team_id) || ! $table->has($match->away_team_id)) {
            return;
        }

        $table->put($match->home_team_id, $this->addResult(
            $table->get($match->home_team_id), (int) $match->home_score, (int) $match->away_score, $points,
        ));
        $table->put($match->away_team_id, $this->addResult(
            $table->get($match->away_team_id), (int) $match->away_score, (int) $match->home_score, $points,
        ));
    }

    private function addResult(array $row, int $scored, int $conceded, array $points): array
    {
        $outcome = match (true) {
            $scored > $conceded => ['key' => 'wins', 'points' => $points['win']],
            $scored < $conceded => ['key' => 'losses', 'points' => $points['loss']],
            default => ['key' => 'draws', 'points' => $points['draw']],
        };

        $row['matches_played']++;
        $row['goals_for'] += $scored;
        $row['goals_against'] += $conceded;
        $row[$outcome['key']]++;
        $row['points'] += $outcome['points'];

        return $row;
    }

    private function persist(Tournament $tournament, Collection $table): void
    {
        $table->each(fn (array $values, int $teamId) => Standings::updateOrCreate(
            ['tournament_id' => $tournament->id, 'team_id' => $teamId],
            $values,
        ));
    }
}
