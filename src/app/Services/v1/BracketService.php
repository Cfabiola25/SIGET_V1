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

            $createdMatches = $bracketSize >= 8
                ? $this->createQuarterfinalBracket($tournament, $qualifiedTeams, $startDate, $daysBetweenStages)
                : $this->createSemifinalBracket($tournament, $qualifiedTeams, $startDate, $daysBetweenStages);

            $totalMatches = collect($createdMatches)->flatten()->count();

            return [
                'bracket_size' => $bracketSize,
                'total_matches_created' => $totalMatches,
                'stages' => $createdMatches,
            ];
        });
    }

    /**
     * Llaves de 8 equipos: 4 cuartos de final, 2 semifinales y 1 final.
     * Emparejamientos estándar por posición: 1-8, 4-5, 2-7 y 3-6.
     */
    private function createQuarterfinalBracket(
        Tournament $tournament,
        Collection $teams,
        Carbon $startDate,
        int $daysBetweenStages,
    ): array {
        $finalDate = $startDate->copy()->addDays($daysBetweenStages * 2);
        $final = $this->createKnockoutMatch($tournament, 'final', 'FINAL', $finalDate);
        $semisDate = $startDate->copy()->addDays($daysBetweenStages);
        $sf1 = $this->createKnockoutMatch($tournament, 'semifinals', 'SF1', $semisDate, [$final, 'home']);
        $sf2 = $this->createKnockoutMatch($tournament, 'semifinals', 'SF2', $semisDate, [$final, 'away']);

        $quarters = collect([
            ['QF1', $startDate->copy(), [$sf1, 'home'], [$teams[0], $teams[7]]],
            ['QF2', $startDate->copy()->addHours(2), [$sf1, 'away'], [$teams[3], $teams[4]]],
            ['QF3', $startDate->copy()->addDays(1), [$sf2, 'home'], [$teams[1], $teams[6]]],
            ['QF4', $startDate->copy()->addDays(1)->addHours(2), [$sf2, 'away'], [$teams[2], $teams[5]]],
        ])->map(fn (array $qf) => $this->createKnockoutMatch($tournament, 'quarterfinals', ...$qf));

        return [
            'quarterfinals' => $quarters,
            'semifinals' => collect([$sf1, $sf2]),
            'final' => collect([$final]),
        ];
    }

    /**
     * Llaves de 4 equipos: 2 semifinales (1-4 y 2-3) y 1 final.
     */
    private function createSemifinalBracket(
        Tournament $tournament,
        Collection $teams,
        Carbon $startDate,
        int $daysBetweenStages,
    ): array {
        $finalDate = $startDate->copy()->addDays($daysBetweenStages);
        $final = $this->createKnockoutMatch($tournament, 'final', 'FINAL', $finalDate);
        $sf1 = $this->createKnockoutMatch($tournament, 'semifinals', 'SF1', $startDate->copy(), [$final, 'home'], [$teams[0], $teams[3]]);
        $sf2Date = $startDate->copy()->addHours(2);
        $sf2 = $this->createKnockoutMatch($tournament, 'semifinals', 'SF2', $sf2Date, [$final, 'away'], [$teams[1], $teams[2]]);

        return [
            'semifinals' => collect([$sf1, $sf2]),
            'final' => collect([$final]),
        ];
    }

    /**
     * Crea un partido de eliminación directa.
     *
     * @param  array{0: MatchGame, 1: string}|null  $advancesTo  partido siguiente y casilla (home|away) del ganador
     * @param  array{0: Team, 1: Team}|null  $teams  local y visitante; null cuando aún no se conocen
     */
    private function createKnockoutMatch(
        Tournament $tournament,
        string $stage,
        string $position,
        Carbon $date,
        ?array $advancesTo = null,
        ?array $teams = null,
    ): MatchGame {
        return MatchGame::create([
            'tournament_id' => $tournament->id,
            'stage' => $stage,
            'bracket_position' => $position,
            'match_date' => $date,
            'status' => 'scheduled',
            'next_match_id' => $advancesTo[0]->id ?? null,
            'next_match_slot' => $advancesTo[1] ?? null,
            'home_team_id' => $teams[0]->id ?? null,
            'away_team_id' => $teams[1]->id ?? null,
        ]);
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
