<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('captain_id')->nullable()->change();
        });

        Schema::table('match_games', function (Blueprint $table) {
            $table->foreignId('venue_id')->nullable()->after('away_team_id')->constrained('venues')->nullOnDelete();
            $table->string('field_number')->nullable()->after('venue_id');
        });
    }

    public function down(): void
    {
        Schema::table('match_games', function (Blueprint $table) {
            $table->dropForeign(['venue_id']);
            $table->dropColumn(['venue_id', 'field_number']);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('captain_id')->nullable(false)->change();
        });
    }
};
