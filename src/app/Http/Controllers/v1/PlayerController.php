<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Player;
use App\Models\v1\PlayerProfile;
use App\Models\v1\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlayerController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $search = $request->query('search');
        $teamId = $request->query('team_id');
        $position = $request->query('position');

        if ($request->query('export') === 'csv') {
            return $this->exportCsv($request);
        }

        $query = Player::with(['team', 'profile', 'sanctions' => fn($q) => $q->where('status', 'active')])
            ->latest('id');

        if ($search) {
            // Check if search looks like PLY-001 or standard string
            $cleanSearch = preg_replace('/[^0-9]/', '', $search);
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('identification_document', 'like', "%{$search}%");
                if ($cleanSearch) {
                    $q->orWhere('id', (int)$cleanSearch);
                }
            });
        }

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        if ($position) {
            $query->whereHas('profile', function ($q) use ($position) {
                $q->where('position', $position);
            });
        }

        $players = $query->paginate(10)->withQueryString();
        $teams = Team::orderBy('name')->get();

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

        $query = Player::with(['team', 'profile', 'sanctions' => fn($q) => $q->where('status', 'active')])->latest('id');

        if ($search) {
            $cleanSearch = preg_replace('/[^0-9]/', '', $search);
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('name', 'like', "%{$search}%");
                if ($cleanSearch) {
                    $q->orWhere('id', (int)$cleanSearch);
                }
            });
        }

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        if ($position) {
            $query->whereHas('profile', function ($q) use ($position) {
                $q->where('position', $position);
            });
        }

        $players = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="players_export_' . now()->format('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($players) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Player Name', 'Team', 'Number', 'Position', 'Status', 'Identification']);

            foreach ($players as $p) {
                $isSuspended = $p->sanctions->isNotEmpty();
                fputcsv($handle, [
                    'PLY-' . str_pad($p->id, 3, '0', STR_PAD_LEFT),
                    $p->name,
                    $p->team?->name ?? 'Free Agent',
                    $p->jersey_number ?? '-',
                    $p->profile?->position ?? 'FW',
                    $isSuspended ? 'SUSPENDED' : 'ACTIVE',
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
            'position' => ['nullable', 'string', 'in:FW,MF,DF,GK'],
            'identification_document' => ['nullable', 'string', 'max:50'],
        ]);

        $player = Player::create([
            'name' => $data['name'],
            'team_id' => $data['team_id'] ?? null,
            'jersey_number' => $data['jersey_number'] ?? null,
            'identification_document' => $data['identification_document'] ?? ('ID-' . rand(10000, 99999)),
        ]);

        PlayerProfile::create([
            'player_id' => $player->id,
            'position' => $data['position'] ?? 'FW',
            'is_free_agent' => empty($data['team_id']),
            'performance_rating' => 7.0,
            'qr_token' => Str::uuid()->toString(),
        ]);

        return redirect()->route('players.index')->with('status', 'Jugador registrado exitosamente.');
    }

    public function destroy(Player $player): RedirectResponse
    {
        $player->delete();

        return redirect()->route('players.index')->with('status', 'Jugador eliminado.');
    }
}
