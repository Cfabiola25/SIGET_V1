<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\StoreTournamentRequest;
use App\Models\v1\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        abort_unless($user && in_array($user->role, ['super_admin', 'admin'], true), 403);

        $query = Tournament::with('admin')->latest();

        if ($user->isAdmin()) {
            $query->where('admin_id', $user->id);
        }

        return view('v1.tournaments.index', ['tournaments' => $query->get()]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        return view('v1.tournaments.create');
    }

    public function store(StoreTournamentRequest $request): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $tournament = $request->user()->createdTournaments()->create($request->validated());

        return redirect()->route('tournaments.show', $tournament);
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

        return view('v1.tournaments.edit', compact('tournament'));
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
