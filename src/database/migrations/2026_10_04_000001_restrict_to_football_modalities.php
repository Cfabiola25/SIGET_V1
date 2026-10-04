<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $footballModalities = [
            'Fútbol 11',
            'Fútbol 8 (Canchas sintéticas)',
            'Fútbol 5 (Canchas sintéticas)',
            'Fútbol de Salón (Microfútbol)',
        ];

        // 1. Asegurar que existan las 4 modalidades oficiales en sports
        foreach ($footballModalities as $name) {
            DB::table('sports')->updateOrInsert(
                ['name' => $name],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // 2. Normalizar torneos existentes para que apunten a una de las modalidades válidas
        DB::table('tournaments')
            ->whereIn('sport_type', ['Futbol', 'Fútbol', 'futbol', 'Soccer'])
            ->update(['sport_type' => 'Fútbol 11']);

        DB::table('tournaments')
            ->whereIn('sport_type', ['Fútbol 8', 'Futbol 8'])
            ->update(['sport_type' => 'Fútbol 8 (Canchas sintéticas)']);

        DB::table('tournaments')
            ->whereIn('sport_type', ['Fútbol 5', 'Futbol 5'])
            ->update(['sport_type' => 'Fútbol 5 (Canchas sintéticas)']);

        DB::table('tournaments')
            ->whereIn('sport_type', ['Futsal', 'Microfutbol', 'Microfútbol', 'Fútbol de Salón'])
            ->update(['sport_type' => 'Fútbol de Salón (Microfútbol)']);

        // Cualquier otro torneo rezagado de prueba se migra a Fútbol 11
        DB::table('tournaments')
            ->whereNotIn('sport_type', $footballModalities)
            ->update(['sport_type' => 'Fútbol 11']);

        // 3. Eliminar deportes ajenos al fútbol del catálogo general
        DB::table('sports')
            ->whereNotIn('name', $footballModalities)
            ->delete();
    }

    public function down(): void
    {
        // Reversible sin romper datos
    }
};
