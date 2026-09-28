<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Player;
use App\Models\v1\Team;
use App\Services\v1\ScoutingRatingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScoutingController extends Controller
{
    public function __construct(
        protected ScoutingRatingService $scoutingService
    ) {}

    /**
     * Muestra el Radar de Talentos y Transfer Market de Agentes Libres.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'position' => ['nullable', 'string', 'in:goalkeeper,defender,midfielder,forward'],
            'preferred_foot' => ['nullable', 'string', 'in:right,left,ambidextrous'],
            'min_rating' => ['nullable', 'numeric', 'min:1', 'max:10'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort_by' => ['nullable', 'string', 'in:rating_desc,rating_asc,mvp_desc,name_asc'],
        ]);

        $freeAgents = $this->scoutingService->getFreeAgents($filters, 12);

        // Equipos disponibles para fichaje si el usuario es DT o Admin
        $user = auth()->user();
        $availableTeams = collect();
        if ($user) {
            if ($user->isSuperAdmin() || $user->isAdmin()) {
                $availableTeams = Team::orderBy('name')->get();
            } elseif ($user->isCoach()) {
                $availableTeams = $user->managedTeams()->get();
            }
        }

        return view('v1.scouting.index', [
            'freeAgents' => $freeAgents,
            'filters' => $filters,
            'availableTeams' => $availableTeams,
        ]);
    }

    /**
     * Ficha técnica y radar de talentos individual del jugador.
     */
    public function show(Player $player): View
    {
        $player->load(['profile', 'team', 'events', 'sanctions']);
        $metrics = $this->scoutingService->getRadarMetrics($player);

        $user = auth()->user();
        $availableTeams = collect();
        if ($user) {
            if ($user->isSuperAdmin() || $user->isAdmin()) {
                $availableTeams = Team::orderBy('name')->get();
            } elseif ($user->isCoach()) {
                $availableTeams = $user->managedTeams()->get();
            }
        }

        return view('v1.scouting.show', [
            'player' => $player,
            'metrics' => $metrics,
            'availableTeams' => $availableTeams,
        ]);
    }

    /**
     * Recluta formalmente a un agente libre para un equipo.
     */
    public function recruit(Request $request, Player $player): RedirectResponse
    {
        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'jersey_number' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $user = auth()->user();
        $team = Team::findOrFail($validated['team_id']);

        // Autorización: Debe ser DT de ese equipo o Admin/SuperAdmin
        abort_unless(
            $user && ($user->isSuperAdmin() || $user->isAdmin() || $team->isManagedBy($user)),
            403,
            'No tienes autorización para reclutar jugadores en este club.'
        );

        try {
            $this->scoutingService->recruitPlayer($player, $team, $validated['jersey_number'] ?? null);

            return redirect()->route('scouting.index')->with('status', "¡Fichaje exitoso! {$player->name} se ha incorporado oficialmente al plantel de {$team->name}.");
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['scouting' => $e->getMessage()]);
        }
    }

    /**
     * Da de baja a un jugador del plantel y lo transfiere a la Agencia Libre.
     */
    public function release(Request $request, Player $player): RedirectResponse
    {
        $validated = $request->validate([
            'scouting_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = auth()->user();
        $team = $player->team;

        abort_unless(
            $user && ($user->isSuperAdmin() || $user->isAdmin() || ($team && $team->isManagedBy($user))),
            403,
            'No tienes autorización para dar de baja a este jugador.'
        );

        $this->scoutingService->releasePlayer($player, $validated['scouting_notes'] ?? null);

        return back()->with('status', "El jugador {$player->name} ha sido transferido a la Agencia Libre del torneo.");
    }
}
