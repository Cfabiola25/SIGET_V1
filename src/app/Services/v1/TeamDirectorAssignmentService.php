<?php

namespace App\Services\v1;

use App\Models\User;
use App\Models\v1\Team;
use App\Models\v1\TeamInvitation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Vincula el Director Técnico (DT) de un equipo recién inscrito:
 *  - existing: asigna un usuario ya registrado.
 *  - new:      crea (o reutiliza por correo) la cuenta del DT y emite la invitación.
 *  - none:     solo emite la invitación si se diligenciaron datos de contacto.
 */
class TeamDirectorAssignmentService
{
    /** Longitud de la clave aleatoria cuando el administrador no define una. */
    public const GENERATED_PASSWORD_LENGTH = 12;

    /**
     * @param  array{dt_mode?: ?string, existing_dt_id?: ?int, coach_name?: ?string, coach_email?: ?string, coach_phone?: ?string, coach_password?: ?string}  $data
     * @return string Detalle para el mensaje de confirmación ('' si no se vinculó DT).
     */
    public function assign(Team $team, User $invitedBy, array $data): string
    {
        // Sin dt_mode explícito solo se emite la invitación (comportamiento original del formulario).
        $mode = $data['dt_mode'] ?? null;

        if ($mode === 'existing' && ! empty($data['existing_dt_id'])) {
            return $this->assignExisting($team, (int) $data['existing_dt_id']);
        }

        if ($mode === 'new' && ! empty($data['coach_name']) && ! empty($data['coach_email'])) {
            return $this->assignNew($team, $invitedBy, $data);
        }

        if (! empty($data['coach_name']) || ! empty($data['coach_email'])) {
            $this->invite($team, $invitedBy, $data['coach_name'] ?? 'Director Técnico', $data['coach_email'] ?? null, $data['coach_phone'] ?? null);
        }

        return '';
    }

    private function assignExisting(Team $team, int $userId): string
    {
        $coach = User::find($userId);

        if (! $coach) {
            return '';
        }

        $team->update(['captain_id' => $coach->id]);

        return " Asignado al DT {$coach->name}.";
    }

    private function assignNew(Team $team, User $invitedBy, array $data): string
    {
        $email = Str::lower(trim($data['coach_email']));
        $coach = User::where('email', $email)->first();

        if ($coach) {
            if (! $coach->isStaff()) {
                $coach->update(['role' => User::ROLE_CAPTAIN]);
            }
            $detail = " Asignado al DT {$coach->name}.";
        } else {
            $password = ($data['coach_password'] ?? null) ?: Str::password(self::GENERATED_PASSWORD_LENGTH, symbols: false);
            $coach = User::create([
                'name' => trim($data['coach_name']),
                'email' => $email,
                'password' => Hash::make($password),
                'role' => User::ROLE_CAPTAIN,
                'is_active' => true,
            ]);
            $detail = " Se creó la cuenta de DT para {$coach->name} (Email: {$coach->email} / Clave: {$password}).";
        }

        $team->update(['captain_id' => $coach->id]);
        $this->invite($team, $invitedBy, $coach->name, $coach->email, $data['coach_phone'] ?? null);

        return $detail;
    }

    /** Invitación oficial compartible por WhatsApp. */
    private function invite(Team $team, User $invitedBy, string $name, ?string $email, ?string $phone): void
    {
        TeamInvitation::createForTeam(
            team: $team,
            invitedBy: $invitedBy,
            recipientName: $name,
            recipientEmail: $email,
            recipientPhone: $phone,
        );
    }
}
