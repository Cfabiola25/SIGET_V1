<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\AuditLog;
use App\Models\v1\MatchEvent;
use App\Models\v1\MatchGame;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true), 403);

        if (auth()->user()?->isAdmin() && ! auth()->user()->hasManagedTournaments()) {
            return redirect()->route('admin.dashboard')->with('warning', 'Tu cuenta de Administrador no tiene torneos asignados.');
        }

        $pendingMatches = MatchGame::with(['homeTeam', 'awayTeam', 'referee'])
            ->where('status', 'played')
            ->where('is_locked', false)
            ->latest('match_date')
            ->take(10)
            ->get();

        $pendingAudits = $pendingMatches->map(function ($m) {
            return [
                'match_id' => $m->id,
                'referee' => $m->referee?->name ?? 'Árbitro Oficial',
                'badge' => 'Cierre de Acta Pendiente',
                'badge_color' => 'amber',
                'summary' => "{$m->homeTeam?->name} ({$m->home_score}) vs {$m->awayTeam?->name} ({$m->away_score}). Pendiente de validación y firma de planilla oficial.",
                'action_primary' => 'Revisar Cierre',
                'action_secondary' => 'Ver Partido',
                'route_primary' => route('matches.closure', $m),
                'route_secondary' => route('matches.show', $m),
                'time_ago' => $m->updated_at ? $m->updated_at->diffForHumans() : 'Reciente',
            ];
        });

        // Eventos reales de tarjeta roja desde la BD
        $redCardEvents = MatchEvent::where('event_type', 'red_card')
            ->with(['match.homeTeam', 'match.awayTeam', 'player'])
            ->latest()
            ->take(5)
            ->get();

        // Trazabilidad de Auditoría del Sistema
        $auditLogs = AuditLog::with('user')->latest()->take(30)->get();

        $stats = [
            'total_pending' => $pendingAudits->count(),
            'disputed_count' => 0,
            'red_cards_pending' => $redCardEvents->count(),
            'resolved_today' => AuditLog::whereDate('created_at', today())->count(),
        ];

        return view('v1.audit.index', compact('pendingAudits', 'redCardEvents', 'auditLogs', 'stats'));
    }
}
