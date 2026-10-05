<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar campos de contacto a player_profiles si no existen
        Schema::table('player_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('player_profiles', 'contact_phone')) {
                $table->string('contact_phone', 30)->nullable()->after('nationality');
            }
            if (! Schema::hasColumn('player_profiles', 'contact_email')) {
                $table->string('contact_email', 150)->nullable()->after('contact_phone');
            }
        });

        // 2. Crear tabla de solicitudes de fichaje y contacto
        Schema::create('player_recruitment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('proposed_jersey_number')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_recruitment_requests');

        Schema::table('player_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('player_profiles', 'contact_phone')) {
                $table->dropColumn('contact_phone');
            }
            if (Schema::hasColumn('player_profiles', 'contact_email')) {
                $table->dropColumn('contact_email');
            }
        });
    }
};
