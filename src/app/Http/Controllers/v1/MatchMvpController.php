<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\MatchGame;
use App\Services\v1\MatchMvpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MatchMvpController extends Controller
{
    public function __construct(
        protected MatchMvpService $mvpService
    ) {}

    /**
     * Votación pública de los aficionados en tiempo real (PWA / Live Match Feed).
     */
    public function vote(Request $request, MatchGame $match): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'player_id' => ['required', 'integer', 'exists:players,id'],
        ]);

        $fingerprint = $request->header('X-Voter-Fingerprint')
            ?? ($request->ip().'|'.$request->header('User-Agent'));

        $userId = auth()->id();

        try {
            $vote = $this->mvpService->vote(
                $match,
                (int) $validated['player_id'],
                $fingerprint,
                $userId
            );

            $stats = $this->mvpService->getLiveVoteStats($match);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '¡Tu voto para el MVP del partido ha sido registrado con éxito!',
                    'vote_id' => $vote->id,
                    'stats' => $stats,
                ]);
            }

            return back()->with('status', '¡Tu voto para el MVP del partido ha sido registrado exitosamente!');
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['mvp' => $e->getMessage()]);
        }
    }

    /**
     * Retorna las estadísticas de votación del MVP en vivo (JSON).
     */
    public function liveStats(MatchGame $match): JsonResponse
    {
        $match->loadMissing(['homeTeam.players.profile', 'awayTeam.players.profile', 'mvpPlayer.profile']);
        $stats = $this->mvpService->getLiveVoteStats($match);

        $homePlayers = $match->homeTeam?->players ?? collect();
        $awayPlayers = $match->awayTeam?->players ?? collect();

        $lineupPlayers = $homePlayers->merge($awayPlayers)->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'jersey_number' => $p->jersey_number,
            'team_name' => $p->team_id === $match->home_team_id ? $match->homeTeam?->name : $match->awayTeam?->name,
            'position' => $p->profile?->position_label ?? 'Jugador',
        ])->values();

        return response()->json([
            'match_id' => $match->id,
            'is_locked' => $match->isLocked(),
            'official_mvp' => $match->mvpPlayer ? [
                'id' => $match->mvpPlayer->id,
                'name' => $match->mvpPlayer->name,
                'team' => $match->mvpPlayer->team?->name,
                'rating' => $match->mvpPlayer->profile?->performance_rating,
            ] : null,
            'total_votes' => $stats->sum('votes_count'),
            'breakdown' => $stats,
            'lineup_players' => $lineupPlayers,
            'stats' => $stats,
        ]);
    }

    /**
     * Coronación manual de MVP por el árbitro/organizador (si se desea adelantar o forzar).
     */
    public function crown(Request $request, MatchGame $match): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        abort_unless($user && ($user->isSuperAdmin() || $user->isAdmin() || $user->isReferee()), 403);

        $mvp = $this->mvpService->crownOfficialMvp($match);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Se ha coronado a {$mvp?->name} como el MVP Oficial del encuentro.",
                'mvp' => $mvp,
            ]);
        }

        return back()->with('status', "Se ha coronado a {$mvp?->name} como el MVP Oficial del encuentro.");
    }
}
