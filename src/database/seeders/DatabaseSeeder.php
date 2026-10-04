<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\v1\Sport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurar únicamente al Super Administrador oficial
        User::updateOrCreate(
            ['email' => 'superadmin@siget.com'],
            [
                'name' => 'Super Admin SIGET',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        // Eliminar cualquier otro usuario para dejar el sistema limpio
        User::where('email', '!=', 'superadmin@siget.com')->delete();

        // 2. Asegurar únicamente las 4 modalidades oficiales de Fútbol
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

