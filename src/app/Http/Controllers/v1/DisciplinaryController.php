<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\DisciplinarySanction;
use App\Models\v1\Player;
use App\Models\v1\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DisciplinaryController extends Controller
{
    public function index(Tournament $tournament): View
    {
        $tournament->load('rules');

        $activeSanctions = $tournament->sanctions()
            ->with(['player.team', 'match.homeTeam', 'match.awayTeam'])
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->get();

        $historySanctions = $tournament->sanctions()
            ->with(['player.team', 'match'])
            ->where('status', '!=', 'active')
            ->orderByDesc('created_at')
            ->take(15)
            ->get();

        $yellowCardLeaders = Player::whereHas('team', fn ($q) => $q->where('tournament_id', $tournament->id))
            ->whereHas('events', fn ($q) => $q->where('event_type', 'yellow_card')
                ->whereHas('match', fn ($m) => $m->where('tournament_id', $tournament->id)))
            ->with('team')
            ->withCount(['events as yellow_cards_count' => fn ($q) => $q->where('event_type', 'yellow_card')
                ->whereHas('match', fn ($m) => $m->where('tournament_id', $tournament->id)),
            ])
            ->orderByDesc('yellow_cards_count')
            ->take(10)
            ->get();

        $redCardLeaders = Player::whereHas('team', fn ($q) => $q->where('tournament_id', $tournament->id))
            ->whereHas('events', fn ($q) => $q->where('event_type', 'red_card')
                ->whereHas('match', fn ($m) => $m->where('tournament_id', $tournament->id)))
            ->with('team')
            ->withCount(['events as red_cards_count' => fn ($q) => $q->where('event_type', 'red_card')
                ->whereHas('match', fn ($m) => $m->where('tournament_id', $tournament->id)),
            ])
            ->orderByDesc('red_cards_count')
            ->take(10)
            ->get();

        return view('v1.tournaments.disciplinary', compact(
            'tournament',
            'activeSanctions',
            'historySanctions',
            'yellowCardLeaders',
            'redCardLeaders'
        ));
    }

    public function pardon(Tournament $tournament, DisciplinarySanction $sanction): RedirectResponse
    {
        $user = request()->user();
        abort_unless(
            $user && (
                $user->isSuperAdmin() ||
                ($user->isAdmin() && $tournament->admin_id === $user->id)
            ),
            403
        );

        abort_unless($sanction->tournament_id === $tournament->id, 404);

        $sanction->update([
            'status' => 'pardoned',
            'notes' => ($sanction->notes ? $sanction->notes.' | ' : '').'Indultado administrativamente por la comisión organizadora el '.now()->toFormattedDateString(),
        ]);

        return redirect()
            ->route('tournaments.disciplinary', $tournament)
            ->with('status', "Sanción indultada exitosamente para {$sanction->player->name}.");
    }
}
