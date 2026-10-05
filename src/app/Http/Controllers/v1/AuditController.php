<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\AuditLog;
use App\Models\v1\DisciplinarySanction;
use App\Models\v1\MatchEvent;
use App\Models\v1\MatchGame;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin'], true), 403);

        // Fetch or simulate pending referee audits in Spanish
        $pendingAudits = [
            [
                'match_id' => 842,
                'referee' => 'Árb. Thomas Pérez',
                'badge' => 'Marcador en Disputa',
                'badge_color' => 'rose',
                'summary' => 'Metro City Strikers (2) vs Eastside Rangers (1). Reclamación oficial registrada por el capitán sobre el minuto del gol definitivo.',
                'action_primary' => 'Resolver Disputa',
                'action_secondary' => 'Revisar Acta',
                'route_secondary' => route('matches.index'),
                'time_ago' => 'Hace 15 min',
            ],
            [
                'match_id' => 840,
                'referee' => 'Árb. Carlos Davis',
                'badge' => 'Revisión de Tarjeta Roja',
                'badge_color' => 'slate',
                'summary' => "Jugador #9 (Valley Heights) expulsado al min 78'. Requiere revisión disciplinaria de fechas de suspensión obligatoria.",
                'action_primary' => 'Procesar Sanción',
                'action_secondary' => null,
                'route_secondary' => null,
                'time_ago' => 'Hace 42 min',
            ],
            [
                'match_id' => 838,
                'referee' => 'Árb. Andrés Martínez',
                'badge' => 'Alineación a Destiempo',
                'badge_color' => 'amber',
                'summary' => 'Modificación de nómina enviada a 8 minutos del pitazo inicial. Pendiente de validación de alineación de emergencia.',
                'action_primary' => 'Validar Planilla',
                'action_secondary' => 'Inspeccionar',
                'route_secondary' => route('matches.index'),
                'time_ago' => 'Hace 2 horas',
            ],
        ];

        // Real red cards from DB
        $redCardEvents = MatchEvent::where('event_type', 'red_card')
            ->with(['match.homeTeam', 'match.awayTeam', 'player'])
            ->latest()
            ->take(5)
            ->get();

        // System Audit Logs
        $auditLogs = AuditLog::latest()->take(20)->get();

        $stats = [
            'total_pending' => count($pendingAudits),
            'disputed_count' => 1,
            'red_cards_pending' => 1,
            'resolved_today' => 14,
        ];

        return view('v1.audit.index', compact('pendingAudits', 'redCardEvents', 'auditLogs', 'stats'));
    }
}
