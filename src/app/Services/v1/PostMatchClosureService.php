<?php

namespace App\Services\v1;

use App\Models\v1\DisciplinarySanction;
use App\Models\v1\MatchEvent;
use App\Models\v1\MatchGame;
use App\Models\v1\TournamentRule;
use App\Services\StandingService;
use Illuminate\Support\Facades\DB;

class PostMatchClosureService
{
    public function __construct(
        protected StandingService $standingService,
        protected ?MatchMvpService $mvpService = null,
        protected ?SportsChronicleService $chronicleService = null
    ) {
        $this->mvpService = $mvpService ?? app(MatchMvpService::class);
        $this->chronicleService = $chronicleService ?? app(SportsChronicleService::class);
    }

    /**
     * Cierra oficialmente el partido:
     * 1. Bloqueo de inmutabilidad (is_locked = true, locked_at = now())
     * 2. Motor disciplinario IFAB/FIFA (Rojas directas, Doble amarilla, Acumulación de amarillas)
     * 3. Recálculo automático de la tabla de posiciones del torneo
     *
     * @return array{success: bool, message: string, sanctions_created: int}
     */
    public function closeMatch(MatchGame $match, ?string $notes = null): array
    {
        return DB::transaction(function () use ($match, $notes): array {
            if ($match->isLocked()) {
                return [
                    'success' => false,
                    'message' => 'El partido ya ha sido cerrado e inmutabilizado previamente.',
                    'sanctions_created' => 0,
                ];
            }

            // 1. Bloqueo de inmutabilidad y estado final
            $match->update([
                'status' => 'played',
                'is_locked' => true,
                'locked_at' => now(),
                'is_timer_running' => false,
                'timer_started_at' => null,
                'match_sheet_notes' => $notes ?? $match->match_sheet_notes,
            ]);

            $tournament = $match->tournament;
            $rules = $tournament?->rules ?? TournamentRule::where('tournament_id', $tournament?->id)->first();

            $yellowLimit = $rules?->yellow_card_limit_for_suspension ?? 2;
            $directRedSuspension = $rules?->direct_red_suspension_matches ?? 1;

            $sanctionsCreated = 0;
            $events = $match->events()->get();

            // 2. Motor Disciplinario IFAB: Rojas Directas
            $redCardEvents = $events->where('event_type', 'red_card')->whereNotNull('player_id');
            foreach ($redCardEvents as $redEvent) {
                DisciplinarySanction::create([
                    'player_id' => $redEvent->player_id,
                    'tournament_id' => $match->tournament_id,
                    'match_id' => $match->id,
                    'sanction_type' => 'direct_red',
                    'matches_suspended' => $directRedSuspension,
                    'matches_served' => 0,
                    'status' => 'active',
                    'notes' => "Expulsión con tarjeta roja directa en la fecha oficial. Suspensión reglamentaria de {$directRedSuspension} fecha(s).",
                ]);
                $sanctionsCreated++;
            }

            // 3. Motor Disciplinario IFAB: Dobles Amarillas y Acumulación
            $yellowCardEvents = $events->where('event_type', 'yellow_card')->whereNotNull('player_id');
            $distinctYellowPlayers = $yellowCardEvents->pluck('player_id')->unique();

            foreach ($distinctYellowPlayers as $playerId) {
                $yellowsInThisMatch = $yellowCardEvents->where('player_id', $playerId)->count();

                if ($yellowsInThisMatch >= 2) {
                    // Doble amarilla en el mismo encuentro (Expulsión por 2ª amonestación - Regla 12 IFAB)
                    DisciplinarySanction::create([
                        'player_id' => $playerId,
                        'tournament_id' => $match->tournament_id,
                        'match_id' => $match->id,
                        'sanction_type' => 'double_yellow',
                        'matches_suspended' => 1,
                        'matches_served' => 0,
                        'status' => 'active',
                        'notes' => 'Expulsión por doble tarjeta amarilla en el mismo partido. Suspensión automática de 1 fecha.',
                    ]);
                    $sanctionsCreated++;
                } else {
                    // Acumulación de tarjetas amarillas en el torneo
                    $totalYellows = MatchEvent::where('player_id', $playerId)
                        ->where('event_type', 'yellow_card')
                        ->whereHas('match', function ($query) use ($match) {
                            $query->where('tournament_id', $match->tournament_id);
                        })
                        ->count();

                    if ($yellowLimit > 0 && ($totalYellows % $yellowLimit === 0)) {
                        DisciplinarySanction::create([
                            'player_id' => $playerId,
                            'tournament_id' => $match->tournament_id,
                            'match_id' => $match->id,
                            'sanction_type' => 'yellow_accumulation',
                            'matches_suspended' => 1,
                            'matches_served' => 0,
                            'status' => 'active',
                            'notes' => "Acumulación de {$totalYellows} tarjetas amarillas en el torneo (límite: {$yellowLimit}). Suspensión automática de 1 fecha.",
                        ]);
                        $sanctionsCreated++;
                    }
                }
            }

            // 4. Recálculo automático de la tabla de posiciones
            if ($tournament) {
                $this->standingService->recalculate($tournament);
            }

            // 5. Avance automático del ganador en brackets de eliminación directa (Playoffs)
            if ($match->next_match_id) {
                $match->advanceWinnerToNextMatch();
            }

            // 6. Coronación de MVP Oficial (cruce de votación de fans + estadísticas del partido)
            if (! $match->mvp_player_id) {
                $this->mvpService->crownOfficialMvp($match);
            }

            // 7. IA Match Reporter: Generación automática de crónica deportiva periodística
            if (! $match->chronicle_body) {
                $this->chronicleService->generateAndSave($match);
            }

            // 8. Actualización de estadísticas del árbitro
            if ($match->referee) {
                $match->referee->recalculateRatingAverage();
            }

            return [
                'success' => true,
                'message' => 'Acta oficial cerrada, firmada y bloqueada exitosamente. Se coronó al MVP, se generó la crónica periodística con IA y se actualizó la tabla de posiciones.',
                'sanctions_created' => $sanctionsCreated,
                'mvp_player_id' => $match->mvp_player_id,
                'chronicle_title' => $match->chronicle_title,
            ];
        });
    }
}
