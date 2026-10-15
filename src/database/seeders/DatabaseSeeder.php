<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\v1\Sport;
use App\Models\v1\Tournament;
use App\Models\v1\TournamentRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurar únicamente al Super Administrador oficial
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@siget.com'],
            [
                'name' => 'Super Admin SIGET',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        // 2. Administrador de Torneo Oficial
        $admin = User::updateOrCreate(
            ['email' => 'nellycano800@gmail.com'],
            [
                'name' => 'Nelly Fabiola Cano Oviedo',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Eliminar otros usuarios para dejar el sistema 100% limpio
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

        // 4. Torneo oficial activo asignado al Administrador
        $tournament = Tournament::firstOrCreate(
            ['name' => 'Copa Élite Federativa SIGET'],
            [
                'sport_type' => 'Fútbol 11',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(3)->toDateString(),
                'status' => 'active',
                'is_featured' => true,
                'admin_id' => $admin->id,
                'super_admin_id' => $superAdmin->id,
            ]
        );

        if (! TournamentRule::where('tournament_id', $tournament->id)->exists()) {
            TournamentRule::create(TournamentRule::rulesForModality('Fútbol 11', $tournament->id));
        }
    }
}

