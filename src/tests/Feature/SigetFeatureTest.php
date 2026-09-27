<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\v1\MatchGame;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SigetFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    #[DataProvider('accountRoles')]
    public function test_users_of_each_role_can_sign_in(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->post(route('login.authenticate'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirectToRoute('dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public static function accountRoles(): array
    {
        return [
            'super admin' => ['super_admin'],
            'admin' => ['admin'],
            'captain' => ['captain'],
            'player' => ['player'],
        ];
    }

    public function test_role_dashboards_are_isolated(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($superAdmin)->get('/super-admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/super-admin/dashboard')->assertForbidden();
        $this->actingAs($superAdmin)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_only_sees_assigned_tournaments(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $adminOne = User::factory()->create(['role' => 'admin']);
        $adminTwo = User::factory()->create(['role' => 'admin']);

        Tournament::factory()->create([
            'name' => 'Torneo visible',
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $adminOne->id,
        ]);
        Tournament::factory()->create([
            'name' => 'Torneo privado',
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $adminTwo->id,
        ]);

        $this->actingAs($adminOne)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Torneo visible')
            ->assertDontSee('Torneo privado');
    }

    public function test_admin_cannot_open_another_admins_tournament(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $adminOne = User::factory()->create(['role' => 'admin']);
        $adminTwo = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create([
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $adminTwo->id,
        ]);

        $this->actingAs($adminOne)
            ->get(route('tournaments.show', $tournament))
            ->assertForbidden();
    }

    public function test_admin_cannot_open_resources_from_another_tournament(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $adminOne = User::factory()->create(['role' => 'admin']);
        $adminTwo = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create([
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $adminTwo->id,
        ]);
        $captain = User::factory()->create(['role' => 'captain']);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'captain_id' => $captain->id]);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'captain_id' => $captain->id]);
        $match = MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
        ]);

        $this->actingAs($adminOne)->get(route('teams.show', $homeTeam))->assertForbidden();
        $this->actingAs($adminOne)->get(route('matches.show', $match))->assertForbidden();
    }

    public function test_super_admin_creates_admins_and_assigns_tournaments(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($superAdmin)
            ->post(route('super-admin.admins.store'), [
                'name' => 'Nuevo Admin',
                'email' => 'new-admin@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('super-admin.dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'new-admin@example.com',
            'role' => 'admin',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('super-admin.tournaments.store'), [
                'name' => 'Torneo asignado',
                'sport_type' => 'Futbol',
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addWeek()->toDateString(),
                'admin_id' => $admin->id,
            ])
            ->assertRedirect(route('super-admin.dashboard'));

        $this->assertDatabaseHas('tournaments', [
            'name' => 'Torneo asignado',
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $admin->id,
        ]);
    }

    public function test_team_cannot_be_scheduled_twice_on_the_same_day(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create([
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $admin->id,
        ]);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id]);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id]);
        $nextOpponent = Team::factory()->create(['tournament_id' => $tournament->id]);
        $matchDate = now()->addDays(2)->setTime(10, 0);

        MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'match_date' => $matchDate,
        ]);

        $this->actingAs($admin)
            ->post(route('matches.store'), [
                'tournament_id' => $tournament->id,
                'home_team_id' => $homeTeam->id,
                'away_team_id' => $nextOpponent->id,
                'match_date' => $matchDate->copy()->setTime(16, 0)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('home_team_id');

        $this->assertDatabaseCount('match_games', 1);
    }
}
