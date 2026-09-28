<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Team;
use App\Models\v1\TeamInvitation;
use App\Models\v1\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        $query = Team::with(['tournament', 'captain'])->latest();

        if (auth()->user()?->isAdmin()) {
            $query->whereHas('tournament', fn ($tournaments) => $tournaments->where('admin_id', auth()->id()));
        }

        return view('v1.teams.index', ['teams' => $query->get()]);
    }

    public function create(): View
    {
        abort_unless(in_array(auth()->user()?->role, ['super_admin', 'admin', 'captain'], true), 403);

        $query = Tournament::whereIn('status', ['pending', 'active']);

        if (auth()->user()?->isAdmin()) {
            $query->where('admin_id', auth()->id());
        }

        return view('v1.teams.create', ['tournaments' => $query->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['super_admin', 'admin', 'captain'], true), 403);

        $data = $request->validate([
            'tournament_id' => ['required', 'exists:tournaments,id'],
            'name' => ['required', 'string', 'max:255'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'captain_id' => ['nullable', 'exists:users,id'],
            'coach_name' => ['nullable', 'string', 'max:255'],
            'coach_email' => ['nullable', 'email', 'max:255'],
            'coach_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $tournament = Tournament::findOrFail($data['tournament_id']);
        if ($request->user()->isAdmin()) {
            abort_unless($tournament->admin_id === $request->user()->id, 403);
            $team = Team::create([
                'tournament_id' => $data['tournament_id'],
                'name' => $data['name'],
                'logo_path' => $data['logo_path'] ?? null,
                'captain_id' => $data['captain_id'] ?? null,
                'status' => 'approved',
            ]);
        } elseif ($request->user()->isSuperAdmin()) {
            $team = Team::create([
                'tournament_id' => $data['tournament_id'],
                'name' => $data['name'],
                'logo_path' => $data['logo_path'] ?? null,
                'captain_id' => $data['captain_id'] ?? null,
                'status' => 'approved',
            ]);
        } else {
            $team = $request->user()->captainedTeams()->create([
                'tournament_id' => $data['tournament_id'],
                'name' => $data['name'],
                'logo_path' => $data['logo_path'] ?? null,
                'status' => 'pending',
            ]);
        }

        if (! $team->captain_id && in_array($request->user()->role, ['super_admin', 'admin'], true)) {
            $invitation = TeamInvitation::createForTeam(
                team: $team,
                invitedBy: $request->user(),
                recipientName: $data['coach_name'] ?? null,
                recipientEmail: $data['coach_email'] ?? null,
                recipientPhone: $data['coach_phone'] ?? null,
            );

            return redirect()->route('teams.show', $team)->with('invitation_created', [
                'token' => $invitation->token,
                'claim_url' => $invitation->getClaimUrl(),
                'whatsapp_url' => $invitation->getWhatsAppShareUrl(),
                'recipient_name' => $invitation->recipient_name,
                'recipient_phone' => $invitation->recipient_phone,
            ]);
        }

        return redirect()->route('teams.show', $team);
    }

    public function show(Team $team): View
    {
        $this->authorizeTeam($team);

        return view('v1.teams.show', ['team' => $team->load(['tournament', 'captain', 'players'])]);
    }

    public function edit(Team $team): View
    {
        $this->authorizeTeam($team);

        return view('v1.teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $this->authorizeTeam($team);
        $team->update($request->validate(['name' => ['required', 'string', 'max:255'], 'logo_path' => ['nullable', 'string', 'max:255'], 'status' => ['sometimes', 'in:pending,approved']]));

        return redirect()->route('teams.show', $team);
    }

    public function destroy(Team $team): RedirectResponse
    {
        $this->authorizeTeam($team);
        $team->delete();

        return redirect()->route('teams.index');
    }

    private function authorizeTeam(Team $team): void
    {
        $user = request()->user();
        abort_unless($user && ($user->isSuperAdmin() || ($user->isAdmin() && $team->tournament->admin_id === $user->id) || ($user->isCaptain() && $team->captain_id === $user->id)), 403);
    }
}
