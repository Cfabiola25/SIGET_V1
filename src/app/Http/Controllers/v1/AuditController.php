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
        // Fetch or simulate pending referee audits like in the screenshot
        $pendingAudits = [
            [
                'match_id' => 842,
                'referee' => 'Ref. Thomas',
                'badge' => 'Disputed Score',
                'badge_color' => 'rose',
                'summary' => 'Metro City Strikers (2) vs Eastside Rangers (1). Captain dispute logged regarding final goal timing.',
                'action_primary' => 'Resolve',
                'action_secondary' => 'Review Log',
                'route_secondary' => route('matches.index'),
                'time_ago' => '15m ago',
            ],
            [
                'match_id' => 840,
                'referee' => 'Ref. Davis',
                'badge' => 'Red Card Review',
                'badge_color' => 'slate',
                'summary' => "Player #9 (Valley Heights) sent off 78'. Mandatory suspension review required.",
                'action_primary' => 'Process Suspension',
                'action_secondary' => null,
                'route_secondary' => null,
                'time_ago' => '42m ago',
            ],
            [
                'match_id' => 838,
                'referee' => 'Ref. Martinez',
                'badge' => 'Lineup Late Entry',
                'badge_color' => 'amber',
                'summary' => 'Late roster modification submitted 8 minutes before kickoff. Sanction review pending.',
                'action_primary' => 'Validate Entry',
                'action_secondary' => 'Inspect',
                'route_secondary' => route('matches.index'),
                'time_ago' => '2h ago',
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
