<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Player;
use App\Models\v1\Team;
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
        ]);

        // Verify jersey number is unique in this team
        if ($team->players()->where('jersey_number', $validated['jersey_number'])->exists()) {
            return back()->withErrors(['jersey_number' => 'El número de dorsal ya está ocupado en este equipo.'])->withInput();
        }

        $team->players()->create($validated);

        return back()->with('status', "Jugador {$validated['name']} agregado exitosamente al plantel.");
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

    public function destroyPlayer(Team $team, Player $player): RedirectResponse
    {
        $this->authorizeTeamCoach($team);
        abort_unless($player->team_id === $team->id, 404);

        $player->delete();

        return back()->with('status', 'Jugador removido del plantel.');
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
