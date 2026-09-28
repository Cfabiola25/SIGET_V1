<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Tournament;
use App\Models\v1\TournamentRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TournamentRuleController extends Controller
{
    public function edit(Tournament $tournament): View
    {
        $this->authorizeTournamentAdmin($tournament);

        $rules = $tournament->rules ?? $tournament->rules()->create(
            TournamentRule::defaultRulesForFootball($tournament->id)
        );

        return view('v1.tournaments.rules', compact('tournament', 'rules'));
    }

    public function update(Request $request, Tournament $tournament): RedirectResponse
    {
        $this->authorizeTournamentAdmin($tournament);

        $validated = $request->validate([
            'yellow_card_limit_for_suspension' => ['required', 'integer', 'min:1', 'max:10'],
            'direct_red_suspension_matches' => ['required', 'integer', 'min:1', 'max:10'],
            'points_for_win' => ['required', 'integer', 'min:1', 'max:10'],
            'points_for_draw' => ['required', 'integer', 'min:0', 'max:5'],
            'points_for_loss' => ['required', 'integer', 'min:0', 'max:5'],
            'match_duration_minutes' => ['required', 'integer', 'min:20', 'max:120'],
            'max_substitutions' => ['required', 'integer', 'min:1', 'max:11'],
            'tiebreaker_rule' => ['required', 'in:goal_difference,head_to_head,goals_for,fair_play'],
            'reset_cards_on_knockout' => ['required', 'boolean'],
            'lineup_lock_minutes_before_match' => ['required', 'integer', 'min:0', 'max:120'],
        ]);

        $tournament->rules()->updateOrCreate(
            ['tournament_id' => $tournament->id],
            $validated
        );

        return redirect()->route('tournaments.show', $tournament)->with('status', 'Reglamento de competición actualizado correctamente.');
    }

    private function authorizeTournamentAdmin(Tournament $tournament): void
    {
        $user = request()->user();
        abort_unless(
            $user && ($user->isSuperAdmin() || ($user->isAdmin() && $tournament->admin_id === $user->id)),
            403
        );
    }
}
