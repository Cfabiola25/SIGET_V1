<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Único usuario inicial: Super Administrador de SIGET
        User::firstOrCreate(
            ['email' => 'superadmin@siget.com'],
            [
                'name' => 'Super Admin SIGET',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
    }
}
