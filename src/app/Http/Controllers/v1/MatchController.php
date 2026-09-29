<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\StoreMatchRequest;
use App\Http\Requests\v1\UpdateScoreRequest;
use App\Models\v1\MatchGame;
use App\Models\v1\Sport;
use App\Models\v1\Tournament;
use App\Services\StandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'sport_type' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['scheduled', 'played', 'suspended'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = MatchGame::with(['tournament', 'homeTeam', 'awayTeam'])->orderBy('match_date');

        if (auth()->user()?->isAdmin()) {
            $query->whereHas('tournament', fn ($tournaments) => $tournaments->where('admin_id', auth()->id()));
        }

        $query
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->whereHas('tournament', fn ($tournaments) => $tournaments->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('homeTeam', fn ($teams) => $teams->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('awayTeam', fn ($teams) => $teams->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['sport_type'] ?? null, fn ($query, $sport) => $query->whereHas('tournament', fn ($tournaments) => $tournaments->where('sport_type', $sport)))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('match_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('match_date', '<=', $date));

        return view('v1.matches.index', [
            'matches' => $query->paginate(12)->withQueryString(),
            'sports' => Sport::orderBy('name')->get(),
            'filters' => $filters,
        ]);
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
        abort_if($match->isLocked(), 403, 'El partido está cerrado y bloqueado oficialmente.');

        $match->update([...$request->validated(), 'status' => 'played']);
        $standings->recalculate($match->tournament);

        return redirect()->route('matches.show', $match);
    }

    public function destroy(MatchGame $match): RedirectResponse
    {
        $this->authorizeMatch($match);
        abort_if($match->isLocked(), 403, 'El partido está cerrado y bloqueado oficialmente.');

        $match->delete();

        return redirect()->route('matches.index');
    }

    public function live(): View
    {
        $query = MatchGame::with(['homeTeam', 'awayTeam', 'tournament', 'venue', 'events.player'])
            ->where(function ($q): void {
                $q->where('is_timer_running', true)
                    ->orWhereIn('current_period', ['first_half', 'halftime', 'second_half'])
                    ->orWhere('status', 'played');
            })
            ->latest('match_date');

        if (auth()->user()?->isAdmin()) {
            $query->whereHas('tournament', fn ($tournaments) => $tournaments->where('admin_id', auth()->id()));
        }

        return view('v1.matches.live', ['matches' => $query->get()]);
    }

    public function schedule(Request $request): View
    {
        $date = $request->query('date', '2024-10-24'); // Default date matching screenshot or fallback to today
        if ($request->has('today')) {
            $date = now()->format('Y-m-d');
        }

        $viewMode = $request->query('view', 'day'); // today, day, week

        $tournaments = Tournament::whereIn('status', ['pending', 'active'])->get();
        $teams = Team::orderBy('name')->get();
        $venues = \App\Models\v1\Venue::where('is_active', true)->get();
        $referees = \App\Models\v1\Referee::where('is_active', true)->get();

        $matches = MatchGame::with(['homeTeam', 'awayTeam', 'referee', 'venue'])
            ->whereDate('match_date', $date)
            ->orderBy('match_date')
            ->get();

        return view('v1.matches.scheduler', [
            'date' => $date,
            'viewMode' => $viewMode,
            'tournaments' => $tournaments,
            'teams' => $teams,
            'venues' => $venues,
            'referees' => $referees,
            'matches' => $matches,
        ]);
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'home_team_id' => ['required', 'exists:teams,id', 'different:away_team_id'],
            'away_team_id' => ['required', 'exists:teams,id'],
            'date' => ['required', 'date'],
            'time' => ['required'],
            'venue_id' => ['nullable'],
            'field_number' => ['nullable', 'integer'],
            'referee_id' => ['nullable'],
        ]);

        $homeTeam = Team::findOrFail($data['home_team_id']);
        $tournamentId = $homeTeam->tournament_id ?? Tournament::value('id') ?? 1;

        $matchDateTime = \Carbon\Carbon::parse($data['date'] . ' ' . $data['time']);

        $refereeId = null;
        if (!empty($data['referee_id']) && $data['referee_id'] !== 'auto') {
            $refereeId = (int)$data['referee_id'];
        } elseif ($data['referee_id'] === 'auto') {
            // Auto-assign first available referee
            $refereeId = \App\Models\v1\Referee::where('is_active', true)->value('id');
        }

        $venueId = null;
        if (!empty($data['venue_id']) && is_numeric($data['venue_id'])) {
            $venueId = (int)$data['venue_id'];
        } else {
            $venueId = \App\Models\v1\Venue::value('id');
        }

        MatchGame::create([
            'tournament_id' => $tournamentId,
            'home_team_id' => $data['home_team_id'],
            'away_team_id' => $data['away_team_id'],
            'match_date' => $matchDateTime,
            'venue_id' => $venueId,
            'field_number' => $data['field_number'] ?? 1,
            'referee_id' => $refereeId,
            'status' => 'scheduled',
            'round_number' => 1,
        ]);

        return redirect()->route('matches.schedule', ['date' => $data['date']])
            ->with('status', 'Partido programado exitosamente en el calendario.');
    }

    private function authorizeMatch(MatchGame $match): void
    {
        $user = request()->user();
        abort_unless($user && ($user->isSuperAdmin() || ($user->isAdmin() && $match->tournament->admin_id === $user->id)), 403);
    }
}
