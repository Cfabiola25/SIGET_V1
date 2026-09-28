<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Player;
use App\Services\v1\QrCarnetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayerProfileController extends Controller
{
    public function showCromo(Player $player): View
    {
        $player->load(['team.tournament', 'profile', 'events', 'lineups']);

        $stats = [
            'matches' => $player->lineups()->count(),
            'goals' => $player->goalsCount(),
            'yellow_cards' => $player->yellowCardsCount(),
            'red_cards' => $player->redCardsCount(),
            'mvps' => $player->profile?->mvp_count ?? 0,
        ];

        return view('v1.players.cromo', compact('player', 'stats'));
    }

    public function showCarnet(Player $player, QrCarnetService $qrService): View
    {
        $player->load(['team.tournament', 'profile', 'medicalRecord']);

        $qrSvg = $qrService->renderSvg($player->profile->qr_payload, 240);

        return view('v1.players.carnet-qr', compact('player', 'qrSvg'));
    }

    public function editMedical(Player $player): View
    {
        $this->authorizePlayerManager($player);

        $medical = $player->medicalRecord ?? $player->medicalRecord()->create([
            'blood_type' => 'O+',
            'waiver_signed' => false,
            'is_medically_cleared' => true,
        ]);

        return view('v1.players.medical', compact('player', 'medical'));
    }

    public function updateMedical(Request $request, Player $player): RedirectResponse
    {
        $this->authorizePlayerManager($player);

        $validated = $request->validate([
            'blood_type' => ['required', 'string', 'in:O+,O-,A+,A-,B+,B-,AB+,AB-'],
            'health_provider' => ['required', 'string', 'max:100'],
            'allergies' => ['nullable', 'string', 'max:500'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:20'],
            'waiver_signed' => ['required', 'boolean'],
        ]);

        $medical = $player->medicalRecord()->updateOrCreate(
            ['player_id' => $player->id],
            [
                ...$validated,
                'waiver_signed_at' => $validated['waiver_signed'] ? now() : null,
                'is_medically_cleared' => true,
            ]
        );

        return redirect()->route('players.cromo', $player)
            ->with('status', 'Ficha médica y exoneración de responsabilidad actualizadas exitosamente.');
    }

    public function editProfile(Player $player): View
    {
        $this->authorizePlayerManager($player);

        return view('v1.players.edit-profile', compact('player'));
    }

    public function updateProfile(Request $request, Player $player): RedirectResponse
    {
        $this->authorizePlayerManager($player);

        $validated = $request->validate([
            'position' => ['required', 'in:goalkeeper,defender,midfielder,forward'],
            'preferred_foot' => ['required', 'in:right,left,ambidextrous'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'height_cm' => ['nullable', 'integer', 'between:100,230'],
            'weight_kg' => ['nullable', 'integer', 'between:30,150'],
        ]);

        $player->profile()->updateOrCreate(
            ['player_id' => $player->id],
            $validated
        );

        return redirect()->route('players.cromo', $player)
            ->with('status', 'Perfil deportivo actualizado correctamente.');
    }

    private function authorizePlayerManager(Player $player): void
    {
        $user = request()->user();
        abort_unless(
            $user && (
                $user->isSuperAdmin() ||
                ($user->isAdmin() && $player->team->tournament->admin_id === $user->id) ||
                $player->team->isManagedBy($user) ||
                $player->user_id === $user->id
            ),
            403
        );
    }
}
