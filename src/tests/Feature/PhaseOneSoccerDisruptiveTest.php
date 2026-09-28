<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\v1\Team;
use App\Models\v1\TeamInvitation;
use App\Models\v1\Tournament;
use App\Models\v1\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PhaseOneSoccerDisruptiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_tournament_automatically_creates_default_soccer_rules(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);

        $tournament = Tournament::create([
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $admin->id,
            'name' => 'Copa Élite Fútbol 2026',
            'sport_type' => 'Futbol',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'pending',
        ]);

        $this->assertNotNull($tournament->rules);
        $this->assertSame(2, $tournament->rules->yellow_card_limit_for_suspension);
        $this->assertSame(1, $tournament->rules->direct_red_suspension_matches);
        $this->assertSame(3, $tournament->rules->points_for_win);
        $this->assertSame(1, $tournament->rules->points_for_draw);
        $this->assertSame(90, $tournament->rules->match_duration_minutes);
        $this->assertSame(5, $tournament->rules->max_substitutions);
        $this->assertSame('goal_difference', $tournament->rules->tiebreaker_rule);
    }

    public function test_admin_can_update_tournament_competition_rules(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);

        $tournament = Tournament::create([
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $admin->id,
            'name' => 'Torneo Clausura',
            'sport_type' => 'Futbol',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('tournaments.rules.edit', $tournament))
            ->assertOk()
            ->assertSee('Reglamento Oficial');

        $this->actingAs($admin)
            ->put(route('tournaments.rules.update', $tournament), [
                'yellow_card_limit_for_suspension' => 3,
                'direct_red_suspension_matches' => 2,
                'points_for_win' => 3,
                'points_for_draw' => 1,
                'points_for_loss' => 0,
                'match_duration_minutes' => 80,
                'max_substitutions' => 6,
                'tiebreaker_rule' => 'head_to_head',
                'reset_cards_on_knockout' => 1,
                'lineup_lock_minutes_before_match' => 15,
            ])
            ->assertRedirect(route('tournaments.show', $tournament));

        $rules = $tournament->fresh()->rules;
        $this->assertSame(3, $rules->yellow_card_limit_for_suspension);
        $this->assertSame(2, $rules->direct_red_suspension_matches);
        $this->assertSame(80, $rules->match_duration_minutes);
        $this->assertSame('head_to_head', $rules->tiebreaker_rule);
        $this->assertTrue($rules->reset_cards_on_knockout);
        $this->assertSame(15, $rules->lineup_lock_minutes_before_match);
    }

    public function test_admin_can_create_venues_with_geolocation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('venues.store'), [
                'name' => 'Estadio Metropolitano',
                'address' => 'Av. Circunvalar',
                'city' => 'Barranquilla',
                'latitude' => 10.9255,
                'longitude' => -74.7997,
                'field_count' => 2,
                'surface_type' => 'grass',
                'notes' => 'Cancha principal y alterna para entrenamientos.',
            ])
            ->assertRedirect(route('venues.index'));

        $venue = Venue::where('name', 'Estadio Metropolitano')->first();
        $this->assertNotNull($venue);
        $this->assertEquals(10.9255, $venue->latitude);
        $this->assertStringContainsString('google.com/maps', $venue->navigation_url);

        $this->actingAs($admin)
            ->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Estadio Metropolitano')
            ->assertSee('Abrir en Google Maps / Waze');
    }

    public function test_admin_creates_team_and_auto_generates_magic_link_invitation(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);
        $tournament = Tournament::factory()->create([
            'super_admin_id' => $superAdmin->id,
            'admin_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('teams.store'), [
                'tournament_id' => $tournament->id,
                'name' => 'Atlético Nacional Juvenil',
                'coach_name' => 'Profe Herrera',
                'coach_phone' => '3001234567',
                'coach_email' => 'profe@ejemplo.com',
            ])
            ->assertRedirect();

        $team = Team::where('name', 'Atlético Nacional Juvenil')->first();
        $this->assertNotNull($team);
        $this->assertNull($team->captain_id);

        $invitation = TeamInvitation::where('team_id', $team->id)->first();
        $this->assertNotNull($invitation);
        $this->assertSame('Profe Herrera', $invitation->recipient_name);
        $this->assertTrue($invitation->isPending());
        $this->assertStringContainsString('/teams/invitations/', $invitation->getClaimUrl());
        $this->assertStringContainsString('api.whatsapp.com', $invitation->getWhatsAppShareUrl());
    }

    public function test_coach_can_claim_team_via_magic_link_and_access_dt_portal(): void
    {
        $tournament = Tournament::factory()->create();
        $team = Team::factory()->create([
            'tournament_id' => $tournament->id,
            'name' => 'Millonarios FC Sub 20',
            'captain_id' => null,
        ]);

        $invitation = TeamInvitation::createForTeam(
            team: $team,
            recipientName: 'Alberto Gamero',
            recipientPhone: '3109876543'
        );

        $this->get(route('teams.invitations.claim', $invitation->token))
            ->assertOk()
            ->assertSee('Millonarios FC Sub 20')
            ->assertSee('Alberto Gamero');

        $response = $this->post(route('teams.invitations.accept', $invitation->token), [
            'name' => 'Alberto Gamero',
            'email' => 'gamero@millonarios.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '3109876543',
        ]);

        $response->assertRedirect(route('dt.dashboard', $team));

        $this->assertAuthenticated();
        $coach = auth()->user();
        $this->assertTrue($coach->isCoach());
        $this->assertSame($coach->id, $team->fresh()->captain_id);
        $this->assertTrue($invitation->fresh()->isAccepted());

        $this->actingAs($coach)
            ->get(route('dt.dashboard', $team))
            ->assertOk()
            ->assertSee('Panel de Director Técnico (DT)')
            ->assertSee('Millonarios FC Sub 20');
    }

    public function test_coach_can_manage_roster_and_import_via_csv(): void
    {
        $coach = User::factory()->create(['role' => 'captain']);
        $tournament = Tournament::factory()->create();
        $team = Team::factory()->create([
            'tournament_id' => $tournament->id,
            'captain_id' => $coach->id,
        ]);

        // 1. Agregar jugador manual
        $this->actingAs($coach)
            ->post(route('dt.players.store', $team), [
                'name' => 'Radamel Falcao',
                'identification_document' => 'CC998877',
                'jersey_number' => 9,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('players', [
            'team_id' => $team->id,
            'name' => 'Radamel Falcao',
            'jersey_number' => 9,
        ]);

        // 2. Importación masiva por archivo CSV
        $csvContent = "Nombre,Documento,Dorsal\n".
                      "David Ospina,CC112233,1\n".
                      "James Rodriguez,CC445566,10\n".
                      "Luis Diaz,CC778899,7\n";

        $file = UploadedFile::fake()->createWithContent('nomina.csv', $csvContent);

        $this->actingAs($coach)
            ->post(route('dt.roster.import', $team), [
                'roster_file' => $file,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('players', 4);
        $this->assertDatabaseHas('players', ['team_id' => $team->id, 'name' => 'James Rodriguez', 'jersey_number' => 10]);
        $this->assertDatabaseHas('players', ['team_id' => $team->id, 'name' => 'David Ospina', 'jersey_number' => 1]);

        // 3. Ver nómina en la vista
        $this->actingAs($coach)
            ->get(route('dt.roster', $team))
            ->assertOk()
            ->assertSee('Radamel Falcao')
            ->assertSee('James Rodriguez')
            ->assertSee('#10');
    }
}
