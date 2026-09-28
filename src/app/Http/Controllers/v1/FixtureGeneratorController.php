<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Tournament;
use App\Models\v1\Venue;
use App\Services\v1\FixtureGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FixtureGeneratorController extends Controller
{
    public function show(Tournament $tournament): View
    {
        $this->authorizeAdmin($tournament);

        $tournament->load('teams');
        $venues = Venue::where('is_active', true)->get();

        return view('v1.tournaments.fixtures.generate', compact('tournament', 'venues'));
    }

    public function generate(
        Request $request,
        Tournament $tournament,
        FixtureGeneratorService $fixtureService
    ): RedirectResponse {
        $this->authorizeAdmin($tournament);

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'days_between_rounds' => ['nullable', 'integer', 'min:1', 'max:30'],
            'rounds_count' => ['nullable', 'integer', 'in:1,2'],
            'time_slots' => ['nullable', 'array'],
            'venue_ids' => ['nullable', 'array'],
            'venue_ids.*' => ['exists:venues,id'],
        ]);

        $matches = $fixtureService->generateAndPersist($tournament, $validated);

        return redirect()
            ->route('tournaments.show', $tournament)
            ->with('status', "¡Fixture generado exitosamente! Se programaron {$matches->count()} partidos según el algoritmo oficial Berger.");
    }

    private function authorizeAdmin(Tournament $tournament): void
    {
        $user = request()->user();
        abort_unless(
            $user && (
                $user->isSuperAdmin() ||
                ($user->isAdmin() && $tournament->admin_id === $user->id)
            ),
            403
        );
    }
}
