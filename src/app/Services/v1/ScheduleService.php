<?php

namespace App\Services\v1;

use App\Models\v1\MatchGame;
use App\Models\v1\Tournament;
use App\Models\v1\Venue;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    /**
     * Reprogramación en cascada por contingencia climática o fuerza mayor.
     * Desplaza los partidos de una fecha o sede afectada y corre en bloque las jornadas posteriores
     * para mantener la paridad competitiva sin generar solapamientos de canchas ni equipos.
     *
     * @param  array{
     *     tournament_id: int,
     *     affected_date?: string,
     *     venue_id?: int,
     *     round_number?: int,
     *     shift_days: int,
     *     cascade_subsequent_rounds?: bool,
     *     reason?: string
     * }  $params
     * @return array{
     *     rescheduled_count: int,
     *     shifted_rounds: array<int>,
     *     conflicts_detected: array<string>,
     *     message: string
     * }
     */
    public function rescheduleCascade(array $params): array
    {
        return DB::transaction(function () use ($params): array {
            $tournament = Tournament::findOrFail($params['tournament_id']);
            $shiftDays = (int) ($params['shift_days'] ?? 7);
            $cascadeSubsequent = $params['cascade_subsequent_rounds'] ?? true;
            $reason = $params['reason'] ?? 'Contingencia climática / Fuerza mayor';

            $query = MatchGame::where('tournament_id', $tournament->id)
                ->where('status', 'scheduled')
                ->where('is_locked', false);

            if (! empty($params['venue_id'])) {
                $query->where('venue_id', $params['venue_id']);
            }

            if (! empty($params['round_number'])) {
                $affectedRound = (int) $params['round_number'];
                $query->where('round_number', $affectedRound);
            } elseif (! empty($params['affected_date'])) {
                $affectedDate = Carbon::parse($params['affected_date'])->toDateString();
                $query->whereDate('match_date', $affectedDate);
                $firstAffected = (clone $query)->orderBy('round_number')->first();
                $affectedRound = $firstAffected?->round_number ?? 1;
            } else {
                $affectedRound = 1;
            }

            $affectedMatches = $query->get();
            $conflicts = [];
            $rescheduledMatches = 0;
            $affectedRoundsList = [];

            // 1. Reprogramar los partidos directamente afectados
            foreach ($affectedMatches as $match) {
                $newDate = Carbon::parse($match->match_date)->addDays($shiftDays);

                // Validar colisiones de sede y franja horaria
                $collision = MatchGame::where('venue_id', $match->venue_id)
                    ->where('field_number', $match->field_number)
                    ->where('id', '!=', $match->id)
                    ->whereBetween('match_date', [
                        $newDate->copy()->subMinutes(90),
                        $newDate->copy()->addMinutes(90),
                    ])
                    ->exists();

                if ($collision) {
                    $conflicts[] = "Conflicto en Sede {$match->venue?->name} (Campo {$match->field_number}) para el partido {$match->homeTeam?->name} vs {$match->awayTeam?->name} el {$newDate->format('d/m/Y H:i')}.";
                }

                $match->update([
                    'match_date' => $newDate,
                    'match_sheet_notes' => trim(($match->match_sheet_notes ?? '')." [Reprogramado por contingencia: {$reason}]"),
                ]);

                $rescheduledMatches++;
                $affectedRoundsList[] = $match->round_number;
            }

            // 2. Si se activa la cascada, desplazar en bloque las jornadas posteriores para mantener orden deportivo
            if ($cascadeSubsequent && $affectedRound > 0) {
                $subsequentMatches = MatchGame::where('tournament_id', $tournament->id)
                    ->where('status', 'scheduled')
                    ->where('is_locked', false)
                    ->where('round_number', '>', $affectedRound)
                    ->whereNotIn('id', $affectedMatches->pluck('id'))
                    ->get();

                foreach ($subsequentMatches as $subMatch) {
                    $newDate = Carbon::parse($subMatch->match_date)->addDays($shiftDays);
                    $subMatch->update([
                        'match_date' => $newDate,
                        'match_sheet_notes' => trim(($subMatch->match_sheet_notes ?? '')." [Desplazado en cascada por corrimiento de Fecha {$affectedRound}]"),
                    ]);
                    $rescheduledMatches++;
                    $affectedRoundsList[] = $subMatch->round_number;
                }
            }

            return [
                'rescheduled_count' => $rescheduledMatches,
                'shifted_rounds' => array_values(array_unique($affectedRoundsList)),
                'conflicts_detected' => $conflicts,
                'message' => "Se reprogramaron exitosamente {$rescheduledMatches} encuentros por contingencia logistica.",
            ];
        });
    }

    /**
     * Validador de Restricciones Avanzadas de Disponibilidad:
     * - Franjas horarias específicas por sede (ej. Cancha con o sin luz artificial)
     * - Incompatibilidad horaria de directores técnicos que dirigen múltiples categorías
     * - Restricción geográfica de desplazamientos
     *
     * @return array{
     *     valid: bool,
     *     violations: array<string>
     * }
     */
    public function validateMatchConstraints(MatchGame $match, Carbon $proposedDate, Venue $venue): array
    {
        $violations = [];

        // 1. Restricción de Sede y Luz Artificial
        // Canchas sin reflectores o césped natural en horario nocturno (posterior a 18:00)
        $hour = $proposedDate->hour;
        $isNightSlot = $hour >= 18 || $hour < 7;
        $notes = strtolower($venue->notes ?? '');
        $hasArtificialLight = str_contains($notes, 'luz') || str_contains($notes, 'iluminacion') || str_contains($notes, 'artificial');

        if ($isNightSlot && ! $hasArtificialLight) {
            $violations[] = "La sede '{$venue->name}' no dispone de iluminación artificial certificada para albergar encuentros a las {$proposedDate->format('H:i')}.";
        }

        // 2. Incompatibilidad de Entrenadores (DT dirigiéndose en dos categorías distintas el mismo club)
        $homeCoachId = $match->homeTeam?->coach_user_id;
        $awayCoachId = $match->awayTeam?->coach_user_id;

        if ($homeCoachId) {
            $coachOverlap = MatchGame::where('id', '!=', $match->id)
                ->where(function ($q) use ($homeCoachId) {
                    $q->whereHas('homeTeam', fn ($t) => $t->where('coach_user_id', $homeCoachId))
                      ->orWhereHas('awayTeam', fn ($t) => $t->where('coach_user_id', $homeCoachId));
                })
                ->whereBetween('match_date', [
                    $proposedDate->copy()->subMinutes(120),
                    $proposedDate->copy()->addMinutes(120),
                ])
                ->with(['homeTeam', 'awayTeam'])
                ->first();

            if ($coachOverlap) {
                $violations[] = "Colisión de Director Técnico: El DT del equipo local ({$match->homeTeam?->name}) ya tiene programado el encuentro '{$coachOverlap->homeTeam?->name} vs {$coachOverlap->awayTeam?->name}' a las {$coachOverlap->match_date->format('H:i')}.";
            }
        }

        if ($awayCoachId) {
            $awayCoachOverlap = MatchGame::where('id', '!=', $match->id)
                ->where(function ($q) use ($awayCoachId) {
                    $q->whereHas('homeTeam', fn ($t) => $t->where('coach_user_id', $awayCoachId))
                      ->orWhereHas('awayTeam', fn ($t) => $t->where('coach_user_id', $awayCoachId));
                })
                ->whereBetween('match_date', [
                    $proposedDate->copy()->subMinutes(120),
                    $proposedDate->copy()->addMinutes(120),
                ])
                ->with(['homeTeam', 'awayTeam'])
                ->first();

            if ($awayCoachOverlap) {
                $violations[] = "Colisión de Director Técnico: El DT del equipo visitante ({$match->awayTeam?->name}) ya tiene programado el encuentro '{$awayCoachOverlap->homeTeam?->name} vs {$awayCoachOverlap->awayTeam?->name}' a las {$awayCoachOverlap->match_date->format('H:i')}.";
            }
        }

        // 3. Restricción Geográfica / Desplazamientos
        if ($venue->city && $match->homeTeam?->city && $match->awayTeam?->city) {
            if ($venue->city !== $match->homeTeam->city && $venue->city !== $match->awayTeam->city) {
                $violations[] = "Alerta geográfica: El partido se programaría en '{$venue->city}', obligando al desplazamiento foráneo de ambos clubes ({$match->homeTeam->city} y {$match->awayTeam->city}) en fase clasificatoria previa.";
            }
        }

        return [
            'valid' => empty($violations),
            'violations' => $violations,
        ];
    }
}

