<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_games', function (Blueprint $table) {
            $table->foreignId('home_team_id')->nullable()->change();
            $table->foreignId('away_team_id')->nullable()->change();
            $table->unsignedSmallInteger('round_number')->default(1)->after('tournament_id');
            $table->string('stage')->default('regular')->after('round_number');
            $table->string('bracket_position')->nullable()->after('stage');
            $table->foreignId('next_match_id')->nullable()->after('bracket_position')->constrained('match_games')->nullOnDelete();
            $table->string('next_match_slot')->nullable()->after('next_match_id'); // 'home' | 'away'
            $table->string('group_name')->nullable()->after('next_match_slot');
        });
    }

    public function down(): void
    {
        Schema::table('match_games', function (Blueprint $table) {
            $table->dropForeign(['next_match_id']);
            $table->dropColumn([
                'round_number',
                'stage',
                'bracket_position',
                'next_match_id',
                'next_match_slot',
                'group_name',
            ]);
        });
    }
};
