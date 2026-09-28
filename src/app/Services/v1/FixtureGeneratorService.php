<?php

namespace App\Services\v1;

use App\Models\v1\MatchGame;
use App\Models\v1\Tournament;
use App\Models\v1\Venue;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FixtureGeneratorService
{
    /**
     * Genera la previsualización del fixture usando el Algoritmo Berger / Round-Robin
     * resolviendo restricciones cruzadas de sedes, campos, franjas y descanso.
     *
     * @param  array{
     *     start_date?: string|Carbon,
     *     days_between_rounds?: int,
     *     rounds_count?: int,
     *     time_slots?: array<string>,
     *     venue_ids?: array<int>
     * }  $options
     * @return array{
     *     total_rounds: int,
     *     total_matches: int,
     *     rounds: array<int, array<string, mixed>>
     * }
     */
    public function preview(Tournament $tournament, array $options = []): array
    {
        $teams = $tournament->teams()->get();
        if ($teams->count() < 2) {
            return [
                'total_rounds' => 0,
                'total_matches' => 0,
                'rounds' => [],
            ];
        }

        $venues = ! empty($options['venue_ids'])
            ? Venue::whereIn('id', $options['venue_ids'])->get()
            : Venue::where('is_active', true)->get();

        if ($venues->isEmpty()) {
            $venues = Venue::all();
        }

        $startDate = isset($options['start_date'])
            ? Carbon::parse($options['start_date'])
            : now()->next(Carbon::SATURDAY)->setTime(10, 0);

        $daysBetweenRounds = (int) ($options['days_between_rounds'] ?? 7);
        $vueltas = (int) ($options['rounds_count'] ?? 1); // 1 vuelta o 2 vueltas (ida y vuelta)
        $timeSlots = ! empty($options['time_slots'])
            ? $options['time_slots']
            : ['09:00', '11:00', '14:00', '16:00'];

        $teamList = $teams->values()->all();
        $isOdd = count($teamList) % 2 !== 0;

        if ($isOdd) {
            // Se agrega un equipo fantasma (BYE) para equipos impares
            $teamList[] = null;
        }

        $numTeams = count($teamList);
        $roundsInVuelta = $numTeams - 1;
        $matchesPerRound = (int) floor($numTeams / 2);

        $schedule = [];
        $totalMatches = 0;
        $currentDate = $startDate->copy();

        for ($v = 0; $v < $vueltas; $v++) {
            $list = $teamList;

            for ($round = 0; $round < $roundsInVuelta; $round++) {
                $roundNumber = ($v * $roundsInVuelta) + $round + 1;
                $roundMatches = [];
                $slotIndex = 0;
                $venueIndex = 0;

                for ($i = 0; $i < $matchesPerRound; $i++) {
                    $teamA = $list[$i];
                    $teamB = $list[$numTeams - 1 - $i];

                    // Si uno es BYE, el equipo descansa esta fecha
                    if ($teamA === null || $teamB === null) {
                        continue;
                    }

                    // Alternancia de localía por ronda
                    if (($round + $i) % 2 === 0) {
                        $home = $teamA;
                        $away = $teamB;
                    } else {
                        $home = $teamB;
                        $away = $teamA;
                    }

                    // En la 2ª vuelta (revancha), se invierte la localía
                    if ($v % 2 !== 0) {
                        $temp = $home;
                        $home = $away;
                        $away = $temp;
                    }

                    // Asignación de franja horaria
                    $slotTime = $timeSlots[$slotIndex % count($timeSlots)];
                    [$hour, $min] = explode(':', $slotTime);
                    $matchDate = $currentDate->copy()->setTime((int) $hour, (int) $min);

                    // Asignación de sede y campo
                    $venue = $venues->isNotEmpty() ? $venues[$venueIndex % $venues->count()] : null;
                    $fieldNumber = ($venueIndex % 2) + 1;

                    $roundMatches[] = [
                        'round_number' => $roundNumber,
                        'stage' => 'regular',
                        'home_team' => $home,
                        'away_team' => $away,
                        'match_date' => $matchDate,
                        'venue' => $venue,
                        'field_number' => $fieldNumber,
                    ];

                    $totalMatches++;
                    $slotIndex++;
                    $venueIndex++;
                }

                $schedule[$roundNumber] = [
                    'round_number' => $roundNumber,
                    'date' => $currentDate->copy(),
                    'matches' => $roundMatches,
                ];

                // Rotar los equipos manteniendo el índice 0 fijo (Algoritmo Berger)
                $last = array_pop($list);
                array_splice($list, 1, 0, [$last]);

                // Avanzar fecha de jornada respetando días de descanso
                $currentDate->addDays($daysBetweenRounds);
            }
        }

        return [
            'total_rounds' => count($schedule),
            'total_matches' => $totalMatches,
            'rounds' => $schedule,
        ];
    }

    /**
     * Genera y persiste en base de datos los partidos del torneo.
     *
     * @return Collection<int, MatchGame>
     */
    public function generateAndPersist(Tournament $tournament, array $options = []): Collection
    {
        return DB::transaction(function () use ($tournament, $options): Collection {
            $preview = $this->preview($tournament, $options);
            $createdMatches = collect();

            foreach ($preview['rounds'] as $roundData) {
                foreach ($roundData['matches'] as $matchData) {
                    $match = MatchGame::create([
                        'tournament_id' => $tournament->id,
                        'round_number' => $matchData['round_number'],
                        'stage' => 'regular',
                        'home_team_id' => $matchData['home_team']->id,
                        'away_team_id' => $matchData['away_team']->id,
                        'match_date' => $matchData['match_date'],
                        'venue_id' => $matchData['venue']?->id,
                        'field_number' => $matchData['field_number'] ?? 1,
                        'status' => 'scheduled',
                        'home_score' => 0,
                        'away_score' => 0,
                    ]);

                    $createdMatches->push($match);
                }
            }

            return $createdMatches;
        });
    }
}
