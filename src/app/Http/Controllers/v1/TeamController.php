<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        $query = Team::with(['tournament', 'captain'])->latest();

        if (auth()->user()?->isAdmin()) {
            $query->whereHas('tournament', fn ($tournaments) => $tournaments->where('admin_id', auth()->id()));
        }

        return view('v1.teams.index', ['teams' => $query->get()]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->role === 'captain', 403);

        $query = Tournament::whereIn('status', ['pending', 'active']);

        if (auth()->user()?->isAdmin()) {
            $query->where('admin_id', auth()->id());
        }

        return view('v1.teams.create', ['tournaments' => $query->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'captain'], true), 403);

        $data = $request->validate([
            'tournament_id' => ['required', 'exists:tournaments,id'],
            'name' => ['required', 'string', 'max:255'],
            'logo_path' => ['nullable', 'string', 'max:255'],
        ]);

        $tournament = Tournament::findOrFail($data['tournament_id']);
        if ($request->user()->isAdmin()) {
            abort_unless($tournament->admin_id === $request->user()->id, 403);
            abort_unless(isset($data['captain_id']), 422);
            $team = Team::create($data);
        } else {
            $team = $request->user()->captainedTeams()->create($data);
        }

        return redirect()->route('teams.show', $team);
    }

    public function show(Team $team): View
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
