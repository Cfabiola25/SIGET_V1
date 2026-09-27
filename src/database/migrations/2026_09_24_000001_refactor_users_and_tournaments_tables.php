<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'organizer')
            ->update(['role' => 'admin']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin', 'captain', 'player'])
                ->default('player')
                ->change();
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->foreignId('super_admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('id');
            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('super_admin_id');
        });

        DB::statement(
            'UPDATE tournaments SET super_admin_id = user_id WHERE super_admin_id IS NULL'
        );

        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('id');
        });

        DB::statement(
            'UPDATE tournaments SET user_id = super_admin_id WHERE user_id IS NULL'
        );

        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropForeign(['super_admin_id']);
            $table->dropForeign(['admin_id']);
            $table->dropColumn(['super_admin_id', 'admin_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'organizer', 'captain', 'player'])
                ->default('player')
                ->change();
        });
    }
};
