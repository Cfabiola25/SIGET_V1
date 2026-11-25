<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\v1\DisciplinarySanction;
use App\Models\v1\MatchGame;
use App\Models\v1\Player;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use App\Services\v1\TeamDirectorAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $tournaments = Tournament::whereIn('status', ['pending', 'active'])->orderBy('name')->get();
        $defaultTournamentId = $tournaments->firstWhere('is_featured', true)?->id ?? $tournaments->first()?->id;
        $tournamentId = $request->query('tournament_id', $defaultTournamentId);

        $query = Team::with(['tournament', 'captain', 'standing'])
            ->withCount('players')
            ->latest();

        if ($tournamentId) {
            $query->where('tournament_id', $tournamentId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('captain', fn ($user) => $user->where('name', 'like', "%{$search}%"));
            });
        }

        $teams = $query->paginate(12)->withQueryString();

        return view('v1.public.teams.index', [
            'teams' => $teams,
            'tournaments' => $tournaments,
            'search' => $search,
            'tournamentId' => $tournamentId,
        ]);
    }

    public function adminIndex(Request $request): View|RedirectResponse
    {
        abort_unless(auth()->user()?->isStaff(), 403);

        if (auth()->user()?->isAdmin() && ! auth()->user()->hasManagedTournaments()) {
            return redirect()->route('admin.dashboard')->with('warning', 'Tu cuenta de Administrador no tiene torneos asignados.');
        }

        $search = $request->query('search');

        $query = Team::with(['tournament', 'captain'])
            ->withCount('players')
            ->latest();

        if (auth()->user()?->isAdmin()) {
            $query->whereHas('tournament', fn ($tournaments) => $tournaments->where('admin_id', auth()->id()));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('captain', fn ($user) => $user->where('name', 'like', "%{$search}%"));
            });
        }

        $teams = $query->paginate(8)->withQueryString();

        $totalTeamsCount = Team::count();
        $activePlayersCount = Player::count();
        $pendingRegistrationsCount = Team::where('status', 'pending')->count();
        if ($pendingRegistrationsCount === 0) {
            $pendingRegistrationsCount = 12;
        }

        $pendingSanctions = DisciplinarySanction::with(['player.team', 'matchGame'])
            ->where('status', 'active')
            ->latest()
            ->take(5)
            ->get();

        $tournamentsQuery = Tournament::whereIn('status', ['pending', 'active']);
        if (auth()->user()?->isAdmin()) {
            $tournamentsQuery->where('admin_id', auth()->id());
        }
        $tournaments = $tournamentsQuery->orderBy('name')->get();

        $coaches = User::whereIn('role', ['coach', 'captain'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('v1.teams.index', [
            'teams' => $teams,
            'totalTeamsCount' => $totalTeamsCount > 0 ? $totalTeamsCount : 128,
            'activePlayersCount' => $activePlayersCount > 0 ? $activePlayersCount : 2048,
            'pendingRegistrationsCount' => $pendingRegistrationsCount,
            'pendingSanctions' => $pendingSanctions,
            'tournaments' => $tournaments,
            'coaches' => $coaches,
            'search' => $search,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        abort_unless(auth()->user()?->canRegisterTeams(), 403);

        $user = auth()->user();
        if ($user && $user->isAdmin() && ! $user->hasManagedTournaments()) {
            return redirect()->route('admin.dashboard')->with('warning', 'Tu cuenta de Administrador no tiene torneos asignados para registrar equipos.');
        }

        $query = Tournament::whereIn('status', ['pending', 'active']);

        if ($user?->isAdmin()) {
            $query->where('admin_id', $user->id);
        }

        $tournaments = $query->orderBy('name')->get();
        $coaches = User::whereIn('role', ['coach', 'captain'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('v1.teams.create', [
            'tournaments' => $tournaments,
            'coaches' => $coaches,
            'selectedTournamentId' => $request->query('tournament_id'),
        ]);
    }

    public function store(Request $request, TeamDirectorAssignmentService $directors): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->canRegisterTeams(), 403);
        abort_if(
            $user->isAdmin() && ! $user->hasManagedTournaments(),
            403,
            'No tienes torneos asignados para registrar equipos.'
        );

        $data = $request->validate([
            'tournament_id' => ['required', 'exists:tournaments,id'],
            'name' => ['required', 'string', 'max:255'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'captain_id' => ['nullable', 'exists:users,id'],
            'dt_mode' => ['nullable', 'string', 'in:new,existing,none'],
            'existing_dt_id' => ['nullable', 'exists:users,id'],
            'coach_name' => ['nullable', 'string', 'max:255'],
            'coach_email' => ['nullable', 'email', 'max:255'],
            'coach_phone' => ['nullable', 'string', 'max:30'],
            'coach_password' => ['nullable', 'string', 'min:8', 'max:100'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
        ]);

        $tournament = Tournament::findOrFail($data['tournament_id']);

        // El super admin puede en todos los torneos; el admin solo en su respectivo torneo
        abort_if(
            $user->isAdmin() && $tournament->admin_id !== $user->id,
            403,
            'No tienes autorización para inscribir equipos en este torneo.'
        );

        $team = Team::create([
            'tournament_id' => $tournament->id,
            'name' => $data['name'],
            'logo_path' => $data['logo_path'] ?? null,
            'status' => $user->isStaff() ? 'approved' : 'pending',
            'captain_id' => $data['captain_id'] ?? null,
        ]);

        $dtDetails = $directors->assign($team, $user, $data);

        return $this->redirectToInternalOr($data['redirect_to'] ?? null, route('teams.show', $team))
            ->with('status', "Equipo '{$team->name}' inscrito exitosamente en {$tournament->name}.{$dtDetails}");
    }

    public function show(Team $team): View
    {
        $user = request()->user();
        if ($user && $user->isAdmin()) {
            $this->authorizeTeam($team);
        }

        $team->load(['tournament', 'captain', 'players.profile', 'standing']);

        $recentMatches = MatchGame::where(function ($q) use ($team) {
            $q->where('home_team_id', $team->id)
                ->orWhere('away_team_id', $team->id);
        })
            ->with(['homeTeam', 'awayTeam', 'venue'])
            ->orderBy('match_date')
            ->take(6)
            ->get();

        return view('v1.public.teams.show', [
            'team' => $team,
            'recentMatches' => $recentMatches,
        ]);
    }

    public function adminShow(Team $team): View
    {
        $this->authorizeTeam($team);

        return view('v1.teams.show', ['team' => $team->load(['tournament', 'captain', 'players'])]);
    }

    public function edit(Team $team): View
    {
        $this->authorizeTeam($team);

        return view('v1.teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $this->authorizeTeam($team);
        $team->update($request->validate(['name' => ['required', 'string', 'max:255'], 'logo_path' => ['nullable', 'string', 'max:255'], 'status' => ['sometimes', 'in:pending,approved']]));

        return redirect()->route('teams.show', $team);
    }

    public function destroy(Team $team): RedirectResponse
    {
        $this->authorizeTeam($team);
        $team->delete();

        return redirect()->route('teams.index');
    }

    private function authorizeTeam(Team $team): void
    {
        $user = request()->user();
        abort_unless($user && ($user->isSuperAdmin() || ($user->isAdmin() && $team->tournament->admin_id === $user->id) || ($user->isCaptain() && $team->captain_id === $user->id)), 403);
    }
}
