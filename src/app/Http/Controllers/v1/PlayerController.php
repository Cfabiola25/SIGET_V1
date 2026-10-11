<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Player;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlayerController extends Controller
{
    public function index(Request $request): View|StreamedResponse|RedirectResponse
    {
        $user = $request->user();
        if ($user && in_array($user->role, ['captain', 'coach'], true)) {
            $myTeam = $user->managedTeams()->first();
            if ($myTeam) {
                return redirect()->route('dt.roster', $myTeam);
            }
        }

        $search = $request->query('search');
        $teamId = $request->query('team_id');
        $position = $request->query('position');

        if ($request->query('export') === 'csv') {
            return $this->exportCsv($request);
        }

        $query = Player::with(['team', 'profile', 'sanctions' => fn ($q) => $q->where('status', 'active')])
            ->latest('id');

        if ($user && $user->isAdmin()) {
            $adminTournamentIds = Tournament::where('admin_id', $user->id)->pluck('id');
            $query->where(function ($q) use ($adminTournamentIds) {
                $q->whereHas('team', fn ($t) => $t->whereIn('tournament_id', $adminTournamentIds))
                    ->orWhereNull('team_id');
            });
            $teams = Team::whereIn('tournament_id', $adminTournamentIds)->orderBy('name')->get();
        } else {
            $teams = Team::orderBy('name')->get();
        }

        if ($search) {
            // Check if search looks like PLY-001 or standard string
            $cleanSearch = preg_replace('/[^0-9]/', '', $search);
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('identification_document', 'like', "%{$search}%");
                if ($cleanSearch) {
                    $q->orWhere('id', (int) $cleanSearch);
                }
            });
        }

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        $posMap = [
            'FW' => 'forward',
            'MF' => 'midfielder',
            'DF' => 'defender',
            'GK' => 'goalkeeper',
            'forward' => 'forward',
            'midfielder' => 'midfielder',
            'defender' => 'defender',
            'goalkeeper' => 'goalkeeper',
        ];

        if ($position) {
            $mapped = $posMap[strtoupper($position)] ?? ($posMap[strtolower($position)] ?? $position);
            $query->whereHas('profile', function ($q) use ($position, $mapped) {
                $q->whereIn('position', array_unique([$position, $mapped]));
            });
        }

        $players = $query->paginate(10)->withQueryString();

        return view('v1.players.index', [
            'players' => $players,
            'teams' => $teams,
            'search' => $search,
            'teamId' => $teamId,
            'position' => $position,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $search = $request->query('search');
        $teamId = $request->query('team_id');
        $position = $request->query('position');

        $query = Player::with(['team', 'profile', 'sanctions' => fn ($q) => $q->where('status', 'active')])->latest('id');

        if ($search) {
            $cleanSearch = preg_replace('/[^0-9]/', '', $search);
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('name', 'like', "%{$search}%");
                if ($cleanSearch) {
                    $q->orWhere('id', (int) $cleanSearch);
                }
            });
        }

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        if ($position) {
            $posMap = [
                'FW' => 'forward',
                'MF' => 'midfielder',
                'DF' => 'defender',
                'GK' => 'goalkeeper',
                'forward' => 'forward',
                'midfielder' => 'midfielder',
                'defender' => 'defender',
                'goalkeeper' => 'goalkeeper',
            ];
            $mapped = $posMap[strtoupper($position)] ?? ($posMap[strtolower($position)] ?? $position);
            $query->whereHas('profile', function ($q) use ($position, $mapped) {
                $q->whereIn('position', array_unique([$position, $mapped]));
            });
        }

        $players = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="players_export_'.now()->format('Y-m-d').'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($players) {
            $handle = fopen('php://output', 'w');
            // Byte Order Mark for Excel UTF-8 compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Código ID', 'Nombre del Jugador', 'Club / Equipo', 'Dorsal', 'Posición', 'Estado Deportivo', 'Documento']);

            foreach ($players as $p) {
                $isSuspended = $p->sanctions->isNotEmpty();
                $posLabel = match ($p->profile?->position) {
                    'goalkeeper' => 'Portero',
                    'defender' => 'Defensa',
                    'midfielder' => 'Mediocampista',
                    'forward' => 'Delantero',
                    default => ($p->profile?->position ?? 'Delantero'),
                };

                fputcsv($handle, [
                    'PLY-'.str_pad($p->id, 3, '0', STR_PAD_LEFT),
                    $p->name,
                    $p->team?->name ?? 'Agente Libre',
                    $p->jersey_number ?? '-',
                    $posLabel,
                    $isSuspended ? 'SUSPENDIDO' : 'ACTIVO',
                    $p->identification_document ?? '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'jersey_number' => ['nullable', 'integer', 'min:1', 'max:99'],
            'position' => ['nullable', 'string'],
            'preferred_foot' => ['nullable', 'string', 'in:right,left,ambidextrous'],
            'identification_document' => ['nullable', 'string', 'max:50'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'scouting_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $posMap = [
            'FW' => 'forward',
            'MF' => 'midfielder',
            'DF' => 'defender',
            'GK' => 'goalkeeper',
            'forward' => 'forward',
            'midfielder' => 'midfielder',
            'defender' => 'defender',
            'goalkeeper' => 'goalkeeper',
        ];

        $normalizedPos = $posMap[$data['position'] ?? ''] ?? 'forward';

        $player = Player::create([
            'name' => $data['name'],
            'team_id' => ! empty($data['team_id']) ? $data['team_id'] : null,
            'jersey_number' => $data['jersey_number'] ?? null,
            'identification_document' => $data['identification_document'] ?? ('ID-'.rand(10000, 99999)),
        ]);

        $isFreeAgent = empty($data['team_id']);

        $player->profile()->updateOrCreate(
            ['player_id' => $player->id],
            [
                'position' => $normalizedPos,
                'preferred_foot' => $data['preferred_foot'] ?? 'right',
                'is_free_agent' => $isFreeAgent,
                'performance_rating' => 7.5,
                'contact_phone' => $data['contact_phone'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'scouting_notes' => $data['scouting_notes'] ?? null,
                'qr_token' => Str::uuid()->toString(),
            ]
        );

        if ($isFreeAgent) {
            return redirect()->route('admin.scouting.index')->with('status', "Jugador {$player->name} registrado exitosamente en la Bolsa de Talentos como Agente Libre.");
        }

        return redirect()->route('players.index')->with('status', "Jugador {$player->name} registrado exitosamente.");
    }

    public function destroy(Player $player): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user && ($user->isSuperAdmin() || $user->isAdmin()), 403);

        if ($user->isAdmin()) {
            $adminTournamentIds = Tournament::where('admin_id', $user->id)->pluck('id');
            abort_unless(! $player->team_id || $adminTournamentIds->contains($player->team?->tournament_id), 403, 'No tienes autorización para eliminar jugadores fuera de tu torneo.');
        }

        $playerName = $player->name;
        $player->delete();

        return redirect()->route('players.index')->with('status', "Jugador {$playerName} eliminado exitosamente.");
    }
}
