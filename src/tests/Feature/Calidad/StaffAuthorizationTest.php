<?php

namespace Tests\Feature\Calidad;

use App\Models\User;
use App\Models\v1\MatchGame;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Guía 11 – Pruebas de control de acceso del personal administrativo.
 * Componentes intervenidos: User::isStaff() y los controladores que antes repetían
 * in_array($user->role, ['super_admin', 'admin'], true) (Mejora 3), y el filtro
 * reutilizable de partidos MatchGame::scopeFilter() (Mejora 4).
 */
class StaffAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public static function adminRoutes(): array
    {
        return [
            'equipos' => ['admin.teams.index'],
            'partidos' => ['admin.matches.index'],
            'posiciones' => ['admin.standings.index'],
            'auditoría' => ['auditing.index'],
            'sedes' => ['venues.index'],
            'árbitros' => ['referees.index'],
        ];
    }

    /** CP-10: un usuario que no es personal administrativo (DT) recibe 403 en el backoffice. */
    #[DataProvider('adminRoutes')]
    public function test_non_staff_user_is_forbidden_from_backoffice(string $routeName): void
    {
        $coach = User::factory()->create(['role' => 'captain']);

        $this->actingAs($coach)->get(route($routeName))->assertForbidden();
    }

    /** CP-11: el super administrador accede a todas las pantallas del backoffice. */
    #[DataProvider('adminRoutes')]
    public function test_super_admin_can_access_backoffice(string $routeName): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)->get(route($routeName))->assertOk();
    }

    /** CP-12: el listado administrativo de partidos filtra por búsqueda y estado, y solo muestra torneos propios. */
    public function test_admin_match_list_applies_filters_within_own_tournaments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $own = Tournament::factory()->create(['admin_id' => $admin->id, 'name' => 'Liga Prados']);
        $foreign = Tournament::factory()->create(['name' => 'Liga Prados Externa']);

        $tigres = Team::factory()->create(['tournament_id' => $own->id, 'name' => 'Tigres FC']);
        $leones = Team::factory()->create(['tournament_id' => $own->id, 'name' => 'Leones FC']);
        $halcones = Team::factory()->create(['tournament_id' => $own->id, 'name' => 'Halcones FC']);
        $ajeno = Team::factory()->create(['tournament_id' => $foreign->id, 'name' => 'Tigres Ajenos FC']);

        MatchGame::factory()->create(['tournament_id' => $own->id, 'home_team_id' => $tigres->id, 'away_team_id' => $leones->id, 'status' => 'played']);
        MatchGame::factory()->create(['tournament_id' => $own->id, 'home_team_id' => $halcones->id, 'away_team_id' => $leones->id, 'status' => 'played']);
        MatchGame::factory()->create(['tournament_id' => $own->id, 'home_team_id' => $tigres->id, 'away_team_id' => $halcones->id, 'status' => 'scheduled']);
        MatchGame::factory()->create(['tournament_id' => $foreign->id, 'home_team_id' => $ajeno->id, 'away_team_id' => $ajeno->id, 'status' => 'played']);

        $response = $this->actingAs($admin)
            ->get(route('admin.matches.index', ['search' => 'Tigres', 'status' => 'played']))
            ->assertOk();

        $matches = $response->viewData('matches');
        $this->assertSame(1, $matches->total());
        $this->assertSame($tigres->id, $matches->first()->home_team_id);
        $this->assertSame('played', $matches->first()->status);
    }
}
