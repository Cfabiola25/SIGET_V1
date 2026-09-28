<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\MatchGame;
use App\Models\v1\Referee;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use App\Services\v1\RefereeAssignmentEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefereeEvaluationController extends Controller
{
    public function __construct(
        protected RefereeAssignmentEngine $refereeEngine
    ) {}

    /**
     * Muestra el panel de profesionalización arbitral, ratings y asignaciones.
     */
    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user && ($user->isSuperAdmin() || $user->isAdmin()), 403);

        $referees = Referee::withCount('matches')
            ->with(['evaluations.evaluatedBy', 'evaluations.team', 'conflictRecords.team'])
            ->orderByDesc('rating_average')
            ->get();

        $teams = Team::orderBy('name')->get();
        $tournaments = Tournament::whereIn('status', ['pending', 'active'])->get();

        return view('v1.referees.evaluations_index', [
            'referees' => $referees,
            'teams' => $teams,
            'tournaments' => $tournaments,
        ]);
    }

    /**
     * El Director Técnico (DT) evalúa el desempeño arbitral post-partido.
     */
    public function evaluate(Request $request, MatchGame $match): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'score_overall' => ['required', 'integer', 'min:1', 'max:5'],
            'score_rule_enforcement' => ['required', 'integer', 'min:1', 'max:5'],
            'score_fairness' => ['required', 'integer', 'min:1', 'max:5'],
            'score_punctuality' => ['required', 'integer', 'min:1', 'max:5'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = auth()->user();
        $teamId = (int) $validated['team_id'];

        // Validar que el usuario sea el DT del equipo o Admin
        abort_unless(
            $user && ($user->isSuperAdmin() || $user->isAdmin() || ($user->isCoach() && Team::where('id', $teamId)->where('captain_id', $user->id)->exists())),
            403,
            'Solo el Director Técnico de uno de los equipos participantes puede calificar al cuerpo arbitral.'
        );

        try {
            $evaluation = $this->refereeEngine->submitEvaluation(
                $match,
                $teamId,
                $user->id,
                $validated
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Evaluación arbitral enviada y promediada correctamente.',
                    'evaluation' => $evaluation,
                ]);
            }

            return back()->with('status', '¡Evaluación arbitral enviada exitosamente! Tus observaciones aportan a la profesionalización del torneo.');
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['referee' => $e->getMessage()]);
        }
    }

    /**
     * Motor de Asignación Algorítmica: designa árbitro para un partido específico.
     */
    public function autoAssignMatch(MatchGame $match): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        abort_unless($user && ($user->isSuperAdmin() || $user->isAdmin()), 403);

        $referee = $this->refereeEngine->assignReferee($match);

        if (! $referee) {
            $msg = 'No se encontró un árbitro disponible sin conflictos de interés para este encuentro.';
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->withErrors(['referee' => $msg]);
        }

        $msg = "Árbitro {$referee->name} (Rating: {$referee->rating_average} ★) asignado algorítmicamente sin conflictos de interés.";

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'referee' => $referee,
            ]);
        }

        return back()->with('status', $msg);
    }

    /**
     * Asignación algorítmica masiva para toda una jornada de torneo.
     */
    public function bulkAssignRound(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'tournament_id' => ['required', 'integer', 'exists:tournaments,id'],
            'round_number' => ['required', 'integer', 'min:1'],
        ]);

        $user = auth()->user();
        abort_unless($user && ($user->isSuperAdmin() || $user->isAdmin()), 403);

        $result = $this->refereeEngine->bulkAssignForRound(
            (int) $validated['tournament_id'],
            (int) $validated['round_number']
        );

        $msg = "Asignación completada: {$result['assigned']} partidos asignados algorítmicamente, {$result['unassigned']} sin árbitro disponible.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'data' => $result,
            ]);
        }

        return back()->with('status', $msg);
    }

    /**
     * Registra un conflicto de interés formal.
     */
    public function registerConflict(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'referee_id' => ['required', 'integer', 'exists:referees,id'],
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        abort_unless($user && ($user->isSuperAdmin() || $user->isAdmin()), 403);

        $this->refereeEngine->registerConflict(
            (int) $validated['referee_id'],
            (int) $validated['team_id'],
            $validated['reason']
        );

        return back()->with('status', 'Conflicto de interés registrado. El motor algorítmico evitará que este árbitro sea designado para dicho club.');
    }
}
