<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Referee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefereePortalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Obtener el perfil de árbitro vinculado al usuario
        $referee = Referee::where('user_id', $user->id)->first()
            ?? Referee::where('name', $user->name)->first();

        abort_unless($referee, 404, 'No se encontró el perfil arbitral vinculado a esta cuenta.');

        // Cargar los partidos asignados a este árbitro
        $allMatches = $referee->matches()
            ->with([
                'tournament',
                'homeTeam.players.profile',
                'awayTeam.players.profile',
                'venue',
                'evaluations',
            ])
            ->orderBy('match_date')
            ->get();

        $upcomingMatches = $allMatches
            ->whereIn('status', ['scheduled', 'pending'])
            ->sortBy('match_date')
            ->values();

        $liveMatches = $allMatches
            ->filter(fn ($m) => $m->status === 'live' || $m->is_timer_running)
            ->values();

        $pastMatches = $allMatches
            ->where('status', 'played')
            ->sortByDesc('match_date')
            ->values();

        // Evaluaciones recibidas
        $evaluations = $referee->evaluations()
            ->with(['match.homeTeam', 'match.awayTeam', 'team'])
            ->latest()
            ->take(10)
            ->get();

        return view('v1.referees.portal', compact(
            'referee',
            'upcomingMatches',
            'liveMatches',
            'pastMatches',
            'evaluations'
        ));
    }
}
