<?php

namespace App\Services\v1;

use App\Models\v1\Player;
use App\Models\v1\Team;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

class ScoutingRatingService
{
    /**
     * Consulta el mercado de agentes libres con filtros avanzados y ordenación.
     */
    public function getFreeAgents(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Player::with(['profile', 'team'])
            ->freeAgents();

        if (! empty($filters['position'])) {
            $query->whereHas('profile', function ($q) use ($filters) {
                $q->where('position', $filters['position']);
            });
        }

        if (! empty($filters['preferred_foot'])) {
            $query->whereHas('profile', function ($q) use ($filters) {
                $q->where('preferred_foot', $filters['preferred_foot']);
            });
        }

        if (! empty($filters['min_rating'])) {
            $minRating = (float) $filters['min_rating'];
            $query->whereHas('profile', function ($q) use ($minRating) {
                $q->where('performance_rating', '>=', $minRating);
            });
        }

        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('identification_document', 'like', $search);
            });
        }

        $sort = $filters['sort_by'] ?? 'rating_desc';
        match ($sort) {
            'rating_asc' => $query->join('player_profiles', 'players.id', '=', 'player_profiles.player_id')
                ->orderBy('player_profiles.performance_rating', 'asc')
                ->select('players.*'),
            'mvp_desc' => $query->join('player_profiles', 'players.id', '=', 'player_profiles.player_id')
                ->orderBy('player_profiles.mvp_awards_count', 'desc')
                ->select('players.*'),
            'name_asc' => $query->orderBy('name', 'asc'),
            default => $query->join('player_profiles', 'players.id', '=', 'player_profiles.player_id')
                ->orderBy('player_profiles.performance_rating', 'desc')
                ->select('players.*'),
        };

        return $query->paginate($perPage);
    }

    /**
     * Calcula métricas detalladas del radar de talentos para un jugador.
     *
     * @return array{overall: float, scoring: float, discipline: float, leadership: float, consistency: float}
     */
    public function getRadarMetrics(Player $player): array
    {
        $goals = $player->goalsCount();
        $yellows = $player->yellowCardsCount();
        $reds = $player->redCardsCount();
        $matches = $player->lineups()->count();
        $mvps = $player->profile?->mvp_awards_count ?? 0;

        // Submétrica de Anotación (1.0 - 10.0)
        $scoringRatio = $matches > 0 ? ($goals / $matches) : ($goals > 0 ? 1.0 : 0.0);
        $scoring = min(10.0, max(1.0, round(5.0 + ($scoringRatio * 5.0), 1)));

        // Submétrica de Disciplina (1.0 - 10.0)
        $penalty = ($reds * 2.5) + ($yellows * 0.8);
        $discipline = min(10.0, max(1.0, round(10.0 - $penalty, 1)));

        // Submétrica de Liderazgo / MVP (1.0 - 10.0)
        $leadership = min(10.0, max(1.0, round(5.0 + ($mvps * 1.5), 1)));

        // Submétrica de Regularidad / Presencia (1.0 - 10.0)
        $consistency = min(10.0, max(1.0, round(5.0 + min(5.0, $matches * 0.5), 1)));

        $overall = $player->profile?->recalculatePerformanceRating() ?? 7.0;

        return [
            'overall' => $overall,
            'scoring' => $scoring,
            'discipline' => $discipline,
            'leadership' => $leadership,
            'consistency' => $consistency,
            'matches_played' => $matches,
            'goals_scored' => $goals,
            'mvp_awards' => $mvps,
        ];
    }

    /**
     * Recluta a un agente libre para un equipo dado.
     *
     * @throws InvalidArgumentException
     */
    public function recruitPlayer(Player $player, Team $team, ?int $jerseyNumber = null): Player
    {
        // 1. Validar límite de plantilla del equipo (25 jugadores por defecto)
        $currentRosterCount = $team->players()->count();
        if ($currentRosterCount >= 25) {
            throw new InvalidArgumentException("El equipo {$team->name} ha alcanzado el límite máximo reglamentario de 25 jugadores.");
        }

        // 2. Determinar número de camiseta si no se suministra
        if (! $jerseyNumber) {
            $usedNumbers = $team->players()->pluck('jersey_number')->all();
            for ($i = 1; $i <= 99; $i++) {
                if (! in_array($i, $usedNumbers)) {
                    $jerseyNumber = $i;
                    break;
                }
            }
        } else {
            // Verificar si el dorsal ya está ocupado en el equipo
            $numberTaken = $team->players()->where('jersey_number', $jerseyNumber)->where('id', '!=', $player->id)->exists();
            if ($numberTaken) {
                throw new InvalidArgumentException("El dorsal #{$jerseyNumber} ya está en uso por otro jugador en {$team->name}.");
            }
        }

        // 3. Fichar al jugador
        $player->update([
            'team_id' => $team->id,
            'jersey_number' => $jerseyNumber,
        ]);

        if ($player->profile) {
            $player->profile->update([
                'is_free_agent' => false,
            ]);
            $player->profile->recalculatePerformanceRating();
        }

        return $player->fresh(['team', 'profile']);
    }

    /**
     * Libera a un jugador a la Agencia Libre.
     */
    public function releasePlayer(Player $player, ?string $scoutingNotes = null): Player
    {
        $player->update([
            'team_id' => null,
        ]);

        if ($player->profile) {
            $player->profile->update([
                'is_free_agent' => true,
                'scouting_notes' => $scoutingNotes ?? $player->profile->scouting_notes,
            ]);
            $player->profile->recalculatePerformanceRating();
        }

        return $player->fresh(['profile']);
    }
}
