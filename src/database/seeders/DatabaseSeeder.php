<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\v1\Sport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpieza absoluta de tablas de competencia y registros de prueba
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $tablesToClean = [
            'audit_logs',
            'disciplinary_sanctions',
            'match_signatures',
            'match_mvp_votes',
            'match_events',
            'match_lineups',
            'match_games',
            'standings',
            'team_invitations',
            'player_medical_records',
            'player_recruitment_requests',
            'player_profiles',
            'players',
            'tournament_team',
            'teams',
            'referee_conflict_records',
            'referee_evaluations',
            'referees',
            'venues',
            'tournament_rules',
            'tournaments',
        ];

        foreach ($tablesToClean as $table) {
            DB::table($table)->truncate();
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // 2. Asegurar únicamente a los Super Administradores oficiales
        User::updateOrCreate(
            ['email' => 'superadmin@siget.com'],
            [
                'name' => 'Super Admin SIGET',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'nellycano800@gmail.com'],
            [
                'name' => 'Nelly Fabiola Cano Oviedo',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        // Eliminar cualquier otro usuario para dejar únicamente a Super Admin
        User::whereNotIn('email', ['superadmin@siget.com', 'nellycano800@gmail.com'])->delete();

        // 3. Asegurar únicamente las 4 modalidades oficiales de Fútbol
        $footballModalities = [
            'Fútbol 11',
            'Fútbol 8 (Canchas sintéticas)',
            'Fútbol 5 (Canchas sintéticas)',
            'Fútbol de Salón (Microfútbol)',
        ];

        foreach ($footballModalities as $name) {
            Sport::firstOrCreate(['name' => $name]);
        }

        Sport::whereNotIn('name', $footballModalities)->delete();
    }
}

