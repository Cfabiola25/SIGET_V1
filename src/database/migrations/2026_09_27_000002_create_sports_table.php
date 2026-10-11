<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        $footballModalities = [
            'Fútbol 11',
            'Fútbol 8 (Canchas sintéticas)',
            'Fútbol 5 (Canchas sintéticas)',
            'Fútbol de Salón (Microfútbol)',
        ];

        foreach ($footballModalities as $name) {
            DB::table('sports')->insert([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $existingSports = DB::table('tournaments')->distinct()->pluck('sport_type');

        foreach ($existingSports as $name) {
            DB::table('sports')->insertOrIgnore([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sports');
    }
};
