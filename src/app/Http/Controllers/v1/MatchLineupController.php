<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\MatchGame;
use App\Models\v1\MatchLineup;
use App\Models\v1\Player;
use App\Models\v1\Team;
use App\Services\v1\PlayerEligibilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MatchLineupController extends Controller
{
    public function edit(MatchGame $match, Team $team, PlayerEligibilityService $eligibilityService): View
    {
        $this->authorizeCoachOrAdmin($team);
        abort_unless($match->home_team_id === $team->id || $match->away_team_id === $team->id, 404);

        $match->load(['tournament.rules', 'homeTeam', 'awayTeam']);
        $players = $team->players()->with(['profile', 'medicalRecord', 'sanctions'])->orderBy('jersey_number')->get();

        // Evaluar Filtro Tripartito para cada jugador
        $eligibilityData = [];
        foreach ($players as $player) {
            $eligibilityData[$player->id] = $eligibilityService->check($player, $match);
        }

        // Obtener alineaciones previamente guardadas
        $currentStarters = $match->lineups()->where('team_id', $team->id)->where('is_starter', true)->pluck('player_id')->toArray();
        $currentSubs = $match->lineups()->where('team_id', $team->id)->where('is_starter', false)->pluck('player_id')->toArray();

        $isLocked = $match->isLineupSubmissionLocked();
        $lockMinutes = $match->tournament?->rules?->lineup_lock_minutes_before_match ?? 10;

        return view('v1.matches.lineup', compact(
            'match',
            'team',
            'players',
            'eligibilityData',
            'currentStarters',
            'currentSubs',
            'isLocked',
            'lockMinutes'
        ));
    }

    public function store(Request $request, MatchGame $match, Team $team, PlayerEligibilityService $eligibilityService): RedirectResponse
    {
        $this->authorizeCoachOrAdmin($team);
        abort_unless($match->home_team_id === $team->id || $match->away_team_id === $team->id, 404);

        if ($match->isLineupSubmissionLocked()) {
            return back()->withErrors([
                'lineup' => 'El tiempo reglamentario para registrar o modificar la alineación ha finalizado (límite superado antes del inicio del encuentro).',
            ]);
        }

        $validated = $request->validate([
            'starters' => ['required', 'array', 'min:1', 'max:11'],
            'starters.*' => ['required', 'exists:players,id'],
            'substitutes' => ['nullable', 'array', 'max:15'],
            'substitutes.*' => ['required', 'exists:players,id'],
        ]);

        $starters = $validated['starters'];
        $substitutes = $validated['substitutes'] ?? [];

        // Validar que no haya jugadores repetidos entre titulares y suplentes
        $duplicates = array_intersect($starters, $substitutes);
        if (count($duplicates) > 0) {
            return back()->withErrors(['lineup' => 'Un jugador no puede estar registrado como titular y suplente simultáneamente.'])->withInput();
        }

        $allSelectedIds = array_merge($starters, $substitutes);
        $selectedPlayers = Player::with(['team', 'medicalRecord', 'sanctions'])->whereIn('id', $allSelectedIds)->get();

        // Validación estricta del Filtro Tripartito para cada convocado
        $ineligibleErrors = [];
        foreach ($selectedPlayers as $player) {
            if ($player->team_id !== $team->id) {
                $ineligibleErrors[] = "El jugador {$player->name} no pertenece a {$team->name}.";

                continue;
            }

            $check = $eligibilityService->check($player, $match);
            if (! $check['eligible']) {
                $ineligibleErrors[] = "{$player->name} (#{$player->jersey_number}): ".implode(' ', $check['reasons']);
            }
        }

        if (count($ineligibleErrors) > 0) {
            return back()->withErrors(['eligibility' => $ineligibleErrors])->withInput();
        }

        DB::transaction(function () use ($match, $team, $starters, $substitutes, $selectedPlayers): void {
            // Eliminar nómina anterior de este equipo para este partido
            $match->lineups()->where('team_id', $team->id)->delete();

            $playerMap = $selectedPlayers->keyBy('id');

            foreach ($starters as $playerId) {
                $p = $playerMap->get($playerId);
                MatchLineup::create([
                    'match_id' => $match->id,
                    'team_id' => $team->id,
                    'player_id' => $playerId,
                    'is_starter' => true,
                    'jersey_number' => $p->jersey_number,
                    'position' => $p->profile?->position ?? 'midfielder',
                ]);
            }

            foreach ($substitutes as $playerId) {
                $p = $playerMap->get($playerId);
                MatchLineup::create([
                    'match_id' => $match->id,
                    'team_id' => $team->id,
                    'player_id' => $playerId,
                    'is_starter' => false,
                    'jersey_number' => $p->jersey_number,
                    'position' => $p->profile?->position ?? 'midfielder',
                ]);
            }
        });

        return redirect()->route('matches.show', $match)->with(
            'status',
            "Alineación oficial de {$team->name} registrada y validada digitalmente (".count($starters).' titulares, '.count($substitutes).' suplentes).'
        );
    }

    private function authorizeCoachOrAdmin(Team $team): void
    {
        $user = request()->user();
        abort_unless(
            $user && (
                $user->isSuperAdmin() ||
                ($user->isAdmin() && $team->tournament->admin_id === $user->id) ||
                $team->isManagedBy($user)
            ),
            403
        );
    }
}
