<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Player;
use App\Models\v1\Team;
use App\Services\v1\ScoutingRatingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeamPortalController extends Controller
{
    public function dashboard(Team $team): View
    {
        $this->authorizeTeamCoach($team);

        $team->load(['tournament.rules', 'players', 'homeMatches.awayTeam', 'awayMatches.homeTeam']);

        $upcomingMatches = $team->homeMatches->concat($team->awayMatches)
            ->where('status', 'scheduled')
            ->sortBy('match_date')
            ->take(5);

        $recentMatches = $team->homeMatches->concat($team->awayMatches)
            ->where('status', 'played')
            ->sortByDesc('match_date')
            ->take(5);

        return view('v1.dt.dashboard', compact('team', 'upcomingMatches', 'recentMatches'));
    }

    public function roster(Team $team): View
    {
        $this->authorizeTeamCoach($team);

        $players = $team->players()->orderBy('jersey_number')->get();

        return view('v1.dt.roster', compact('team', 'players'));
    }

    public function storePlayer(Request $request, Team $team): RedirectResponse
    {
        $this->authorizeTeamCoach($team);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'identification_document' => ['required', 'string', 'max:50'],
            'jersey_number' => ['required', 'integer', 'between:1,99'],
            'position' => ['nullable', 'string', 'in:goalkeeper,defender,midfielder,forward'],
            'preferred_foot' => ['nullable', 'string', 'in:right,left,ambidextrous'],
            'height_cm' => ['nullable', 'integer', 'between:100,230'],
            'weight_kg' => ['nullable', 'integer', 'between:30,150'],
        ]);

        // Verify jersey number is unique in this team
        if ($team->players()->where('jersey_number', $validated['jersey_number'])->exists()) {
            return back()->withErrors(['jersey_number' => 'El número de dorsal ya está ocupado en este equipo.'])->withInput();
        }

        $player = $team->players()->create([
            'name' => $validated['name'],
            'identification_document' => $validated['identification_document'],
            'jersey_number' => $validated['jersey_number'],
        ]);

        // Update profile if additional athletic data was submitted
        if ($player->profile) {
            $player->profile->update([
                'position' => $validated['position'] ?? $player->profile->position ?? 'midfielder',
                'preferred_foot' => $validated['preferred_foot'] ?? $player->profile->preferred_foot ?? 'right',
                'height_cm' => $validated['height_cm'] ?? $player->profile->height_cm,
                'weight_kg' => $validated['weight_kg'] ?? $player->profile->weight_kg,
                'is_free_agent' => false,
            ]);
        }

        return redirect()->route('dt.roster', $team)
            ->with('status', "Jugador {$validated['name']} agregado exitosamente al plantel con su perfil deportivo oficial.");
    }

    public function importRoster(Request $request, Team $team): RedirectResponse
    {
        $this->authorizeTeamCoach($team);

        $request->validate([
            'roster_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('roster_file');
        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            return back()->withErrors(['roster_file' => 'No se pudo abrir el archivo CSV.']);
        }

        $imported = 0;
        $errors = [];
        $row = 0;

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $row++;
                // Skip header row if contains non-numeric jersey or header keywords
                if ($row === 1 && (strtolower($data[0] ?? '') === 'nombre' || strtolower($data[0] ?? '') === 'name')) {
                    continue;
                }

                $name = trim($data[0] ?? '');
                $doc = trim($data[1] ?? '');
                $dorsal = isset($data[2]) ? (int) trim($data[2]) : null;

                if (! $name || ! $doc || ! $dorsal) {
                    continue;
                }

                if ($team->players()->where('jersey_number', $dorsal)->exists()) {
                    $errors[] = "Fila {$row}: El dorsal {$dorsal} ({$name}) ya está registrado.";

                    continue;
                }

                $team->players()->create([
                    'name' => $name,
                    'identification_document' => $doc,
                    'jersey_number' => $dorsal,
                ]);

                $imported++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['roster_file' => 'Error al procesar el archivo CSV: '.$e->getMessage()]);
        } finally {
            fclose($handle);
        }

        $msg = "Se importaron {$imported} jugadores exitosamente.";
        if (count($errors) > 0) {
            $msg .= ' Avisos: '.implode(' ', $errors);
        }

        return back()->with('status', $msg);
    }

    public function releasePlayer(Team $team, Player $player, ScoutingRatingService $scoutingService): RedirectResponse
    {
        $this->authorizeTeamCoach($team);
        abort_unless($player->team_id === $team->id, 404);

        $scoutingService->releasePlayer($player);

        return redirect()->route('dt.roster', $team)
            ->with('status', "El jugador {$player->name} ha sido liberado del plantel y transferido a la Agencia Libre. Ahora está disponible en el Radar de Talentos para que otros clubes soliciten su fichaje.");
    }

    public function destroyPlayer(Team $team, Player $player, ScoutingRatingService $scoutingService): RedirectResponse
    {
        return $this->releasePlayer($team, $player, $scoutingService);
    }

    private function authorizeTeamCoach(Team $team): void
    {
        $user = request()->user();
        abort_unless(
            $user && ($user->isSuperAdmin() || ($user->isAdmin() && $team->tournament->admin_id === $user->id) || $team->isManagedBy($user)),
            403
        );
    }
}
