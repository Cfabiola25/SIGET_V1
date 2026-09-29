<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\v1\DisciplinarySanction;
use App\Models\v1\MatchEvent;
use App\Models\v1\MatchGame;
use App\Models\v1\MatchLineup;
use App\Models\v1\MatchMvpVote;
use App\Models\v1\MatchSignature;
use App\Models\v1\Player;
use App\Models\v1\Referee;
use App\Models\v1\RefereeEvaluation;
use App\Models\v1\Sport;
use App\Models\v1\Team;
use App\Models\v1\Tournament;
use App\Models\v1\Venue;
use App\Services\StandingService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TournamentCompleteSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Iniciando Seeder Completo de SIGET...');

        // -------------------------------------------------------------
        // 1. SUPER ADMINISTRADOR Y DEPORTES
        // -------------------------------------------------------------
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@siget.com'],
            [
                'name' => 'Super Admin SIGET',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        $sportFutbol = Sport::firstOrCreate(['name' => 'Fútbol']);
        Sport::firstOrCreate(['name' => 'Fútbol 8']);
        Sport::firstOrCreate(['name' => 'Futsal']);

        $this->command?->info("✓ Super Admin asegurado: {$superAdmin->email}");

        // -------------------------------------------------------------
        // 2. ADMINISTRADOR DE TORNEO (CREADO POR SUPER ADMIN)
        // -------------------------------------------------------------
        $admin = User::updateOrCreate(
            ['email' => 'admin@siget.com'],
            [
                'name' => 'Carlos Mendoza (Organizador)',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // -------------------------------------------------------------
        // 3. TORNEO ASIGNADO AL ADMINISTRADOR
        // -------------------------------------------------------------
        $tournament = Tournament::updateOrCreate(
            ['name' => 'Copa Élite SIGET 2026'],
            [
                'super_admin_id' => $superAdmin->id,
                'admin_id' => $admin->id,
                'sport_type' => $sportFutbol->name,
                'start_date' => now()->subDays(7)->toDateString(),
                'end_date' => now()->addMonths(2)->toDateString(),
                'status' => 'active',
            ]
        );

        if ($tournament->rules) {
            $tournament->rules->update([
                'points_for_win' => 3,
                'points_for_draw' => 1,
                'points_for_loss' => 0,
                'yellow_card_limit_for_suspension' => 2,
                'direct_red_suspension_matches' => 1,
                'match_duration_minutes' => 90,
                'max_substitutions' => 5,
                'tiebreaker_rule' => 'goal_difference',
            ]);
        }

        $this->command?->info("✓ Torneo creado y asignado a Admin: {$tournament->name}");

        // -------------------------------------------------------------
        // 4. SEDES Y CUERPO ARBITRAL (CREADOS POR EL ADMINISTRADOR)
        // -------------------------------------------------------------
        $venue1 = Venue::firstOrCreate(
            ['name' => 'Estadio Metropolitano Central'],
            [
                'created_by_user_id' => $admin->id,
                'address' => 'Av. Circunvalar No. 45-10',
                'city' => 'Bogotá',
                'latitude' => 4.60971,
                'longitude' => -74.08175,
                'field_count' => 2,
                'surface_type' => 'Césped Natural',
                'notes' => 'Cancha principal con iluminación LED para partidos nocturnos.',
                'is_active' => true,
            ]
        );

        $venue2 = Venue::firstOrCreate(
            ['name' => 'Complejo Deportivo El Campín'],
            [
                'created_by_user_id' => $admin->id,
                'address' => 'Calle 53 con Carrera 30',
                'city' => 'Bogotá',
                'latitude' => 4.64588,
                'longitude' => -74.07750,
                'field_count' => 3,
                'surface_type' => 'Sintética Profesional',
                'notes' => 'Vestuarios equipados y zona de calentamiento techada.',
                'is_active' => true,
            ]
        );

        $refereeRoldan = Referee::firstOrCreate(
            ['license_number' => 'FIFA-COL-001'],
            [
                'name' => 'Wilmar Roldán',
                'is_active' => true,
                'rating_average' => 4.85,
                'total_matches_officiated' => 14,
            ]
        );

        $refereeRojas = Referee::firstOrCreate(
            ['license_number' => 'COL-042'],
            [
                'name' => 'Andrés Rojas',
                'is_active' => true,
                'rating_average' => 4.60,
                'total_matches_officiated' => 9,
            ]
        );

        $refereeDaza = Referee::firstOrCreate(
            ['license_number' => 'FIFA-COL-003'],
            [
                'name' => 'María Victoria Daza',
                'is_active' => true,
                'rating_average' => 4.90,
                'total_matches_officiated' => 11,
            ]
        );

        // -------------------------------------------------------------
        // 5. 4 DIRECTORES TÉCNICOS Y SUS RESPECTIVOS EQUIPOS
        // -------------------------------------------------------------
        $teamsData = [
            [
                'team_name' => 'Atlético Nacional',
                'coach_name' => 'Pep Guardiola',
                'coach_email' => 'dt.nacional@siget.com',
                'prefix' => 'NAC',
                'players' => [
                    ['name' => 'Kevin Mier', 'number' => 1, 'pos' => 'goalkeeper', 'foot' => 'right', 'rating' => 8.7, 'bio' => 'Arquero seguro bajo los palos y excelente juego de pies.'],
                    ['name' => 'Andrés Román', 'number' => 2, 'pos' => 'defender', 'foot' => 'right', 'rating' => 8.2, 'bio' => 'Lateral potente con constante proyección ofensiva.'],
                    ['name' => 'Felipe Aguirre', 'number' => 3, 'pos' => 'defender', 'foot' => 'right', 'rating' => 8.4, 'bio' => 'Central con gran anticipación y solidez aérea.'],
                    ['name' => 'Juan José Arias', 'number' => 4, 'pos' => 'defender', 'foot' => 'left', 'rating' => 7.8, 'bio' => 'Defensor zurdo táctico y disciplinado.'],
                    ['name' => 'Samuel Velásquez', 'number' => 5, 'pos' => 'defender', 'foot' => 'left', 'rating' => 8.0, 'bio' => 'Marcador de punta con velocidad en retroceso.'],
                    ['name' => 'Robert Mejía', 'number' => 6, 'pos' => 'midfielder', 'foot' => 'right', 'rating' => 8.3, 'bio' => 'Eje de contención y equilibrio táctico.'],
                    ['name' => 'Dorlan Pabón', 'number' => 8, 'pos' => 'midfielder', 'foot' => 'right', 'rating' => 8.9, 'bio' => 'Pegada letal de media distancia y liderazgo.'],
                    ['name' => 'Edwin Cardona', 'number' => 10, 'pos' => 'midfielder', 'foot' => 'right', 'rating' => 9.2, 'bio' => 'Mago del balón, visión milimétrica y tiros libres.'],
                    ['name' => 'Marino Hinestroza', 'number' => 7, 'pos' => 'forward', 'foot' => 'left', 'rating' => 8.6, 'bio' => 'Extremo desequilibrante en el uno contra uno.'],
                    ['name' => 'Jefferson Duque', 'number' => 9, 'pos' => 'forward', 'foot' => 'right', 'rating' => 8.8, 'bio' => 'Goleador de área implacable con olfato de gol.'],
                    ['name' => 'Brahian Palacios', 'number' => 11, 'pos' => 'forward', 'foot' => 'left', 'rating' => 8.1, 'bio' => 'Veloz en contragolpe con gran disparo cruzado.'],
                ],
            ],
            [
                'team_name' => 'Millonarios FC',
                'coach_name' => 'Carlo Ancelotti',
                'coach_email' => 'dt.millonarios@siget.com',
                'prefix' => 'MIL',
                'players' => [
                    ['name' => 'Álvaro Montero', 'number' => 1, 'pos' => 'goalkeeper', 'foot' => 'right', 'rating' => 8.9, 'bio' => 'Gigante del arco con reflejos de primer nivel.'],
                    ['name' => 'Delvin Alfonzo', 'number' => 2, 'pos' => 'defender', 'foot' => 'right', 'rating' => 7.9, 'bio' => 'Lateral incansable en la banda derecha.'],
                    ['name' => 'Andrés Llinás', 'number' => 3, 'pos' => 'defender', 'foot' => 'right', 'rating' => 8.7, 'bio' => 'Central jerárquico, salida limpia y buen quite.'],
                    ['name' => 'Juan Pablo Vargas', 'number' => 4, 'pos' => 'defender', 'foot' => 'left', 'rating' => 8.6, 'bio' => 'Internacional, potente en marca y juego aéreo.'],
                    ['name' => 'Danovis Banguero', 'number' => 5, 'pos' => 'defender', 'foot' => 'left', 'rating' => 7.8, 'bio' => 'Veteranía y oficio en el lateral izquierdo.'],
                    ['name' => 'Stiven Vega', 'number' => 6, 'pos' => 'midfielder', 'foot' => 'right', 'rating' => 8.1, 'bio' => 'Motor incansable en la zona de recuperación.'],
                    ['name' => 'Daniel Giraldo', 'number' => 8, 'pos' => 'midfielder', 'foot' => 'right', 'rating' => 8.0, 'bio' => 'Transición rápida y llegada al área rival.'],
                    ['name' => 'David Mackalister Silva', 'number' => 10, 'pos' => 'midfielder', 'foot' => 'right', 'rating' => 9.1, 'bio' => 'Capitán e ídolo, inteligencia pura y pase entre líneas.'],
                    ['name' => 'Emerson Rodríguez', 'number' => 7, 'pos' => 'forward', 'foot' => 'right', 'rating' => 8.3, 'bio' => 'Desborde explosivo y cambio de ritmo vertiginoso.'],
                    ['name' => 'Leonardo Castro', 'number' => 9, 'pos' => 'forward', 'foot' => 'right', 'rating' => 8.9, 'bio' => 'Artillero potente, remate voraz y juego de espaldas.'],
                    ['name' => 'Daniel Ruiz', 'number' => 11, 'pos' => 'forward', 'foot' => 'left', 'rating' => 8.5, 'bio' => 'Talento diferencial, regate corto y pegada exquisita.'],
                ],
            ],
            [
                'team_name' => 'América de Cali',
                'coach_name' => 'Jürgen Klopp',
                'coach_email' => 'dt.america@siget.com',
                'prefix' => 'AME',
                'players' => [
                    ['name' => 'Jorge Soto', 'number' => 1, 'pos' => 'goalkeeper', 'foot' => 'right', 'rating' => 8.1, 'bio' => 'Atajador solvente con reflejos felinos.'],
                    ['name' => 'Nilson Castrillón', 'number' => 2, 'pos' => 'defender', 'foot' => 'right', 'rating' => 7.9, 'bio' => 'Fuerza física y marca férrea en el carril diestro.'],
                    ['name' => 'Daniel Bocanegra', 'number' => 3, 'pos' => 'defender', 'foot' => 'right', 'rating' => 8.6, 'bio' => 'Maestría táctica, tiro libre y jerarquía en el fondo.'],
                    ['name' => 'Andrés Mosquera Guardia', 'number' => 4, 'pos' => 'defender', 'foot' => 'right', 'rating' => 8.3, 'bio' => 'Central veloz en cruces y coberturas.'],
                    ['name' => 'Edwin Velasco', 'number' => 5, 'pos' => 'defender', 'foot' => 'left', 'rating' => 8.0, 'bio' => 'Lateral zurdo con gran potencia y disciplina táctica.'],
                    ['name' => 'Harold Rivera', 'number' => 6, 'pos' => 'midfielder', 'foot' => 'right', 'rating' => 8.2, 'bio' => 'Primer pase limpio y corte oportuno.'],
                    ['name' => 'Franco Leys', 'number' => 8, 'pos' => 'midfielder', 'foot' => 'right', 'rating' => 8.1, 'bio' => 'Muerde cada pelota con intensidad argentina.'],
                    ['name' => 'Duván Vergara', 'number' => 10, 'pos' => 'midfielder', 'foot' => 'right', 'rating' => 9.3, 'bio' => 'Estrella del torneo, finta mágica y golazo garantizado.'],
                    ['name' => 'Cristian Barrios', 'number' => 7, 'pos' => 'forward', 'foot' => 'left', 'rating' => 8.7, 'bio' => 'Imparable en velocidad y diagonales letales.'],
                    ['name' => 'Rodrigo Holgado', 'number' => 9, 'pos' => 'forward', 'foot' => 'right', 'rating' => 8.6, 'bio' => 'Delantero tanque que no da una pelota por perdida.'],
                    ['name' => 'Michael Barrios', 'number' => 11, 'pos' => 'forward', 'foot' => 'right', 'rating' => 8.2, 'bio' => 'Desequilibrio por banda con experiencia internacional.'],
                ],
            ],
            [
                'team_name' => 'Junior FC',
                'coach_name' => 'Lionel Scaloni',
                'coach_email' => 'dt.junior@siget.com',
                'prefix' => 'JUN',
                'players' => [
                    ['name' => 'Santiago Mele', 'number' => 1, 'pos' => 'goalkeeper', 'foot' => 'right', 'rating' => 9.0, 'bio' => 'Seleccionado uruguayo, salvador en momentos clave.'],
                    ['name' => 'Edwin Herrera', 'number' => 2, 'pos' => 'defender', 'foot' => 'right', 'rating' => 8.0, 'bio' => 'Polifuncional por ambas bandas con criterio.'],
                    ['name' => 'Emanuel Olivera', 'number' => 3, 'pos' => 'defender', 'foot' => 'right', 'rating' => 8.5, 'bio' => 'El Turro: temperamento, anticipo y fuerza defensiva.'],
                    ['name' => 'Jermein Peña', 'number' => 4, 'pos' => 'defender', 'foot' => 'right', 'rating' => 8.2, 'bio' => 'Agresividad sana en la marca y juego físico.'],
                    ['name' => 'Gabriel Fuentes', 'number' => 5, 'pos' => 'defender', 'foot' => 'left', 'rating' => 8.4, 'bio' => 'Lateral zurdo con centro quirúrgico al área.'],
                    ['name' => 'Didier Moreno', 'number' => 6, 'pos' => 'midfielder', 'foot' => 'right', 'rating' => 8.3, 'bio' => 'Despliegue físico incansable en toda la cancha.'],
                    ['name' => 'Víctor Cantillo', 'number' => 8, 'pos' => 'midfielder', 'foot' => 'right', 'rating' => 8.8, 'bio' => 'Claridad suprema en la distribución y pausas de juego.'],
                    ['name' => 'José Enamorado', 'number' => 10, 'pos' => 'midfielder', 'foot' => 'right', 'rating' => 9.1, 'bio' => 'Gambeteador nato que enloquece a cualquier defensa.'],
                    ['name' => 'Deiber Caicedo', 'number' => 7, 'pos' => 'forward', 'foot' => 'right', 'rating' => 8.5, 'bio' => 'Punta veloz con asociación rápida y desmarque.'],
                    ['name' => 'Carlos Bacca', 'number' => 9, 'pos' => 'forward', 'foot' => 'right', 'rating' => 9.3, 'bio' => 'Leyenda goleadora, definición clínica y alma de líder.'],
                    ['name' => 'Yimmi Chará', 'number' => 11, 'pos' => 'forward', 'foot' => 'right', 'rating' => 8.7, 'bio' => 'Dinamismo, paredes rápidas y definición certera.'],
                ],
            ],
        ];

        $createdTeams = [];

        foreach ($teamsData as $tData) {
            // 1. Crear DT (User con rol captain)
            $dtUser = User::updateOrCreate(
                ['email' => $tData['coach_email']],
                [
                    'name' => $tData['coach_name'],
                    'password' => Hash::make('password'),
                    'role' => 'captain',
                    'is_active' => true,
                ]
            );

            // 2. Crear Equipo asignado al Torneo y DT
            $team = Team::updateOrCreate(
                [
                    'tournament_id' => $tournament->id,
                    'name' => $tData['team_name'],
                ],
                [
                    'captain_id' => $dtUser->id,
                    'status' => 'approved',
                    'payment_status' => 'up_to_date',
                ]
            );

            // Tabla pivote tournament_team
            DB::table('tournament_team')->updateOrInsert(
                [
                    'tournament_id' => $tournament->id,
                    'team_id' => $team->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // 3. DT inscribe a sus jugadores, perfiles y fichas médicas
            $createdPlayers = [];
            foreach ($tData['players'] as $pData) {
                $doc = 'CC-' . $tData['prefix'] . '-' . str_pad($pData['number'], 2, '0', STR_PAD_LEFT);

                $player = Player::updateOrCreate(
                    [
                        'team_id' => $team->id,
                        'jersey_number' => $pData['number'],
                    ],
                    [
                        'name' => $pData['name'],
                        'identification_document' => $doc,
                    ]
                );

                // Perfil deportivo
                if ($player->profile) {
                    $player->profile->update([
                        'position' => $pData['pos'],
                        'preferred_foot' => $pData['foot'],
                        'nationality' => 'Colombiana',
                        'birth_date' => Carbon::now()->subYears(rand(20, 33))->subDays(rand(10, 300)),
                        'height_cm' => $pData['pos'] === 'goalkeeper' ? rand(186, 194) : rand(172, 185),
                        'weight_kg' => rand(70, 84),
                        'performance_rating' => $pData['rating'],
                        'is_free_agent' => false,
                        'scouting_notes' => $pData['bio'],
                        'qr_token' => $player->profile->qr_token ?: Str::random(40),
                    ]);
                }

                // Ficha médica
                if ($player->medicalRecord) {
                    $player->medicalRecord->update([
                        'blood_type' => ['O+', 'A+', 'B+', 'O-'][rand(0, 3)],
                        'health_provider' => ['EPS Sura', 'Compensar', 'Sanitas', 'Colmédica'][rand(0, 3)],
                        'allergies' => rand(0, 5) === 0 ? 'Penicilina' : 'Ninguna',
                        'emergency_contact_name' => 'Familia de ' . $player->name,
                        'emergency_contact_phone' => '+57 310 ' . rand(100, 999) . ' ' . rand(1000, 9999),
                        'waiver_signed' => true,
                        'waiver_signed_at' => now()->subDays(6),
                        'is_medically_cleared' => true,
                    ]);
                }

                $createdPlayers[$pData['number']] = $player;
            }

            $createdTeams[$tData['team_name']] = [
                'team' => $team,
                'dt' => $dtUser,
                'players' => $createdPlayers,
            ];

            $this->command?->info("✓ Equipo {$team->name} creado con DT {$dtUser->name} y 11 jugadores registrados.");
        }

        // -------------------------------------------------------------
        // 6. PROGRAMACIÓN DE ENCUENTROS (FIXTURES ROUND-ROBIN ENTRE LOS 4)
        // -------------------------------------------------------------
        $nacional = $createdTeams['Atlético Nacional']['team'];
        $millonarios = $createdTeams['Millonarios FC']['team'];
        $america = $createdTeams['América de Cali']['team'];
        $junior = $createdTeams['Junior FC']['team'];

        $nacPlayers = $createdTeams['Atlético Nacional']['players'];
        $milPlayers = $createdTeams['Millonarios FC']['players'];
        $amePlayers = $createdTeams['América de Cali']['players'];
        $junPlayers = $createdTeams['Junior FC']['players'];

        // =============================================================
        // FECHA 1 - JUGADA (PARTIDOS CERRADOS CON ACTAS Y FIRMAS)
        // =============================================================

        // PARTIDO 1: Atlético Nacional 2 - 1 Millonarios FC
        $match1Date = now()->subDays(3)->setTime(15, 0);
        $match1 = MatchGame::updateOrCreate(
            [
                'tournament_id' => $tournament->id,
                'round_number' => 1,
                'home_team_id' => $nacional->id,
                'away_team_id' => $millonarios->id,
            ],
            [
                'stage' => 'regular',
                'venue_id' => $venue1->id,
                'field_number' => 1,
                'referee_id' => $refereeRoldan->id,
                'match_date' => $match1Date,
                'home_score' => 2,
                'away_score' => 1,
                'status' => 'played',
                'is_locked' => true,
                'locked_at' => $match1Date->copy()->addHours(2),
                'current_period' => 'ended',
                'elapsed_seconds' => 5400,
                'is_timer_running' => false,
                'mvp_player_id' => $nacPlayers[10]->id, // Edwin Cardona MVP
                'chronicle_title' => 'Nacional se impone en un clásico vibrante con genialidad de Cardona',
                'chronicle_body' => "En una tarde memorable en el Estadio Metropolitano Central, Atlético Nacional superó 2-1 a Millonarios FC en un clásico que desbordó adrenalina de principio a fin. El conjunto verdolaga abrió la cuenta temprano gracias a una soberbia definición de Jefferson Duque al minuto 22 tras pase milimétrico de Edwin Cardona. Millonarios reaccionó con fiereza y encontró la igualdad a los 54 minutos por intermedio de Leonardo Castro, quien fusiló al arquero Mier. Sin embargo, cuando parecía que el clásico terminaba en tablas, Cardona sacó un latigazo inolvidable desde fuera del área al minuto 82 que selló el triunfo de Nacional y desató el júbilo en las gradas.",
                'chronicle_generated_at' => $match1Date->copy()->addHours(2),
                'match_sheet_notes' => 'Partido disputado con alto nivel competitivo y deportividad. Sin incidentes en tribuna ni reclamos formales.',
            ]
        );

        // Alineaciones Match 1
        $this->seedLineup($match1, $nacional->id, $nacPlayers);
        $this->seedLineup($match1, $millonarios->id, $milPlayers);

        // Eventos Match 1
        MatchEvent::firstOrCreate(
            ['match_id' => $match1->id, 'minute' => 22, 'event_type' => 'goal'],
            ['team_id' => $nacional->id, 'player_id' => $nacPlayers[9]->id, 'period' => '1H', 'second' => 14, 'notes' => 'Gol de jugada colectiva']
        );
        MatchEvent::firstOrCreate(
            ['match_id' => $match1->id, 'minute' => 38, 'event_type' => 'yellow_card'],
            ['team_id' => $millonarios->id, 'player_id' => $milPlayers[3]->id, 'period' => '1H', 'second' => 45, 'notes' => 'Falta táctica reiterada']
        );
        MatchEvent::firstOrCreate(
            ['match_id' => $match1->id, 'minute' => 54, 'event_type' => 'goal'],
            ['team_id' => $millonarios->id, 'player_id' => $milPlayers[9]->id, 'period' => '2H', 'second' => 20, 'notes' => 'Definición cruzada']
        );
        MatchEvent::firstOrCreate(
            ['match_id' => $match1->id, 'minute' => 68, 'event_type' => 'substitution'],
            ['team_id' => $nacional->id, 'player_id' => $nacPlayers[7]->id, 'sub_in_player_id' => $nacPlayers[11]->id, 'period' => '2H', 'second' => 0, 'notes' => 'Cambio ofensivo']
        );
        MatchEvent::firstOrCreate(
            ['match_id' => $match1->id, 'minute' => 82, 'event_type' => 'goal'],
            ['team_id' => $nacional->id, 'player_id' => $nacPlayers[10]->id, 'period' => '2H', 'second' => 50, 'notes' => 'Golazo de media distancia']
        );

        // Firmas Digitales Tripartitas Match 1
        MatchSignature::updateOrCreate(
            ['match_id' => $match1->id, 'signer_role' => 'referee'],
            [
                'signer_name' => 'Wilmar Roldán',
                'signature_data' => 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="50"><text x="10" y="30" font-family="cursive" font-size="20" fill="%231e293b">W. Roldan</text></svg>',
                'signed_at' => $match1Date->copy()->addMinutes(110),
                'ip_address' => '192.168.1.15',
            ]
        );
        MatchSignature::updateOrCreate(
            ['match_id' => $match1->id, 'signer_role' => 'home_coach'],
            [
                'signer_name' => 'Pep Guardiola',
                'signature_data' => 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="50"><text x="10" y="30" font-family="cursive" font-size="20" fill="%231e293b">Pep Guardiola</text></svg>',
                'signed_at' => $match1Date->copy()->addMinutes(115),
                'ip_address' => '192.168.1.18',
            ]
        );
        MatchSignature::updateOrCreate(
            ['match_id' => $match1->id, 'signer_role' => 'away_coach'],
            [
                'signer_name' => 'Carlo Ancelotti',
                'signature_data' => 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="50"><text x="10" y="30" font-family="cursive" font-size="20" fill="%231e293b">C. Ancelotti</text></svg>',
                'signed_at' => $match1Date->copy()->addMinutes(117),
                'ip_address' => '192.168.1.20',
            ]
        );

        // Evaluaciones de los DTs al Árbitro en Match 1
        RefereeEvaluation::updateOrCreate(
            ['match_id' => $match1->id, 'team_id' => $nacional->id],
            [
                'referee_id' => $refereeRoldan->id,
                'evaluated_by_user_id' => $createdTeams['Atlético Nacional']['dt']->id,
                'score_overall' => 5,
                'score_rule_enforcement' => 5,
                'score_fairness' => 5,
                'score_punctuality' => 5,
                'comments' => 'Excelente arbitraje, dejó jugar y manejó muy bien las amonestaciones.',
            ]
        );
        RefereeEvaluation::updateOrCreate(
            ['match_id' => $match1->id, 'team_id' => $millonarios->id],
            [
                'referee_id' => $refereeRoldan->id,
                'evaluated_by_user_id' => $createdTeams['Millonarios FC']['dt']->id,
                'score_overall' => 4,
                'score_rule_enforcement' => 5,
                'score_fairness' => 4,
                'score_punctuality' => 5,
                'comments' => 'Buen partido, correcto en la aplicación del reglamento.',
            ]
        );

        // Votos de fanáticos MVP Match 1
        MatchMvpVote::firstOrCreate(
            ['match_id' => $match1->id, 'voter_fingerprint' => 'fan-session-1'],
            ['player_id' => $nacPlayers[10]->id, 'user_id' => null]
        );
        MatchMvpVote::firstOrCreate(
            ['match_id' => $match1->id, 'voter_fingerprint' => 'fan-session-2'],
            ['player_id' => $nacPlayers[10]->id, 'user_id' => null]
        );
        MatchMvpVote::firstOrCreate(
            ['match_id' => $match1->id, 'voter_fingerprint' => 'fan-session-3'],
            ['player_id' => $milPlayers[9]->id, 'user_id' => null]
        );

        // PARTIDO 2: América de Cali 1 - 1 Junior FC
        $match2Date = now()->subDays(3)->setTime(17, 30);
        $match2 = MatchGame::updateOrCreate(
            [
                'tournament_id' => $tournament->id,
                'round_number' => 1,
                'home_team_id' => $america->id,
                'away_team_id' => $junior->id,
            ],
            [
                'stage' => 'regular',
                'venue_id' => $venue2->id,
                'field_number' => 1,
                'referee_id' => $refereeRojas->id,
                'match_date' => $match2Date,
                'home_score' => 1,
                'away_score' => 1,
                'status' => 'played',
                'is_locked' => true,
                'locked_at' => $match2Date->copy()->addHours(2),
                'current_period' => 'ended',
                'elapsed_seconds' => 5400,
                'is_timer_running' => false,
                'mvp_player_id' => $amePlayers[10]->id, // Duván Vergara MVP
                'chronicle_title' => 'Tablas en El Campín: América y Junior igualan en un choque de titanes',
                'chronicle_body' => "En el marco de la primera jornada, América de Cali y Junior FC protagonizaron un intenso empate 1-1 en el Complejo Deportivo El Campín. Duván Vergara frotó la lámpara al minuto 31 con una diagonal letal para abrir la cuenta escarlata. En el complemento, el tiburón respondió con el peso de su historia: Carlos Bacca aprovechó un balón suelto al 67 para sellar la igualdad definitiva con frialdad implacable.",
                'chronicle_generated_at' => $match2Date->copy()->addHours(2),
                'match_sheet_notes' => 'Empate cerrado sin amonestaciones desmedidas. Gran comportamiento del público.',
            ]
        );

        $this->seedLineup($match2, $america->id, $amePlayers);
        $this->seedLineup($match2, $junior->id, $junPlayers);

        MatchEvent::firstOrCreate(
            ['match_id' => $match2->id, 'minute' => 31, 'event_type' => 'goal'],
            ['team_id' => $america->id, 'player_id' => $amePlayers[10]->id, 'period' => '1H', 'second' => 25, 'notes' => 'Disparo al ángulo superior']
        );
        MatchEvent::firstOrCreate(
            ['match_id' => $match2->id, 'minute' => 67, 'event_type' => 'goal'],
            ['team_id' => $junior->id, 'player_id' => $junPlayers[9]->id, 'period' => '2H', 'second' => 10, 'notes' => 'Aprovecha rebote en el área']
        );
        MatchEvent::firstOrCreate(
            ['match_id' => $match2->id, 'minute' => 77, 'event_type' => 'yellow_card'],
            ['team_id' => $junior->id, 'player_id' => $junPlayers[4]->id, 'period' => '2H', 'second' => 40, 'notes' => 'Corte con mano involuntaria']
        );

        MatchSignature::updateOrCreate(
            ['match_id' => $match2->id, 'signer_role' => 'referee'],
            [
                'signer_name' => 'Andrés Rojas',
                'signature_data' => 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="50"><text x="10" y="30" font-family="cursive" font-size="20" fill="%231e293b">A. Rojas</text></svg>',
                'signed_at' => $match2Date->copy()->addMinutes(110),
            ]
        );
        MatchSignature::updateOrCreate(
            ['match_id' => $match2->id, 'signer_role' => 'home_coach'],
            [
                'signer_name' => 'Jürgen Klopp',
                'signature_data' => 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="50"><text x="10" y="30" font-family="cursive" font-size="20" fill="%231e293b">J. Klopp</text></svg>',
                'signed_at' => $match2Date->copy()->addMinutes(115),
            ]
        );
        MatchSignature::updateOrCreate(
            ['match_id' => $match2->id, 'signer_role' => 'away_coach'],
            [
                'signer_name' => 'Lionel Scaloni',
                'signature_data' => 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="50"><text x="10" y="30" font-family="cursive" font-size="20" fill="%231e293b">L. Scaloni</text></svg>',
                'signed_at' => $match2Date->copy()->addMinutes(116),
            ]
        );

        // =============================================================
        // FECHA 2 - PROGRAMADA PARA ESTE FIN DE SEMANA
        // =============================================================
        $nextSaturday = now()->next(Carbon::SATURDAY);

        // PARTIDO 3: Atlético Nacional vs América de Cali
        MatchGame::updateOrCreate(
            [
                'tournament_id' => $tournament->id,
                'round_number' => 2,
                'home_team_id' => $nacional->id,
                'away_team_id' => $america->id,
            ],
            [
                'stage' => 'regular',
                'venue_id' => $venue1->id,
                'field_number' => 1,
                'referee_id' => $refereeDaza->id,
                'match_date' => $nextSaturday->copy()->setTime(15, 0),
                'home_score' => 0,
                'away_score' => 0,
                'status' => 'scheduled',
                'current_period' => 'scheduled',
                'is_locked' => false,
            ]
        );

        // PARTIDO 4: Millonarios FC vs Junior FC
        MatchGame::updateOrCreate(
            [
                'tournament_id' => $tournament->id,
                'round_number' => 2,
                'home_team_id' => $millonarios->id,
                'away_team_id' => $junior->id,
            ],
            [
                'stage' => 'regular',
                'venue_id' => $venue2->id,
                'field_number' => 1,
                'referee_id' => $refereeRoldan->id,
                'match_date' => $nextSaturday->copy()->setTime(17, 30),
                'home_score' => 0,
                'away_score' => 0,
                'status' => 'scheduled',
                'current_period' => 'scheduled',
                'is_locked' => false,
            ]
        );

        // =============================================================
        // FECHA 3 - PROGRAMADA PARA EL SIGUIENTE FIN DE SEMANA
        // =============================================================
        $futureSaturday = $nextSaturday->copy()->addWeek();

        // PARTIDO 5: Junior FC vs Atlético Nacional
        MatchGame::updateOrCreate(
            [
                'tournament_id' => $tournament->id,
                'round_number' => 3,
                'home_team_id' => $junior->id,
                'away_team_id' => $nacional->id,
            ],
            [
                'stage' => 'regular',
                'venue_id' => $venue1->id,
                'field_number' => 2,
                'referee_id' => $refereeRojas->id,
                'match_date' => $futureSaturday->copy()->setTime(15, 0),
                'home_score' => 0,
                'away_score' => 0,
                'status' => 'scheduled',
                'current_period' => 'scheduled',
                'is_locked' => false,
            ]
        );

        // PARTIDO 6: Millonarios FC vs América de Cali
        MatchGame::updateOrCreate(
            [
                'tournament_id' => $tournament->id,
                'round_number' => 3,
                'home_team_id' => $millonarios->id,
                'away_team_id' => $america->id,
            ],
            [
                'stage' => 'regular',
                'venue_id' => $venue2->id,
                'field_number' => 2,
                'referee_id' => $refereeDaza->id,
                'match_date' => $futureSaturday->copy()->setTime(17, 30),
                'home_score' => 0,
                'away_score' => 0,
                'status' => 'scheduled',
                'current_period' => 'scheduled',
                'is_locked' => false,
            ]
        );

        $this->command?->info("✓ 6 Encuentros del Torneo programados (2 jugados con resultados y 4 por disputar).");

        // -------------------------------------------------------------
        // 7. RECÁLCULO OFICIAL DE TABLA DE POSICIONES
        // -------------------------------------------------------------
        app(StandingService::class)->recalculate($tournament);
        $this->command?->info("✓ Tabla de posiciones calculada automáticamente con los partidos de la Fecha 1.");

        // -------------------------------------------------------------
        // 8. TRIBUNAL DE PENAS / SANCIÓN DISCIPLINARIA DEMO
        // -------------------------------------------------------------
        DisciplinarySanction::updateOrCreate(
            [
                'tournament_id' => $tournament->id,
                'player_id' => $milPlayers[3]->id,
                'match_id' => $match1->id,
            ],
            [
                'sanction_type' => 'yellow_accumulation',
                'matches_suspended' => 1,
                'matches_served' => 0,
                'status' => 'active',
                'notes' => 'Acumulación de tarjetas amarillas según artículo 14 del Reglamento.',
            ]
        );

        // -------------------------------------------------------------
        // 9. AGENTES LIBRES PARA EL RADAR DE SCOUTING & FICHAJES
        // -------------------------------------------------------------
        $freeAgent1 = Player::firstOrCreate(
            ['identification_document' => 'CC-SCOUT-01'],
            [
                'team_id' => null,
                'name' => 'Mateo Casierra',
                'jersey_number' => 9,
            ]
        );
        if ($freeAgent1->profile) {
            $freeAgent1->profile->update([
                'position' => 'forward',
                'preferred_foot' => 'right',
                'nationality' => 'Colombiana',
                'birth_date' => Carbon::parse('1997-04-13'),
                'height_cm' => 184,
                'weight_kg' => 77,
                'performance_rating' => 8.9,
                'is_free_agent' => true,
                'scouting_notes' => 'Delantero libre con gran movilidad internacional y alta tasa de efectividad.',
            ]);
        }

        $freeAgent2 = Player::firstOrCreate(
            ['identification_document' => 'CC-SCOUT-02'],
            [
                'team_id' => null,
                'name' => 'Yerson Mosquera',
                'jersey_number' => 4,
            ]
        );
        if ($freeAgent2->profile) {
            $freeAgent2->profile->update([
                'position' => 'defender',
                'preferred_foot' => 'right',
                'nationality' => 'Colombiana',
                'birth_date' => Carbon::parse('2001-05-02'),
                'height_cm' => 188,
                'weight_kg' => 81,
                'performance_rating' => 8.8,
                'is_free_agent' => true,
                'scouting_notes' => 'Defensa central potente, rápido en los repliegues y con proyección europea.',
            ]);
        }

        $this->command?->info("✓ Agentes libres agregados para el módulo de Scouting.");
        $this->command?->info("=====================================================");
        $this->command?->info("SEEDER COMPLETO FINALIZADO EXITOSAMENTE.");
        $this->command?->info("=====================================================");
    }

    /**
     * Registra la alineación oficial de un equipo para un partido (titulares y suplentes verificados).
     *
     * @param array<int, Player> $players
     */
    protected function seedLineup(MatchGame $match, int $teamId, array $players): void
    {
        // 7 Titulares (Fútbol 7 / Cancha reducida o base de 11)
        $starters = [1, 2, 3, 5, 8, 9, 10];

        foreach ($players as $num => $player) {
            $isStarter = in_array($num, $starters, true);

            MatchLineup::updateOrCreate(
                [
                    'match_id' => $match->id,
                    'team_id' => $teamId,
                    'player_id' => $player->id,
                ],
                [
                    'jersey_number' => $player->jersey_number,
                    'position' => $player->profile?->position ?? 'midfielder',
                    'is_starter' => $isStarter,
                    'verified_by_qr' => true,
                    'verified_at' => $match->match_date->copy()->subMinutes(25),
                ]
            );
        }
    }
}
