<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\StoreMatchRequest;
use App\Http\Requests\v1\UpdateScoreRequest;
use App\Models\v1\MatchGame;
use App\Models\v1\Tournament;
use App\Services\StandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function index(): View
    {
        $query = MatchGame::with(['tournament', 'homeTeam', 'awayTeam'])->orderBy('match_date');

        if (auth()->user()?->isAdmin()) {
            $query->whereHas('tournament', fn ($tournaments) => $tournaments->where('admin_id', auth()->id()));
        }

        return view('v1.matches.index', ['matches' => $query->get()]);
    }

    public function create(): View
    {
        abort_unless(in_array(auth()->user()?->role, ['super_admin', 'admin'], true), 403);

        $query = Tournament::with('teams')->whereIn('status', ['pending', 'active']);
        if (auth()->user()->isAdmin()) {
            $query->where('admin_id', auth()->id());
        }

        return view('v1.matches.create', ['tournaments' => $query->get()]);
    }

    public function store(StoreMatchRequest $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['super_admin', 'admin'], true), 403);
        $tournament = Tournament::findOrFail($request->validated()['tournament_id']);
        abort_unless($request->user()->isSuperAdmin() || $tournament->admin_id === $request->user()->id, 403);
        $match = MatchGame::create($request->validated());

        return redirect()->route('matches.show', $match);
    }

    public function show(MatchGame $match): View
    {
        $this->authorizeMatch($match);

        return view('v1.matches.show', ['match' => $match->load(['tournament', 'homeTeam', 'awayTeam'])]);
    }

    public function edit(MatchGame $match): View
    {
        $this->authorizeMatch($match);

        return view('v1.matches.edit', compact('match'));
    }

    public function update(UpdateScoreRequest $request, MatchGame $match, StandingService $standings): RedirectResponse
    {
        $this->authorizeMatch($match);
        $match->update([...$request->validated(), 'status' => 'played']);
        $standings->recalculate($match->tournament);

        return redirect()->route('matches.show', $match);
    }

    public function destroy(MatchGame $match): RedirectResponse
    {
        $this->authorizeMatch($match);
        $match->delete();

        return redirect()->route('matches.index');
    }

    public function live(): View
    {
        $query = MatchGame::with(['homeTeam', 'awayTeam'])->where('status', 'played')->latest('match_date');
        if (auth()->user()?->isAdmin()) {
            $query->whereHas('tournament', fn ($tournaments) => $tournaments->where('admin_id', auth()->id()));
        }

        return view('v1.matches.live', ['matches' => $query->get()]);
    }

    private function authorizeMatch(MatchGame $match): void
    {
        $user = request()->user();
        abort_unless($user && ($user->isSuperAdmin() || ($user->isAdmin() && $match->tournament->admin_id === $user->id)), 403);
    }
}
