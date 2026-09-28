<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('match_games')->cascadeOnDelete();
            $table->enum('signer_role', ['referee', 'home_coach', 'away_coach']);
            $table->string('signer_name');
            $table->longText('signature_data'); // Data URL (SVG/Base64)
            $table->timestamp('signed_at');
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->unique(['match_id', 'signer_role']);
        });

        Schema::table('match_games', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('status');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->text('match_sheet_notes')->nullable()->after('locked_at');
        });
    }

    public function down(): void
    {
        Schema::table('match_games', function (Blueprint $table) {
            $table->dropColumn(['is_locked', 'locked_at', 'match_sheet_notes']);
        });

        Schema::dropIfExists('match_signatures');
    }
};
