<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medical_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->nullable()
                ->constrained('prescriptions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('appointment_id')->nullable()
                ->constrained('appointments')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('symptoms')->nullable();
            $table->string('diagnosis')->nullable();
            $table->string('doctorNote')->nullable();
            $table->string('patientNote')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_infos');
    }
};
