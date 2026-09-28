<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('yellow_card_limit_for_suspension')->default(2);
            $table->unsignedTinyInteger('direct_red_suspension_matches')->default(1);
            $table->unsignedTinyInteger('points_for_win')->default(3);
            $table->unsignedTinyInteger('points_for_draw')->default(1);
            $table->unsignedTinyInteger('points_for_loss')->default(0);
            $table->unsignedSmallInteger('match_duration_minutes')->default(90);
            $table->unsignedTinyInteger('max_substitutions')->default(5);
            $table->string('tiebreaker_rule')->default('goal_difference');
            $table->boolean('reset_cards_on_knockout')->default(false);
            $table->unsignedSmallInteger('lineup_lock_minutes_before_match')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_rules');
    }
};
