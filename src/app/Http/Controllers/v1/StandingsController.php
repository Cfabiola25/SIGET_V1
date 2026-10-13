<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Standings;
use App\Models\v1\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StandingsController extends Controller
{
    public function index(Request $request): View
    {
        $tournaments = Tournament::whereIn('status', ['active', 'pending', 'completed'])->orderBy('name')->get();
        $selectedTournamentId = $request->query('tournament_id', $tournaments->firstWhere('is_featured', true)?->id ?? $tournaments->first()?->id);

        $query = Standings::with(['team.captain', 'tournament'])
            ->orderByDesc('points');

        if ($selectedTournamentId) {
            $query->where('tournament_id', $selectedTournamentId);
        }

        $standings = $query->get()->sortByDesc(fn ($s) => [
            $s->points,
            ($s->goals_for - $s->goals_against),
            $s->goals_for,
            $s->wins ?? $s->won,
        ])->values();

        return view('v1.public.standings.index', [
            'standings' => $standings,
            'tournaments' => $tournaments,
            'selectedTournamentId' => $selectedTournamentId,
        ]);
    }

    public function adminIndex(Request $request): View|RedirectResponse
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true), 403);

        if (auth()->user()?->isAdmin() && ! auth()->user()->hasManagedTournaments()) {
            return redirect()->route('admin.dashboard')->with('warning', 'Tu cuenta de Administrador no tiene torneos asignados.');
        }

        $query = Standings::with(['team', 'tournament'])->orderByDesc('points');

        if (auth()->user()->isAdmin()) {
            $adminTournamentIds = Tournament::where('admin_id', auth()->id())->pluck('id');
            $query->whereIn('tournament_id', $adminTournamentIds);
        }

        return view('v1.standings.index', [
            'standings' => $query->get(),
        ]);
    }
}
