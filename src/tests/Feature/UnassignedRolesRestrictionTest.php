<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use App\Models\v1\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnassignedRolesRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_unassigned_admin_can_login_and_access_dashboard_with_waiting_screen(): void
    {
        $admin = User::factory()->create([
            'name' => 'Carlos Administrador',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('En Espera de Asignación');
        $response->assertSee('Carlos Administrador');
        $response->assertSee('0 Torneos');
        $response->assertSee('Sin Torneo Asignado');
        $response->assertSee('Módulos en Espera');
        $response->assertDontSee('Nuevo Torneo');
        $response->assertDontSee('Gestión de Partidos');
    }

    public function test_unassigned_admin_cannot_create_tournaments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('tournaments.create'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('tournaments.store'), [
                'name' => 'Torneo No Autorizado',
                'sport_type' => 'Fútbol 11',
            ])
            ->assertForbidden();
    }

    public function test_unassigned_admin_cannot_access_backoffice_or_schedule_matches(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // No puede programar partidos
        $this->actingAs($admin)
            ->get(route('matches.schedule'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->post(route('matches.schedule.store'), [
                'home_team_id' => 1,
                'away_team_id' => 2,
                'date' => '2026-10-15',
                'time' => '10:00',
            ])
            ->assertForbidden();

        // No puede ver gestión backoffice
        $this->actingAs($admin)
            ->get(route('admin.matches.index'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('admin.teams.index'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('admin.standings.index'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('admin.scouting.index'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('tournaments.index'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('venues.index'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->post(route('venues.store'), [
                'name' => 'Cancha no permitida',
                'field_count' => 1,
                'surface_type' => 'grass',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('referees.store'), [
                'name' => 'Árbitro no permitido',
                'license_number' => 'REF-999',
            ])
            ->assertForbidden();
    }

    public function test_assigned_admin_can_access_full_admin_dashboard_and_features(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);

        $tournament = Tournament::factory()->create([
            'name' => 'Copa de Oro Federativa',
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Copa de Oro Federativa');
        $response->assertSee('Mis Torneos Asignados');
        $response->assertSee('Mis Torneos');
        $response->assertSee('Gestión de Partidos');
        $response->assertDontSee('Módulos en Espera');
    }

    public function test_unassigned_dt_can_login_and_sees_waiting_screen(): void
    {
        $dt = User::factory()->create([
            'name' => 'Marcelo Gallardo',
            'role' => 'captain',
        ]);

        $response = $this->actingAs($dt)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Sin Equipo Asignado');
        $response->assertSee('Marcelo Gallardo');
        $response->assertSee('0 Equipos');
        $response->assertSee('Sin Equipo Vinculado');
        $response->assertDontSee('Gestionar Mi Plantilla');
        $response->assertDontSee('Mi Plantilla de Jugadores');
        $response->assertDontSee('Mis Solicitudes de Fichaje');
    }

    public function test_unassigned_dt_cannot_manage_players_or_rosters(): void
    {
        $dt = User::factory()->create(['role' => 'captain']);

        $this->actingAs($dt)
            ->get(route('players.index'))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($dt)
            ->post(route('players.store'), [
                'name' => 'Jugador Falso',
            ])
            ->assertForbidden();

        $this->actingAs($dt)
            ->get(route('scouting.requests.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_assigned_dt_redirects_to_dt_dashboard(): void
    {
        $dt = User::factory()->create(['role' => 'captain']);
        $team = Team::factory()->create([
            'name' => 'Club Atlético River',
            'captain_id' => $dt->id,
        ]);

        $response = $this->actingAs($dt)->get(route('dashboard'));

        $response->assertRedirect(route('dt.dashboard', $team));
    }
}
