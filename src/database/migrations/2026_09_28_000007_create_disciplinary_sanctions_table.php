<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplinary_sanctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('match_id')->nullable()->constrained('match_games')->nullOnDelete();
            $table->string('sanction_type')->default('yellow_accumulation'); // yellow_accumulation, direct_red, double_yellow, administrative
            $table->unsignedTinyInteger('matches_suspended')->default(1);
            $table->unsignedTinyInteger('matches_served')->default(0);
            $table->enum('status', ['active', 'served', 'pardoned'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_sanctions');
    }
};
