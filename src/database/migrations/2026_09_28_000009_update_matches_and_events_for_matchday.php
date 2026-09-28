<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_games', function (Blueprint $table) {
            $table->string('current_period')->default('scheduled')->after('status');
            $table->timestamp('timer_started_at')->nullable()->after('current_period');
            $table->unsignedInteger('elapsed_seconds')->default(0)->after('timer_started_at');
            $table->boolean('is_timer_running')->default(false)->after('elapsed_seconds');
            $table->foreignId('referee_id')->nullable()->after('venue_id')->constrained('referees')->nullOnDelete();
        });

        Schema::table('match_events', function (Blueprint $table) {
            $table->string('period')->default('1H')->after('event_type');
            $table->unsignedTinyInteger('second')->default(0)->after('minute');
            $table->foreignId('sub_in_player_id')->nullable()->after('player_id')->constrained('players')->nullOnDelete();
            $table->string('notes')->nullable()->after('second');
        });
    }

    public function down(): void
    {
        Schema::table('match_events', function (Blueprint $table) {
            $table->dropForeign(['sub_in_player_id']);
            $table->dropColumn(['period', 'second', 'sub_in_player_id', 'notes']);
        });

        Schema::table('match_games', function (Blueprint $table) {
            $table->dropForeign(['referee_id']);
            $table->dropColumn(['current_period', 'timer_started_at', 'elapsed_seconds', 'is_timer_running', 'referee_id']);
        });
    }
};
