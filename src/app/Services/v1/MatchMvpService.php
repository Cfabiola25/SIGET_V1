<?php

namespace App\Services\v1;

use App\Models\v1\MatchGame;
use App\Models\v1\MatchMvpVote;
use App\Models\v1\Player;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MatchMvpService
{
    /**
     * Registra un voto de aficionado para el MVP en tiempo real.
     *
     * @throws InvalidArgumentException
     */
    public function vote(MatchGame $match, int $playerId, string $fingerprint, ?int $userId = null): MatchMvpVote
    {
        // 1. Validar que el partido esté en curso o recién finalizado antes de bloqueo
        if ($match->isLocked()) {
            throw new InvalidArgumentException('La votación de MVP está cerrada para este partido.');
        }

        // 2. Validar que el jugador pertenezca a alguno de los dos equipos
        $validPlayer = Player::where('id', $playerId)
            ->whereIn('team_id', [$match->home_team_id, $match->away_team_id])
            ->exists();

        if (! $validPlayer) {
            throw new InvalidArgumentException('El jugador seleccionado no participa en este encuentro.');
        }

        // 3. Registrar o actualizar voto único por fingerprint
        return MatchMvpVote::updateOrCreate(
            [
                'match_id' => $match->id,
                'voter_fingerprint' => $fingerprint,
            ],
            [
                'player_id' => $playerId,
                'user_id' => $userId,
            ]
        );
    }

    /**
     * Obtiene el ranking de votación del público en vivo.
     *
     * @return Collection<int, array{player_id: int, player_name: string, team_id: int, votes_count: int, percentage: float}>
     */
    public function getLiveVoteStats(MatchGame $match): Collection
    {
        $totalVotes = MatchMvpVote::where('match_id', $match->id)->count();

        $votesByPlayer = MatchMvpVote::where('match_id', $match->id)
            ->select('player_id', DB::raw('count(*) as votes_count'))
            ->groupBy('player_id')
            ->orderByDesc('votes_count')
            ->with('player.team')
            ->get();

        return $votesByPlayer->map(function ($row) use ($totalVotes) {
            $count = (int) $row->votes_count;
            $pct = $totalVotes > 0 ? round(($count / $totalVotes) * 100, 1) : 0.0;

            return [
                'player_id' => $row->player_id,
                'player_name' => $row->player?->name ?? 'Jugador',
                'team_id' => $row->player?->team_id,
                'team_name' => $row->player?->team?->name ?? '',
                'votes_count' => $count,
                'percentage' => $pct,
            ];
        });
    }

    /**
     * Corona al MVP oficial del partido cruzando votos de fanáticos (40%) y estadísticas objetivas de juego (60%).
     */
    public function crownOfficialMvp(MatchGame $match): ?Player
    {
        // Obtener jugadores que participaron en el partido (lineup o eventos)
        $playerIds = $match->lineups()->pluck('player_id')->merge(
            $match->events()->whereNotNull('player_id')->pluck('player_id')
        )->unique();

        if ($playerIds->isEmpty()) {
            // Si no hay alineaciones registradas aún, tomar jugadores de ambos equipos
            $playerIds = Player::whereIn('team_id', array_filter([$match->home_team_id, $match->away_team_id]))->pluck('id');
        }

        if ($playerIds->isEmpty()) {
            return null;
        }

        $totalFanVotes = MatchMvpVote::where('match_id', $match->id)->count();
        $fanVotesByPlayer = MatchMvpVote::where('match_id', $match->id)
            ->select('player_id', DB::raw('count(*) as total'))
            ->groupBy('player_id')
            ->pluck('total', 'player_id');

        $events = $match->events()->get();

        $bestScore = -9999.0;
        $mvpPlayerId = null;

        foreach ($playerIds as $pid) {
            // Puntos de afición (escala normalizada de 0 a 40 puntos)
            $playerFanVotes = (int) ($fanVotesByPlayer[$pid] ?? 0);
            $fanScore = $totalFanVotes > 0 ? ($playerFanVotes / $totalFanVotes) * 40.0 : 0.0;

            // Puntos objetivos de rendimiento arbitral/partido (escala de hasta 60 puntos)
            $goals = $events->where('player_id', $pid)->where('event_type', 'goal')->count();
            $yellowCards = $events->where('player_id', $pid)->where('event_type', 'yellow_card')->count();
            $redCards = $events->where('player_id', $pid)->where('event_type', 'red_card')->count();

            $statsScore = ($goals * 25.0) - ($yellowCards * 5.0) - ($redCards * 20.0);

            // Bonus si su equipo ganó
            $player = Player::find($pid);
            if ($player && $match->getWinnerTeam()?->id === $player->team_id) {
                $statsScore += 10.0;
            }

            $totalCalculatedScore = $fanScore + $statsScore;

            if ($totalCalculatedScore > $bestScore) {
                $bestScore = $totalCalculatedScore;
                $mvpPlayerId = $pid;
            }
        }

        if (! $mvpPlayerId) {
            $mvpPlayerId = $playerIds->first();
        }

        $mvpPlayer = Player::find($mvpPlayerId);

        if ($mvpPlayer) {
            $match->update(['mvp_player_id' => $mvpPlayer->id]);

            if ($profile = $mvpPlayer->profile) {
                $profile->increment('mvp_awards_count');
                $profile->increment('mvp_count');
                $profile->recalculatePerformanceRating();
            }
        }

        return $mvpPlayer;
    }
}
