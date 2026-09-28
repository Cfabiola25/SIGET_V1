<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('photo_path')->nullable();
            $table->string('position')->default('midfielder'); // goalkeeper, defender, midfielder, forward
            $table->string('preferred_foot')->default('right'); // right, left, ambidextrous
            $table->date('birth_date')->nullable();
            $table->string('nationality')->default('Colombiana');
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->unsignedSmallInteger('weight_kg')->nullable();
            $table->unsignedSmallInteger('mvp_count')->default(0);
            $table->string('qr_token', 64)->unique();
            $table->timestamps();
        });

        Schema::create('player_medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('blood_type')->default('O+');
            $table->string('health_provider')->nullable(); // EPS / Seguro Médico
            $table->text('allergies')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->boolean('waiver_signed')->default(false);
            $table->timestamp('waiver_signed_at')->nullable();
            $table->string('id_document_path')->nullable();
            $table->boolean('is_medically_cleared')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_medical_records');
        Schema::dropIfExists('player_profiles');
    }
};
