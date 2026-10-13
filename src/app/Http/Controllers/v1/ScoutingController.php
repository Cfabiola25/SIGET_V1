<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Player;
use App\Models\v1\PlayerRecruitmentRequest;
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

        return view('v1.public.scouting.index', [
            'freeAgents' => $freeAgents,
            'filters' => $filters,
        ]);
    }

    public function adminIndex(Request $request): View|RedirectResponse
    {
        abort_unless(auth()->check(), 403);

        if (auth()->user()?->isAdmin() && ! auth()->user()->hasManagedTournaments()) {
            return redirect()->route('admin.dashboard')->with('warning', 'Tu cuenta de Administrador no tiene torneos asignados.');
        }

        $filters = $request->validate([
            'position' => ['nullable', 'string', 'in:goalkeeper,defender,midfielder,forward'],
            'preferred_foot' => ['nullable', 'string', 'in:right,left,ambidextrous'],
            'min_rating' => ['nullable', 'numeric', 'min:1', 'max:10'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort_by' => ['nullable', 'string', 'in:rating_desc,rating_asc,mvp_desc,name_asc'],
        ]);

        $freeAgents = $this->scoutingService->getFreeAgents($filters, 12);

        $user = auth()->user();
        $availableTeams = collect();
        if ($user) {
            if ($user->isSuperAdmin()) {
                $availableTeams = Team::orderBy('name')->get();
            } elseif ($user->isAdmin()) {
                $adminTournamentIds = $user->managedTournaments()->pluck('id');
                $availableTeams = Team::whereIn('tournament_id', $adminTournamentIds)->orderBy('name')->get();
            } elseif ($user->isCoach() || $user->isCaptain()) {
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
     * Ficha técnica y radar de talentos individual del jugador (Público).
     */
    public function show(Player $player): View
    {
        $player->load(['profile', 'team', 'events', 'sanctions']);
        $metrics = $this->scoutingService->getRadarMetrics($player);

        return view('v1.public.scouting.show', [
            'player' => $player,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Ficha técnica con opciones de reclutamiento (Admin / DT).
     */
    public function adminShow(Player $player): View
    {
        abort_unless(auth()->check(), 403);

        $player->load(['profile', 'team', 'events', 'sanctions']);
        $metrics = $this->scoutingService->getRadarMetrics($player);

        $user = auth()->user();
        $availableTeams = collect();
        if ($user) {
            if ($user->isSuperAdmin()) {
                $availableTeams = Team::orderBy('name')->get();
            } elseif ($user->isAdmin()) {
                $adminTournamentIds = $user->managedTournaments()->pluck('id');
                $availableTeams = Team::whereIn('tournament_id', $adminTournamentIds)->orderBy('name')->get();
            } elseif ($user->isCoach() || $user->isCaptain()) {
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
     * Envía una propuesta y solicitud formal de fichaje a un agente libre.
     */
    public function requestRecruitment(Request $request, Player $player): RedirectResponse
    {
        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'proposed_jersey_number' => ['nullable', 'integer', 'min:1', 'max:99'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = auth()->user();
        $team = Team::findOrFail($validated['team_id']);

        abort_unless(
            $user && ($user->isSuperAdmin() || $user->isAdmin() || $team->isManagedBy($user)),
            403,
            'No tienes autorización para solicitar fichajes a nombre de este club.'
        );

        abort_unless($player->isFreeAgent(), 422, 'El jugador no se encuentra disponible como agente libre.');

        PlayerRecruitmentRequest::create([
            'player_id' => $player->id,
            'team_id' => $team->id,
            'requested_by_user_id' => $user->id,
            'proposed_jersey_number' => $validated['proposed_jersey_number'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        return redirect()->route('scouting.requests.index')->with(
            'status',
            "Solicitud de fichaje y propuesta enviada exitosamente para {$player->name}. Se encuentra pendiente de aceptación antes de incorporarse al plantel."
        );
    }

    /**
     * Muestra las solicitudes de fichaje recibidas y enviadas.
     */
    public function recruitmentRequests(Request $request): View|RedirectResponse
    {
        abort_unless(auth()->check(), 403);
        $user = auth()->user();

        if ($user && $user->isAdmin() && ! $user->hasManagedTournaments()) {
            return redirect()->route('admin.dashboard')->with('warning', 'Tu cuenta de Administrador no tiene torneos asignados.');
        }

        if ($user && ($user->isCoach() || $user->isCaptain()) && ! $user->hasManagedTeams()) {
            return redirect()->route('dashboard')->with('warning', 'Tu cuenta de Director Técnico no tiene un equipo asignado.');
        }

        $query = PlayerRecruitmentRequest::with(['player.profile', 'team.tournament', 'requester'])
            ->latest();

        if ($user->isCoach() || $user->isCaptain()) {
            $managedTeamIds = $user->managedTeams()->pluck('id');
            $query->whereIn('team_id', $managedTeamIds);
        } elseif ($user->isAdmin()) {
            $tournamentIds = $user->managedTournaments()->pluck('id');
            $query->whereHas('team', fn ($q) => $q->whereIn('tournament_id', $tournamentIds));
        }

        $requests = $query->paginate(15);

        return view('v1.scouting.requests', compact('requests'));
    }

    /**
     * Acepta formalmente la solicitud de fichaje e incorpora al jugador al equipo.
     */
    public function acceptRecruitment(PlayerRecruitmentRequest $recruitmentRequest): RedirectResponse
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->isSuperAdmin() || $user->isAdmin() || $recruitmentRequest->player->user_id === $user->id),
            403,
            'Solo el administrador de la federación o el propio jugador pueden aceptar esta propuesta de fichaje.'
        );

        abort_unless($recruitmentRequest->isPending(), 422, 'Esta solicitud ya ha sido procesada.');

        try {
            $player = $recruitmentRequest->player;
            $team = $recruitmentRequest->team;

            $this->scoutingService->recruitPlayer($player, $team, $recruitmentRequest->proposed_jersey_number);

            $recruitmentRequest->update([
                'status' => 'accepted',
                'responded_at' => now(),
                'notes' => 'Propuesta aceptada formalmente.',
            ]);

            // Rechazar cualquier otra solicitud pendiente para este jugador
            PlayerRecruitmentRequest::where('player_id', $player->id)
                ->where('id', '!=', $recruitmentRequest->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                    'responded_at' => now(),
                    'notes' => "El jugador aceptó incorporarse oficialmente al club {$team->name}.",
                ]);

            return redirect()->route('scouting.requests.index')->with(
                'status',
                "¡Fichaje oficializado! {$player->name} se ha incorporado exitosamente al plantel de {$team->name}."
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['scouting' => $e->getMessage()]);
        }
    }

    /**
     * Rechaza una solicitud de fichaje.
     */
    public function rejectRecruitment(Request $request, PlayerRecruitmentRequest $recruitmentRequest): RedirectResponse
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->isSuperAdmin() || $user->isAdmin() || $recruitmentRequest->player->user_id === $user->id),
            403,
            'No tienes autorización para declinar esta solicitud.'
        );

        $recruitmentRequest->update([
            'status' => 'rejected',
            'responded_at' => now(),
            'notes' => $request->input('notes', 'Propuesta declinada por la administración / jugador.'),
        ]);

        return redirect()->route('scouting.requests.index')->with(
            'status',
            "La solicitud de fichaje para {$recruitmentRequest->player->name} ha sido declinada."
        );
    }

    /**
     * Recluta formalmente a un agente libre para un equipo (Administración directa Super Admin / Admin).
     */
    public function recruit(Request $request, Player $player): RedirectResponse
    {
        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'jersey_number' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $user = auth()->user();
        $team = Team::findOrFail($validated['team_id']);

        // Autorización: Super Admin, Admin, o DT que gestiona este club
        abort_unless(
            $user && ($user->isSuperAdmin() || $user->isAdmin() || $team->isManagedBy($user)),
            403,
            'No tienes autorización para fichar a nombre de este club.'
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
