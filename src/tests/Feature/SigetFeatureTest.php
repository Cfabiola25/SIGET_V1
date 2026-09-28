<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\v1\MatchGame;
use App\Models\v1\Sport;
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

    public function test_super_admin_can_deactivate_admin_and_deactivated_admin_cannot_sign_in(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($superAdmin)
            ->patch(route('super-admin.admins.status', $admin), ['is_active' => false])
            ->assertRedirect(route('super-admin.dashboard'));

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_active' => false]);
        $this->actingAs($admin->fresh())
            ->get(route('admin.dashboard'))
            ->assertRedirectToRoute('login');
        $this->assertGuest();

        $this->post(route('logout'))->assertRedirectToRoute('login');
        $this->post(route('login.authenticate'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    public function test_super_admin_can_edit_admin_details_and_reset_password(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($superAdmin)
            ->put(route('super-admin.admins.update', $admin), [
                'name' => 'Admin actualizado',
                'email' => 'updated-admin@example.com',
            ])
            ->assertRedirectToRoute('super-admin.dashboard');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Admin actualizado',
            'email' => 'updated-admin@example.com',
        ]);

        $this->put(route('super-admin.admins.password', $admin), [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirectToRoute('super-admin.dashboard');

        $this->post(route('logout'))->assertRedirectToRoute('login');
        $this->post(route('login.authenticate'), [
            'email' => 'updated-admin@example.com',
            'password' => 'new-password',
        ])->assertRedirectToRoute('dashboard');
        $this->assertAuthenticatedAs($admin->fresh());
    }

    public function test_super_admin_can_edit_tournament_dates(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create([
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $admin->id,
        ]);
        $startDate = now()->addDays(10)->toDateString();
        $endDate = now()->addDays(20)->toDateString();

        $this->actingAs($superAdmin)
            ->put(route('tournaments.update', $tournament), [
                'name' => $tournament->name,
                'sport_type' => $tournament->sport_type,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
            ])
            ->assertRedirectToRoute('tournaments.show', $tournament);

        $this->assertSame($startDate, $tournament->fresh()->start_date->toDateString());
        $this->assertSame($endDate, $tournament->fresh()->end_date->toDateString());
        $this->assertDatabaseHas('tournaments', ['id' => $tournament->id, 'status' => 'active']);
    }

    public function test_tournament_and_match_lists_apply_search_sport_status_and_date_filters(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);
        $startDate = now()->addDays(10)->toDateString();
        $endDate = now()->addDays(20)->toDateString();
        $matchDate = now()->addDays(12)->setTime(15, 0);
        $tournament = Tournament::factory()->create([
            'name' => 'Copa Horizonte',
            'sport_type' => 'Futbol',
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $admin->id,
        ]);
        $otherTournament = Tournament::factory()->create([
            'name' => 'Liga Alterna',
            'sport_type' => 'Baloncesto',
            'status' => 'pending',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $admin->id,
        ]);
        $homeTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Local FC']);
        $awayTeam = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Visitante FC']);
        MatchGame::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'match_date' => $matchDate,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('tournaments.index', [
                'search' => 'Copa Horizonte',
                'sport_type' => 'Futbol',
                'status' => 'active',
                'date_from' => $startDate,
                'date_to' => $endDate,
            ]))
            ->assertOk()
            ->assertSee('Copa Horizonte')
            ->assertDontSee('Liga Alterna');

        $this->get(route('tournaments.index', ['date_to' => $endDate]))->assertOk();

        $this->get(route('matches.index', [
            'search' => 'Copa Horizonte',
            'sport_type' => 'Futbol',
            'status' => 'scheduled',
            'date_from' => $matchDate->toDateString(),
            'date_to' => $matchDate->toDateString(),
        ]))
            ->assertOk()
            ->assertSee('Local FC')
            ->assertDontSee($otherTournament->name);

        $this->get(route('matches.index', ['date_from' => $matchDate->toDateString()]))->assertOk();
    }

    public function test_super_admin_can_manage_sports_without_removing_sports_used_by_tournaments(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tournamentAdmin = User::factory()->create(['role' => 'admin']);
        $sport = Sport::create(['name' => 'Rugby']);
        $tournament = Tournament::factory()->create([
            'sport_type' => $sport->name,
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $tournamentAdmin->id,
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super-admin.sports.destroy', $sport))
            ->assertSessionHasErrors('sport');
        $this->assertModelExists($sport);

        $unusedSport = Sport::create(['name' => 'Badminton']);
        $this->delete(route('super-admin.sports.destroy', $unusedSport))
            ->assertRedirectToRoute('super-admin.dashboard');
        $this->assertDatabaseMissing('sports', ['id' => $unusedSport->id]);

        $this->post(route('super-admin.sports.store'), ['name' => 'Natacion'])
            ->assertRedirectToRoute('super-admin.dashboard');
        $this->assertDatabaseHas('sports', ['name' => 'Natacion']);
        $this->assertModelExists($tournament);
    }
}
