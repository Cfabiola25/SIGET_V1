<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\StoreTournamentRequest;
use App\Models\User;
use App\Models\v1\Sport;
use App\Models\v1\Tournament;
use App\Models\v1\TournamentRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = request()->user();
        abort_unless($user && in_array($user->role, ['super_admin', 'admin'], true), 403);

        if ($user->isAdmin() && ! $user->hasManagedTournaments()) {
            return redirect()->route('admin.dashboard')->with('warning', 'Tu cuenta de Administrador aún no tiene torneos asignados.');
        }

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'sport_type' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'active', 'completed'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = Tournament::with('admin')->latest();

        if ($user->isAdmin()) {
            $query->where('admin_id', $user->id);
        }

        $query
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($filters['sport_type'] ?? null, fn ($query, $sport) => $query->where('sport_type', $sport))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('start_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('end_date', '<=', $date));

        $activeAdmins = $user->isSuperAdmin()
            ? User::where('role', 'admin')->where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('v1.tournaments.index', [
            'tournaments' => $query->paginate(12)->withQueryString(),
            'sports' => Sport::orderBy('name')->get(),
            'filters' => $filters,
            'activeAdmins' => $activeAdmins,
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();
        abort_unless($user && ($user->isSuperAdmin() || ($user->isAdmin() && $user->hasManagedTournaments())), 403);

        $sports = Sport::orderBy('name')->get();
        $activeAdmins = $user->isSuperAdmin()
            ? User::where('role', 'admin')->where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('v1.tournaments.create', [
            'sports' => $sports,
            'activeAdmins' => $activeAdmins,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->isSuperAdmin() || ($user->isAdmin() && $user->hasManagedTournaments())), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sport_type' => ['required', 'string'],
            'season' => ['nullable', 'string', 'max:20'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'points_win' => ['nullable', 'integer'],
            'points_draw' => ['nullable', 'integer'],
            'points_loss' => ['nullable', 'integer'],
            'bonus_goals' => ['nullable', 'boolean'],
            'admin_id' => ['nullable', 'exists:users,id'],
        ]);

        $adminId = $user->isAdmin() ? $user->id : ($data['admin_id'] ?? null);

        $tournament = Tournament::create([
            'name' => $data['name'],
            'sport_type' => $data['sport_type'],
            'start_date' => $data['start_date'] ?? now(),
            'end_date' => $data['end_date'] ?? now()->addMonths(3),
            'status' => 'active',
            'admin_id' => $adminId,
            'super_admin_id' => $user->isSuperAdmin() ? $user->id : null,
        ]);

        // Reglas de competición automáticas según modalidad de fútbol
        $ruleData = TournamentRule::rulesForModality($data['sport_type'], $tournament->id);
        if (isset($data['points_win'])) {
            $ruleData['points_for_win'] = (int) $data['points_win'];
        }
        if (isset($data['points_draw'])) {
            $ruleData['points_for_draw'] = (int) $data['points_draw'];
        }
        if (isset($data['points_loss'])) {
            $ruleData['points_for_loss'] = (int) $data['points_loss'];
        }
        TournamentRule::create($ruleData);

        return redirect()->route('tournaments.show', $tournament)
            ->with('status', 'Torneo y fases configurados exitosamente.');
    }

    public function show(Tournament $tournament): View
    {
        $this->authorizeTournament($tournament);

        $coaches = User::whereIn('role', ['coach', 'captain'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('v1.tournaments.show', [
            'tournament' => $tournament->load(['teams.captain', 'matches.homeTeam', 'matches.awayTeam', 'standings.team']),
            'coaches' => $coaches,
        ]);
    }

    public function edit(Tournament $tournament): View
    {
        $this->authorizeOrganizer($tournament);

        $user = auth()->user();
        $activeAdmins = $user && $user->isSuperAdmin()
            ? User::where('role', 'admin')->where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('v1.tournaments.edit', [
            'tournament' => $tournament,
            'sports' => Sport::orderBy('name')->get(),
            'activeAdmins' => $activeAdmins,
        ]);
    }

    public function update(StoreTournamentRequest $request, Tournament $tournament): RedirectResponse
    {
        $this->authorizeOrganizer($tournament, $request);
        $data = $request->validated();

        if ($request->user()?->isSuperAdmin() && $request->has('admin_id')) {
            $data['admin_id'] = $request->input('admin_id') ?: null;
        }

        $tournament->update($data);

        return redirect()->route('tournaments.show', $tournament);
    }

    public function destroy(Request $request, Tournament $tournament): RedirectResponse
    {
        $this->authorizeOrganizer($tournament, $request);
        $tournament->delete();

        return redirect()->route('tournaments.index');
    }

    private function authorizeOrganizer(Tournament $tournament, ?Request $request = null): void
    {
        $user = ($request ?? request())->user();
        abort_unless($user && (($user->isSuperAdmin()) || ($user->isAdmin() && $tournament->admin_id === $user->id)), 403);
    }

    private function authorizeTournament(Tournament $tournament): void
    {
        $this->authorizeOrganizer($tournament);
    }
}
