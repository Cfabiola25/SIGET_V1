<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\v1\MatchGame;
use App\Models\v1\Player;
use App\Models\v1\PlayerRecruitmentRequest;
use App\Models\v1\Referee;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use App\Models\v1\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DtProfileRecruitmentAndRoleScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dt_adding_player_automatically_initializes_athletic_and_medical_profiles(): void
    {
        $tournament = Tournament::factory()->create();
        $coach = User::factory()->create(['role' => 'captain']);
        $team = Team::factory()->create([
            'tournament_id' => $tournament->id,
            'captain_id' => $coach->id,
            'name' => 'Atlético DT Club',
        ]);

        // DT agrega jugador desde su portal
        $response = $this->actingAs($coach)->post(route('dt.players.store', $team), [
            'name' => 'Mateo Silva',
            'jersey_number' => 10,
            'identification_document' => 'CC-987654321',
            'position' => 'midfielder',
            'preferred_foot' => 'right',
            'height_cm' => 178,
            'weight_kg' => 74,
        ]);

        $response->assertRedirect(route('dt.roster', $team))
            ->assertSessionHas('status');

        $player = Player::where('identification_document', 'CC-987654321')->first();
        $this->assertNotNull($player);
        $this->assertEquals($team->id, $player->team_id);

        // Perfil deportivo y ficha médica auto-creados
        $this->assertNotNull($player->profile);
        $this->assertEquals('midfielder', $player->profile->position);
        $this->assertEquals('right', $player->profile->preferred_foot);
        $this->assertNotNull($player->medicalRecord);

        // DT actualiza ficha médica con retorno al roster
        $medRes = $this->actingAs($coach)->put(route('players.medical.update', $player), [
            'blood_type' => 'O+',
            'health_provider' => 'Sura EPS',
            'emergency_contact_name' => 'Mamá Silva',
            'emergency_contact_phone' => '3009988776',
            'waiver_signed' => 1,
            'redirect_to' => route('dt.roster', $team),
        ]);

        $medRes->assertRedirect(route('dt.roster', $team));
        $this->assertTrue($player->fresh()->medicalRecord->waiver_signed);
    }

    public function test_dt_can_release_player_to_free_agency_for_other_teams_to_scout(): void
    {
        $tournament = Tournament::factory()->create();
        $coach = User::factory()->create(['role' => 'captain']);
        $team = Team::factory()->create([
            'tournament_id' => $tournament->id,
            'captain_id' => $coach->id,
        ]);

        $player = Player::factory()->create([
            'team_id' => $team->id,
            'name' => 'Carlos Valderrama',
        ]);

        $this->assertFalse((bool) $player->profile->is_free_agent);

        // DT libera al jugador
        $releaseRes = $this->actingAs($coach)->post(route('dt.players.release', [$team, $player]));
        $releaseRes->assertRedirect(route('dt.roster', $team))
            ->assertSessionHas('status');

        $player->refresh();
        $this->assertNull($player->team_id);
        $this->assertTrue((bool) $player->profile->is_free_agent);

        // Aparece en el radar de scouting público
        $scoutingRes = $this->get(route('scouting.index'));
        $scoutingRes->assertOk()->assertSee('Carlos Valderrama');
    }

    public function test_super_admin_can_register_free_agent_with_contact_info(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)->post(route('players.store'), [
            'name' => 'Luis Díaz Agente Libre',
            'position' => 'FW',
            'preferred_foot' => 'right',
            'contact_phone' => '+57 311 555 4433',
            'contact_email' => 'luis.diaz@promesas.co',
            'scouting_notes' => 'Extremo desequilibrante con gran cambio de ritmo.',
        ]);

        $response->assertRedirect(route('admin.scouting.index'))
            ->assertSessionHas('status');

        $player = Player::where('name', 'Luis Díaz Agente Libre')->first();
        $this->assertNotNull($player);
        $this->assertNull($player->team_id);
        $this->assertTrue((bool) $player->profile->is_free_agent);
        $this->assertEquals('+57 311 555 4433', $player->profile->contact_phone);
        $this->assertEquals('luis.diaz@promesas.co', $player->profile->contact_email);
    }

    public function test_recruitment_flow_requires_proposal_and_acceptance_before_joining_team(): void
    {
        $tournament = Tournament::factory()->create();
        $coach = User::factory()->create(['role' => 'captain']);
        $team = Team::factory()->create([
            'tournament_id' => $tournament->id,
            'captain_id' => $coach->id,
            'name' => 'Deportivo Independiente',
        ]);

        $freeAgent = Player::factory()->create(['team_id' => null, 'name' => 'Talento Solitario']);
        $freeAgent->profile()->update([
            'is_free_agent' => true,
            'contact_phone' => '3209876543',
            'contact_email' => 'talento@libre.com',
        ]);

        // DT envía solicitud formal de fichaje
        $requestRes = $this->actingAs($coach)->post(route('scouting.recruit.request', $freeAgent), [
            'team_id' => $team->id,
            'proposed_jersey_number' => 7,
            'contact_phone' => '3001122334',
            'contact_email' => 'dt@independiente.com',
            'message' => 'Te ofrecemos ser titular en la delantera.',
        ]);

        $requestRes->assertRedirect(route('scouting.requests.index'))
            ->assertSessionHas('status');

        // El jugador NO debe ser fichado inmediatamente (espera aceptación)
        $freeAgent->refresh();
        $this->assertNull($freeAgent->team_id);
        $this->assertTrue((bool) $freeAgent->profile->is_free_agent);

        $recRequest = PlayerRecruitmentRequest::where('player_id', $freeAgent->id)
            ->where('team_id', $team->id)
            ->first();

        $this->assertNotNull($recRequest);
        $this->assertTrue($recRequest->isPending());

        // Administrador o Super Admin acepta formalmente la solicitud
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $acceptRes = $this->actingAs($superAdmin)->post(route('scouting.requests.accept', $recRequest));

        $acceptRes->assertRedirect(route('scouting.requests.index'))
            ->assertSessionHas('status');

        // Ahora el jugador SI está incorporado en el equipo
        $freeAgent->refresh();
        $this->assertEquals($team->id, $freeAgent->team_id);
        $this->assertEquals(7, $freeAgent->jersey_number);
        $this->assertFalse((bool) $freeAgent->profile->is_free_agent);
    }

    public function test_dt_has_strict_role_based_isolation_and_cannot_access_backoffice_admin_routes(): void
    {
        $coach = User::factory()->create(['role' => 'captain']);
        $team = Team::factory()->create(['captain_id' => $coach->id]);

        // No puede crear torneos
        $this->actingAs($coach)->get(route('tournaments.create'))->assertForbidden();

        // No puede ver gestión backoffice de partidos
        $this->actingAs($coach)->get(route('admin.matches.index'))->assertForbidden();

        // No puede programar partidos
        $this->actingAs($coach)->get(route('matches.schedule'))->assertForbidden();

        // No puede administrar sedes
        $this->actingAs($coach)->get(route('venues.index'))->assertForbidden();

        // No puede gestionar padrón de árbitros
        $this->actingAs($coach)->get(route('referees.index'))->assertForbidden();

        // No puede ver auditoría del sistema
        $this->actingAs($coach)->get(route('auditing.index'))->assertForbidden();
    }

    public function test_admin_is_strictly_scoped_to_their_managed_tournaments(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);

        $tournament1 = Tournament::factory()->create(['admin_id' => $admin1->id]);
        $tournament2 = Tournament::factory()->create(['admin_id' => $admin2->id]);

        $teamT2_A = Team::factory()->create(['tournament_id' => $tournament2->id]);
        $teamT2_B = Team::factory()->create(['tournament_id' => $tournament2->id]);
        $venue = Venue::create([
            'name' => 'Cancha La Salle',
            'field_count' => 2,
            'surface_type' => 'grass',
            'is_active' => true,
        ]);

        // Admin 1 no puede ver el torneo de Admin 2
        $this->actingAs($admin1)->get(route('tournaments.show', $tournament2))->assertForbidden();

        // Admin 1 no puede programar partidos con equipos del torneo 2
        $scheduleRes = $this->actingAs($admin1)->post(route('matches.schedule.store'), [
            'home_team_id' => $teamT2_A->id,
            'away_team_id' => $teamT2_B->id,
            'date' => now()->format('Y-m-d'),
            'time' => '14:00',
            'venue_id' => $venue->id,
            'field_number' => 1,
        ]);

        $scheduleRes->assertForbidden();
    }

    public function test_dt_can_see_assigned_fixture_with_venue_referee_opponent_and_lineup_action(): void
    {
        $tournament = Tournament::factory()->create(['name' => 'Copa Élite 2026']);
        $coach = User::factory()->create(['role' => 'captain', 'name' => 'Profesor Pekerman']);
        $team = Team::factory()->create([
            'tournament_id' => $tournament->id,
            'captain_id' => $coach->id,
            'name' => 'Club Deportivo Halcones',
        ]);
        $opponent = Team::factory()->create([
            'tournament_id' => $tournament->id,
            'name' => 'Huracán FC',
        ]);
        $venue = Venue::create([
            'name' => 'Complejo Deportivo El Campín',
            'field_count' => 3,
            'surface_type' => 'grass',
            'is_active' => true,
        ]);
        $referee = Referee::create([
            'name' => 'Wilmar Roldán',
            'badge_level' => 'fifa',
            'is_active' => true,
        ]);

        $match = MatchGame::create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $team->id,
            'away_team_id' => $opponent->id,
            'match_date' => now()->addDays(2)->setHour(16)->setMinute(30),
            'venue_id' => $venue->id,
            'field_number' => 2,
            'referee_id' => $referee->id,
            'status' => 'scheduled',
            'round_number' => 1,
        ]);

        // El DT entra a su dashboard
        $res = $this->actingAs($coach)->get(route('dt.dashboard', $team));

        $res->assertOk();
        $res->assertSee('Club Deportivo Halcones');
        $res->assertSee('Huracán FC');
        $res->assertSee('Complejo Deportivo El Campín');
        $res->assertSee('Cancha #2');
        $res->assertSee('Wilmar Roldán');
        $res->assertSee('16:30');
        // Enlace para nominar / alinear a sus jugadores
        $res->assertSee(route('matches.lineup.edit', ['match' => $match->id, 'team' => $team->id]));
    }

    public function test_players_are_strictly_forbidden_from_creating_or_storing_players(): void
    {
        $tournament = Tournament::factory()->create();
        $team = Team::factory()->create(['tournament_id' => $tournament->id]);
        $playerUser = User::factory()->create(['role' => 'player', 'name' => 'Juancho Futbolero']);

        // Jugador intenta crear otro jugador por POST
        $res = $this->actingAs($playerUser)->post(route('players.store'), [
            'name' => 'Nuevo Jugador Ilegal',
            'team_id' => $team->id,
            'jersey_number' => 9,
            'identification_document' => 'CC-112233',
        ]);

        $res->assertForbidden();

        // Jugador que visita /players es redirigido a su panel
        $indexRes = $this->actingAs($playerUser)->get(route('players.index'));
        $indexRes->assertRedirect(route('dashboard'));

        // Jugador no puede exportar CSV de jugadores
        $exportRes = $this->actingAs($playerUser)->get(route('players.export'));
        $exportRes->assertForbidden();
    }

    public function test_admin_cannot_create_matches_outside_their_assigned_tournament_in_store(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);

        $tournament1 = Tournament::factory()->create(['admin_id' => $admin1->id]);
        $tournament2 = Tournament::factory()->create(['admin_id' => $admin2->id]);

        $teamT2_A = Team::factory()->create(['tournament_id' => $tournament2->id]);
        $teamT2_B = Team::factory()->create(['tournament_id' => $tournament2->id]);

        // Admin 1 intenta crear partido en el torneo de Admin 2 usando StoreMatchRequest
        $res = $this->actingAs($admin1)->post(route('matches.store'), [
            'tournament_id' => $tournament2->id,
            'home_team_id' => $teamT2_A->id,
            'away_team_id' => $teamT2_B->id,
            'match_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ]);

        $res->assertForbidden();
    }

    public function test_player_dashboard_shows_strictly_player_identity_carnet_and_team_matches(): void
    {
        $tournament = Tournament::factory()->create(['name' => 'Liga Premier Juvenil']);
        $team = Team::factory()->create(['tournament_id' => $tournament->id, 'name' => 'Estrellas FC']);
        $playerUser = User::factory()->create(['role' => 'player', 'name' => 'Santiago Giraldo']);

        $player = Player::factory()->create([
            'user_id' => $playerUser->id,
            'team_id' => $team->id,
            'name' => 'Santiago Giraldo',
            'jersey_number' => 10,
            'identification_document' => 'CC-1098765432',
        ]);

        $player->profile()->update([
            'position' => 'midfielder',
        ]);

        $res = $this->actingAs($playerUser)->get(route('dashboard'));

        $res->assertOk();
        $res->assertSee('Santiago Giraldo');
        $res->assertSee('#10');
        $res->assertSee('Estrellas FC');
        $res->assertSee('Liga Premier Juvenil');
        $res->assertSee(route('players.carnet', $player));
        $res->assertSee(route('players.cromo', $player));
        // No debe mostrar accesos administrativos como Agendador o Crear Torneo
        $res->assertDontSee('Agendador de Partidos');
        $res->assertDontSee('Crear Torneo');
    }
}
