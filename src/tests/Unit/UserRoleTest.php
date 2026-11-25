<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Guía 11 – Prueba unitaria de las reglas de rol centralizadas en User (Mejora 3).
 * No usa base de datos: valida únicamente la lógica del modelo.
 */
class UserRoleTest extends TestCase
{
    /** @return array<string, array{string, bool, bool, bool}> rol, isStaff, canRegisterTeams, isDT */
    public static function roles(): array
    {
        return [
            'super_admin' => ['super_admin', true, true, false],
            'admin' => ['admin', true, true, false],
            'captain' => ['captain', false, true, true],
            'coach' => ['coach', false, false, true],
            'referee' => ['referee', false, false, false],
            'player' => ['player', false, false, false],
        ];
    }

    /** CP-13: cada rol obtiene exactamente los permisos esperados. */
    #[DataProvider('roles')]
    public function test_role_permissions_are_resolved_from_a_single_source(string $role, bool $staff, bool $registersTeams, bool $dt): void
    {
        $user = new User(['role' => $role]);

        $this->assertSame($staff, $user->isStaff());
        $this->assertSame($registersTeams, $user->canRegisterTeams());
        $this->assertSame($dt, $user->isDT());
        $this->assertSame($dt, $user->isCaptain());
        $this->assertSame($dt, $user->isCoach());
    }
}
