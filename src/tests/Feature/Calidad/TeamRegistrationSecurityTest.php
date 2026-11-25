<?php

namespace Tests\Feature\Calidad;

use App\Models\User;
use App\Models\v1\Player;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Guía 11 – Pruebas del registro de equipos y de las redirecciones seguras.
 * Componentes intervenidos: TeamController::store, TeamDirectorAssignmentService,
 * Controller::redirectToInternalOr() y PlayerProfileController (Mejora 5).
 */
class TeamRegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private Tournament $tournament;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->tournament = Tournament::factory()->create();
    }

    /** CP-05: el registro con un DT nuevo crea el equipo aprobado y la cuenta del DT vinculada. */
    public function test_registering_team_with_new_coach_creates_linked_coach_account(): void
    {
        $this->actingAs($this->superAdmin)->post(route('teams.store'), [
            'tournament_id' => $this->tournament->id,
            'name' => 'Atlético Prados',
            'dt_mode' => 'new',
            'coach_name' => 'Carlos Pérez',
            'coach_email' => 'CARLOS@siget.test ',
            'coach_password' => 'clave-segura-2026',
        ])->assertSessionHasNoErrors();

        $coach = User::where('email', 'carlos@siget.test')->firstOrFail();
        $team = Team::where('name', 'Atlético Prados')->firstOrFail();

        $this->assertSame('captain', $coach->role);
        $this->assertSame($coach->id, $team->captain_id);
        $this->assertSame('approved', $team->status);
        $this->assertTrue(Hash::check('clave-segura-2026', $coach->password));
        $this->assertDatabaseHas('team_invitations', ['team_id' => $team->id, 'recipient_email' => 'carlos@siget.test']);
    }

    /**
     * CP-06 (seguridad): si no se envía clave, la cuenta del DT no debe quedar con la clave
     * predecible "password". Antes de la refactorización esta prueba fallaba.
     */
    public function test_new_coach_without_password_does_not_receive_predictable_password(): void
    {
        $this->actingAs($this->superAdmin)->post(route('teams.store'), [
            'tournament_id' => $this->tournament->id,
            'name' => 'Deportivo Sin Clave',
            'dt_mode' => 'new',
            'coach_name' => 'Laura Gómez',
            'coach_email' => 'laura@siget.test',
        ]);

        $coach = User::where('email', 'laura@siget.test')->firstOrFail();

        $this->assertFalse(Hash::check('password', $coach->password));
    }

    /**
     * CP-07 (seguridad – Open Redirect): una URL externa en redirect_to debe ignorarse.
     * Antes de la refactorización el sistema redirigía al dominio externo y esta prueba fallaba.
     */
    public function test_external_redirect_target_is_ignored_when_registering_team(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('teams.store'), [
            'tournament_id' => $this->tournament->id,
            'name' => 'Club Redirección',
            'dt_mode' => 'none',
            'redirect_to' => 'https://sitio-malicioso.example/phishing',
        ]);

        $team = Team::where('name', 'Club Redirección')->firstOrFail();
        $response->assertRedirect(route('teams.show', $team));
    }

    /** CP-08: una ruta interna en redirect_to se sigue respetando (comportamiento conservado). */
    public function test_internal_redirect_target_is_preserved(): void
    {
        $internal = route('tournaments.show', $this->tournament);

        $this->actingAs($this->superAdmin)->post(route('teams.store'), [
            'tournament_id' => $this->tournament->id,
            'name' => 'Club Interno',
            'dt_mode' => 'none',
            'redirect_to' => $internal,
        ])->assertRedirect($internal);
    }

    /** CP-09 (seguridad – Open Redirect): la ficha deportiva del jugador tampoco redirige fuera del sitio. */
    public function test_player_profile_update_ignores_external_redirect_target(): void
    {
        $player = Player::factory()->create();

        $this->actingAs($this->superAdmin)->put(route('players.profile.update', $player), [
            'position' => 'forward',
            'preferred_foot' => 'right',
            'redirect_to' => '//sitio-malicioso.example',
        ])->assertRedirect(route('players.cromo', $player));
    }
}
