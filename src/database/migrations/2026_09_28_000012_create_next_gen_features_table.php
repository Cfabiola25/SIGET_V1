<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Campos de IA Match Reporter y MVP en match_games
        Schema::table('match_games', function (Blueprint $table) {
            if (! Schema::hasColumn('match_games', 'mvp_player_id')) {
                $table->foreignId('mvp_player_id')->nullable()->after('match_sheet_notes')->constrained('players')->nullOnDelete();
            }
            if (! Schema::hasColumn('match_games', 'chronicle_title')) {
                $table->string('chronicle_title')->nullable()->after('mvp_player_id');
            }
            if (! Schema::hasColumn('match_games', 'chronicle_body')) {
                $table->longText('chronicle_body')->nullable()->after('chronicle_title');
            }
            if (! Schema::hasColumn('match_games', 'chronicle_generated_at')) {
                $table->timestamp('chronicle_generated_at')->nullable()->after('chronicle_body');
            }
        });

        // 2. Votación Pública del MVP en Vivo
        if (! Schema::hasTable('match_mvp_votes')) {
            Schema::create('match_mvp_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('match_id')->constrained('match_games')->cascadeOnDelete();
                $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('voter_fingerprint')->index(); // IP o Session ID
                $table->timestamps();

                $table->unique(['match_id', 'voter_fingerprint']);
            });
        }

        // 3. Scouting & Radar de Talentos (Agencia Libre)
        Schema::table('player_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('player_profiles', 'is_free_agent')) {
                $table->boolean('is_free_agent')->default(false)->after('preferred_foot');
            }
            if (! Schema::hasColumn('player_profiles', 'performance_rating')) {
                $table->decimal('performance_rating', 4, 1)->default(7.0)->after('is_free_agent'); // Calificación 1.0 a 10.0
            }
            if (! Schema::hasColumn('player_profiles', 'mvp_awards_count')) {
                $table->unsignedInteger('mvp_awards_count')->default(0)->after('performance_rating');
            }
            if (! Schema::hasColumn('player_profiles', 'scouting_notes')) {
                $table->text('scouting_notes')->nullable()->after('mvp_awards_count');
            }
        });

        // 4. Profesionalización del Arbitraje: Evaluaciones Post-Partido y Conflictos de Interés
        if (! Schema::hasTable('referee_evaluations')) {
            Schema::create('referee_evaluations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('match_id')->constrained('match_games')->cascadeOnDelete();
                $table->foreignId('referee_id')->constrained('referees')->cascadeOnDelete();
                $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
                $table->foreignId('evaluated_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedTinyInteger('score_overall')->default(5); // 1-5
                $table->unsignedTinyInteger('score_rule_enforcement')->default(5);
                $table->unsignedTinyInteger('score_fairness')->default(5);
                $table->unsignedTinyInteger('score_punctuality')->default(5);
                $table->text('comments')->nullable();
                $table->timestamps();

                $table->unique(['match_id', 'team_id']);
            });
        }

        if (! Schema::hasTable('referee_conflict_records')) {
            Schema::create('referee_conflict_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('referee_id')->constrained('referees')->cascadeOnDelete();
                $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
                $table->string('reason'); // 'club_origin', 'disciplinary_incident', 'formal_recusal'
                $table->timestamps();

                $table->unique(['referee_id', 'team_id']);
            });
        }

        Schema::table('referees', function (Blueprint $table) {
            if (! Schema::hasColumn('referees', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('license_number');
            }
            if (! Schema::hasColumn('referees', 'rating_average')) {
                $table->decimal('rating_average', 3, 2)->default(5.00)->after('is_active');
            }
            if (! Schema::hasColumn('referees', 'total_matches_officiated')) {
                $table->unsignedInteger('total_matches_officiated')->default(0)->after('rating_average');
            }
        });
    }

    public function down(): void
    {
        Schema::table('referees', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'rating_average', 'total_matches_officiated']);
        });

        Schema::dropIfExists('referee_conflict_records');
        Schema::dropIfExists('referee_evaluations');

        Schema::table('player_profiles', function (Blueprint $table) {
            $table->dropColumn(['is_free_agent', 'performance_rating', 'mvp_awards_count', 'scouting_notes']);
        });

        Schema::dropIfExists('match_mvp_votes');

        Schema::table('match_games', function (Blueprint $table) {
            $table->dropForeign(['mvp_player_id']);
            $table->dropColumn(['mvp_player_id', 'chronicle_title', 'chronicle_body', 'chronicle_generated_at']);
        });
    }
};
