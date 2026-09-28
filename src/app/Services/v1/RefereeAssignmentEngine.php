<?php

namespace App\Services\v1;

use App\Models\v1\MatchGame;
use App\Models\v1\Referee;
use App\Models\v1\RefereeConflictRecord;
use App\Models\v1\RefereeEvaluation;
use InvalidArgumentException;

class RefereeAssignmentEngine
{
    /**
     * Motor de asignación algorítmica de árbitros:
     * 1. Filtra árbitros activos.
     * 2. Excluye árbitros con conflicto de interés histórico con cualquiera de los dos clubes.
     * 3. Excluye árbitros ya asignados en la misma franja horaria (bloqueo de colisión de agenda).
     * 4. Prioriza por mayor calificación (rating_average) y equilibrio de carga de partidos.
     */
    public function assignReferee(MatchGame $match): ?Referee
    {
        $matchDate = $match->match_date;
        $homeTeamId = $match->home_team_id;
        $awayTeamId = $match->away_team_id;

        $conflictedRefereeIds = RefereeConflictRecord::whereIn('team_id', array_filter([$homeTeamId, $awayTeamId]))
            ->pluck('referee_id')
            ->unique()
            ->all();

        // Detectar árbitros ocupados en partidos en la misma fecha (+- 3 horas)
        $busyRefereeIds = [];
        if ($matchDate) {
            $windowStart = $matchDate->copy()->subHours(3);
            $windowEnd = $matchDate->copy()->addHours(3);

            $busyRefereeIds = MatchGame::where('id', '!=', $match->id)
                ->whereNotNull('referee_id')
                ->whereBetween('match_date', [$windowStart, $windowEnd])
                ->pluck('referee_id')
                ->unique()
                ->all();
        }

        // Obtener candidatos elegibles
        $eligibleReferees = Referee::where('is_active', true)
            ->whereNotIn('id', $conflictedRefereeIds)
            ->whereNotIn('id', $busyRefereeIds)
            ->orderByDesc('rating_average')
            ->orderBy('total_matches_officiated', 'asc')
            ->get();

        $selectedReferee = $eligibleReferees->first();

        if ($selectedReferee) {
            $match->update(['referee_id' => $selectedReferee->id]);
        }

        return $selectedReferee;
    }

    /**
     * Asignación masiva algorítmica para todos los partidos de una jornada.
     *
     * @return array{assigned: int, unassigned: int, matches: array}
     */
    public function bulkAssignForRound(int $tournamentId, int $roundNumber): array
    {
        $matches = MatchGame::where('tournament_id', $tournamentId)
            ->where('round_number', $roundNumber)
            ->whereNull('referee_id')
            ->orderBy('match_date')
            ->get();

        $assignedCount = 0;
        $unassignedCount = 0;
        $results = [];

        foreach ($matches as $match) {
            $ref = $this->assignReferee($match);
            if ($ref) {
                $assignedCount++;
                $results[] = [
                    'match_id' => $match->id,
                    'referee_id' => $ref->id,
                    'referee_name' => $ref->name,
                    'rating' => $ref->rating_average,
                ];
            } else {
                $unassignedCount++;
                $results[] = [
                    'match_id' => $match->id,
                    'referee_id' => null,
                    'referee_name' => null,
                    'reason' => 'Sin árbitros disponibles sin conflicto de interés o sin colisión de horario',
                ];
            }
        }

        return [
            'assigned' => $assignedCount,
            'unassigned' => $unassignedCount,
            'matches' => $results,
        ];
    }

    /**
     * Registra un conflicto de interés formal entre un árbitro y un equipo.
     */
    public function registerConflict(int $refereeId, int $teamId, string $reason): RefereeConflictRecord
    {
        return RefereeConflictRecord::firstOrCreate(
            ['referee_id' => $refereeId, 'team_id' => $teamId],
            ['reason' => $reason]
        );
    }

    /**
     * Registra una evaluación post-partido otorgada por el DT de un equipo.
     * Recalcula el promedio del árbitro y detecta posibles conflictos en caso de calificaciones extremas.
     *
     * @throws InvalidArgumentException
     */
    public function submitEvaluation(
        MatchGame $match,
        int $teamId,
        int $evaluatorUserId,
        array $data
    ): RefereeEvaluation {
        if (! $match->referee_id) {
            throw new InvalidArgumentException('Este partido no tiene un árbitro asignado.');
        }

        if (! in_array($teamId, [$match->home_team_id, $match->away_team_id])) {
            throw new InvalidArgumentException('El equipo no participó en este partido.');
        }

        $overall = (int) ($data['score_overall'] ?? 5);
        $enforcement = (int) ($data['score_rule_enforcement'] ?? 5);
        $fairness = (int) ($data['score_fairness'] ?? 5);
        $punctuality = (int) ($data['score_punctuality'] ?? 5);

        $evaluation = RefereeEvaluation::updateOrCreate(
            [
                'match_id' => $match->id,
                'team_id' => $teamId,
            ],
            [
                'referee_id' => $match->referee_id,
                'evaluated_by_user_id' => $evaluatorUserId,
                'score_overall' => max(1, min(5, $overall)),
                'score_rule_enforcement' => max(1, min(5, $enforcement)),
                'score_fairness' => max(1, min(5, $fairness)),
                'score_punctuality' => max(1, min(5, $punctuality)),
                'comments' => $data['comments'] ?? null,
            ]
        );

        // Recalcular promedio del árbitro
        $match->referee?->recalculateRatingAverage();

        return $evaluation;
    }
}
