<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\v1\Team;
use App\Models\v1\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TeamInvitationController extends Controller
{
    public function store(Request $request, Team $team): RedirectResponse
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->isSuperAdmin() || ($user->isAdmin() && $team->tournament->admin_id === $user->id)),
            403
        );

        $validated = $request->validate([
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'recipient_phone' => ['nullable', 'string', 'max:20'],
            'days_valid' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $days = (int) ($validated['days_valid'] ?? 7);

        $invitation = TeamInvitation::createForTeam(
            team: $team,
            invitedBy: $user,
            recipientName: $validated['recipient_name'] ?? null,
            recipientEmail: $validated['recipient_email'] ?? null,
            recipientPhone: $validated['recipient_phone'] ?? null,
            daysValid: $days
        );

        return back()->with('invitation_created', [
            'token' => $invitation->token,
            'claim_url' => $invitation->getClaimUrl(),
            'whatsapp_url' => $invitation->getWhatsAppShareUrl(),
            'recipient_name' => $invitation->recipient_name,
            'recipient_phone' => $invitation->recipient_phone,
            'expires_at' => $invitation->expires_at->toFormattedDateString(),
        ]);
    }

    public function showClaim(string $token): View
    {
        $invitation = TeamInvitation::with(['team.tournament', 'invitedBy'])
            ->where('token', $token)
            ->firstOrFail();

        return view('v1.teams.claim', compact('invitation'));
    }

    public function claim(Request $request, string $token): RedirectResponse
    {
        $invitation = TeamInvitation::with(['team.tournament'])
            ->where('token', $token)
            ->firstOrFail();

        if ($invitation->isAccepted()) {
            return redirect()->route('login')->withErrors(['invitation' => 'Esta invitación ya fue utilizada anteriormente.']);
        }

        if ($invitation->isExpired()) {
            return redirect()->route('login')->withErrors(['invitation' => 'Esta invitación ha expirado. Contacta al administrador del torneo.']);
        }

        $team = $invitation->team;

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'player') {
                $user->update(['role' => 'captain']);
            }
        } else {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::defaults()],
                'phone' => ['nullable', 'string', 'max:20'],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'captain',
                'is_active' => true,
            ]);

            Auth::login($user);
        }

        $team->update([
            'captain_id' => $user->id,
            'status' => 'approved',
        ]);

        $invitation->update([
            'accepted_at' => now(),
            'claimed_by_user_id' => $user->id,
        ]);

        return redirect()->route('dt.dashboard', $team)
            ->with('status', "¡Bienvenido, Director Técnico! Ahora tienes el control de {$team->name}.");
    }
}
