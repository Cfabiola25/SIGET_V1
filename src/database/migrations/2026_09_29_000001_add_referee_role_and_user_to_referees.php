<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Modificar enum de users.role para incluir 'referee'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'captain', 'player', 'referee') NOT NULL DEFAULT 'player'");

        // 2. Agregar user_id a la tabla referees para vincular con la cuenta de usuario
        Schema::table('referees', function (Blueprint $table) {
            if (! Schema::hasColumn('referees', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('referees', function (Blueprint $table) {
            if (Schema::hasColumn('referees', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'captain', 'player') NOT NULL DEFAULT 'player'");
    }
};
