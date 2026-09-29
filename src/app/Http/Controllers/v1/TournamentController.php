<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\StoreTournamentRequest;
use App\Models\v1\Sport;
use App\Models\v1\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(Request $request): View
    {
        $user = request()->user();
        abort_unless($user && in_array($user->role, ['super_admin', 'admin'], true), 403);

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

        return view('v1.tournaments.index', [
            'tournaments' => $query->paginate(12)->withQueryString(),
            'sports' => Sport::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();
        abort_unless($user && ($user->isSuperAdmin() || $user->isAdmin()), 403);

        $sports = Sport::orderBy('name')->get();

        return view('v1.tournaments.create', [
            'sports' => $sports,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->isSuperAdmin() || $user->isAdmin()), 403);

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
        ]);

        $tournament = Tournament::create([
            'name' => $data['name'],
            'sport_type' => $data['sport_type'],
            'start_date' => $data['start_date'] ?? now(),
            'end_date' => $data['end_date'] ?? now()->addMonths(3),
            'status' => 'active',
            'admin_id' => $user->isAdmin() ? $user->id : null,
            'super_admin_id' => $user->isSuperAdmin() ? $user->id : null,
        ]);

        // Save Tournament Points Rules
        \App\Models\v1\TournamentRule::create([
            'tournament_id' => $tournament->id,
            'points_for_win' => (int)($data['points_win'] ?? 3),
            'points_for_draw' => (int)($data['points_draw'] ?? 1),
            'points_for_loss' => (int)($data['points_loss'] ?? 0),
            'yellow_card_limit_for_suspension' => 2,
            'direct_red_suspension_matches' => 1,
            'match_duration_minutes' => 90,
            'max_substitutions' => 5,
        ]);

        return redirect()->route('tournaments.show', $tournament)
            ->with('status', 'Torneo y fases configurados exitosamente.');
    }

    public function show(Tournament $tournament): View
    {
        $this->authorizeTournament($tournament);

        return view('v1.tournaments.show', [
            'tournament' => $tournament->load(['teams', 'matches.homeTeam', 'matches.awayTeam', 'standings.team']),
        ]);
    }

    public function edit(Tournament $tournament): View
    {
        $this->authorizeOrganizer($tournament);

        return view('v1.tournaments.edit', [
            'tournament' => $tournament,
            'sports' => Sport::orderBy('name')->get(),
        ]);
    }

    public function update(StoreTournamentRequest $request, Tournament $tournament): RedirectResponse
    {
        $this->authorizeOrganizer($tournament, $request);
        $tournament->update($request->validated());

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
