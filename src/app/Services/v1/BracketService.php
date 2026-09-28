<?php

namespace App\Services\v1;

use App\Models\v1\MatchGame;
use App\Models\v1\Tournament;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BracketService
{
    /**
     * Transiciona la fase regular a llaves de eliminación directa (Playoffs / Brackets)
     * basándose en la tabla de posiciones oficial.
     *
     * @param  array{
     *     bracket_size?: int,
     *     start_date?: string|Carbon,
     *     days_between_stages?: int
     * }  $options
     * @return array{
     *     bracket_size: int,
     *     total_matches_created: int,
     *     stages: array<string, Collection<int, MatchGame>>
     * }
     */
    public function transitionFromStandings(Tournament $tournament, array $options = []): array
    {
        return DB::transaction(function () use ($tournament, $options): array {
            $bracketSize = (int) ($options['bracket_size'] ?? 4);
            $startDate = isset($options['start_date'])
                ? Carbon::parse($options['start_date'])
                : now()->next(Carbon::SATURDAY)->setTime(16, 0);

            $daysBetweenStages = (int) ($options['days_between_stages'] ?? 7);

            // Obtener los mejores clasificados de la tabla de posiciones
            $qualifiedTeams = $tournament->standings()
                ->with('team')
                ->orderByDesc('points')
                ->orderByRaw('(goals_for - goals_against) DESC')
                ->orderByDesc('goals_for')
                ->take($bracketSize)
                ->get()
                ->map(fn ($standing) => $standing->team)
                ->values();

            // Si no hay standings calculados aún, tomar los equipos registrados
            if ($qualifiedTeams->count() < $bracketSize) {
                $qualifiedTeams = $tournament->teams()->take($bracketSize)->get()->values();
            }

            if ($qualifiedTeams->count() < 4) {
                throw new \InvalidArgumentException('Se requieren al menos 4 equipos clasificados para generar llaves de eliminación directa.');
            }

            $createdMatches = [];

            if ($bracketSize >= 8) {
                // Estructura de Cuartos de Final (8 equipos -> 4 QF, 2 SF, 1 Final)
                $finalDate = $startDate->copy()->addDays($daysBetweenStages * 2);
                $semisDate = $startDate->copy()->addDays($daysBetweenStages);
                $quartersDate = $startDate->copy();

                // 1. Gran Final
                $final = MatchGame::create([
                    'tournament_id' => $tournament->id,
                    'stage' => 'final',
                    'bracket_position' => 'FINAL',
                    'match_date' => $finalDate,
                    'status' => 'scheduled',
                    'home_team_id' => null,
                    'away_team_id' => null,
                ]);

                // 2. Semifinales
                $sf1 = MatchGame::create([
                    'tournament_id' => $tournament->id,
                    'stage' => 'semifinals',
                    'bracket_position' => 'SF1',
                    'match_date' => $semisDate,
                    'status' => 'scheduled',
                    'next_match_id' => $final->id,
                    'next_match_slot' => 'home',
                    'home_team_id' => null,
                    'away_team_id' => null,
                ]);

                $sf2 = MatchGame::create([
                    'tournament_id' => $tournament->id,
                    'stage' => 'semifinals',
                    'bracket_position' => 'SF2',
                    'match_date' => $semisDate,
                    'status' => 'scheduled',
                    'next_match_id' => $final->id,
                    'next_match_slot' => 'away',
                    'home_team_id' => null,
                    'away_team_id' => null,
                ]);

                // 3. Cuartos de Final (Emparejamientos estándar: 1 vs 8, 4 vs 5, 2 vs 7, 3 vs 6)
                $qf1 = MatchGame::create([
                    'tournament_id' => $tournament->id,
                    'stage' => 'quarterfinals',
                    'bracket_position' => 'QF1',
                    'match_date' => $quartersDate,
                    'status' => 'scheduled',
                    'next_match_id' => $sf1->id,
                    'next_match_slot' => 'home',
                    'home_team_id' => $qualifiedTeams[0]->id, // 1º
                    'away_team_id' => $qualifiedTeams[7]->id, // 8º
                ]);

                $qf2 = MatchGame::create([
                    'tournament_id' => $tournament->id,
                    'stage' => 'quarterfinals',
                    'bracket_position' => 'QF2',
                    'match_date' => $quartersDate->copy()->addHours(2),
                    'status' => 'scheduled',
                    'next_match_id' => $sf1->id,
                    'next_match_slot' => 'away',
                    'home_team_id' => $qualifiedTeams[3]->id, // 4º
                    'away_team_id' => $qualifiedTeams[4]->id, // 5º
                ]);

                $qf3 = MatchGame::create([
                    'tournament_id' => $tournament->id,
                    'stage' => 'quarterfinals',
                    'bracket_position' => 'QF3',
                    'match_date' => $quartersDate->copy()->addDays(1),
                    'status' => 'scheduled',
                    'next_match_id' => $sf2->id,
                    'next_match_slot' => 'home',
                    'home_team_id' => $qualifiedTeams[1]->id, // 2º
                    'away_team_id' => $qualifiedTeams[6]->id, // 7º
                ]);

                $qf4 = MatchGame::create([
                    'tournament_id' => $tournament->id,
                    'stage' => 'quarterfinals',
                    'bracket_position' => 'QF4',
                    'match_date' => $quartersDate->copy()->addDays(1)->addHours(2),
                    'status' => 'scheduled',
                    'next_match_id' => $sf2->id,
                    'next_match_slot' => 'away',
                    'home_team_id' => $qualifiedTeams[2]->id, // 3º
                    'away_team_id' => $qualifiedTeams[5]->id, // 6º
                ]);

                $createdMatches['quarterfinals'] = collect([$qf1, $qf2, $qf3, $qf4]);
                $createdMatches['semifinals'] = collect([$sf1, $sf2]);
                $createdMatches['final'] = collect([$final]);
            } else {
                // Estructura Semifinales (4 equipos -> 2 SF, 1 Final)
                $finalDate = $startDate->copy()->addDays($daysBetweenStages);
                $semisDate = $startDate->copy();

                $final = MatchGame::create([
                    'tournament_id' => $tournament->id,
                    'stage' => 'final',
                    'bracket_position' => 'FINAL',
                    'match_date' => $finalDate,
                    'status' => 'scheduled',
                    'home_team_id' => null,
                    'away_team_id' => null,
                ]);

                $sf1 = MatchGame::create([
                    'tournament_id' => $tournament->id,
                    'stage' => 'semifinals',
                    'bracket_position' => 'SF1',
                    'match_date' => $semisDate,
                    'status' => 'scheduled',
                    'next_match_id' => $final->id,
                    'next_match_slot' => 'home',
                    'home_team_id' => $qualifiedTeams[0]->id, // 1º
                    'away_team_id' => $qualifiedTeams[3]->id, // 4º
                ]);

                $sf2 = MatchGame::create([
                    'tournament_id' => $tournament->id,
                    'stage' => 'semifinals',
                    'bracket_position' => 'SF2',
                    'match_date' => $semisDate->copy()->addHours(2),
                    'status' => 'scheduled',
                    'next_match_id' => $final->id,
                    'next_match_slot' => 'away',
                    'home_team_id' => $qualifiedTeams[1]->id, // 2º
                    'away_team_id' => $qualifiedTeams[2]->id, // 3º
                ]);

                $createdMatches['semifinals'] = collect([$sf1, $sf2]);
                $createdMatches['final'] = collect([$final]);
            }

            $totalMatches = collect($createdMatches)->flatten()->count();

            return [
                'bracket_size' => $bracketSize,
                'total_matches_created' => $totalMatches,
                'stages' => $createdMatches,
            ];
        });
    }

    /**
     * Obtiene el árbol completo de llaves de eliminación del torneo.
     *
     * @return array{
     *     quarterfinals: Collection<int, MatchGame>,
     *     semifinals: Collection<int, MatchGame>,
     *     final: ?MatchGame
     * }
     */
    public function getBracketTree(Tournament $tournament): array
    {
        $matches = $tournament->matches()
            ->whereIn('stage', ['quarterfinals', 'semifinals', 'final'])
            ->with(['homeTeam', 'awayTeam', 'venue', 'nextMatch'])
            ->orderBy('match_date')
            ->get();

        return [
            'quarterfinals' => $matches->where('stage', 'quarterfinals')->values(),
            'semifinals' => $matches->where('stage', 'semifinals')->values(),
            'final' => $matches->firstWhere('stage', 'final'),
        ];
    }
}
