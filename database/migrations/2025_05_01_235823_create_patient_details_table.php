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
        Schema::create('patient_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('doctor_id')->constrained('doctors')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('allergies');
            $table->string('chronic_conditions');
            $table->enum('marital_status',['single','married','divorced']);
            $table->string('occupation');
            $table->enum('smoking_status',['smoked','notSmoked','previously']);
            $table->enum('alcohol_use',['rare','regular','notDrinking']);
            $table->string('weight_kg');
            $table->string('height_cm');
            $table->string('last_visit_date')->nullable();
            $table->string('family_medical_history');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_details');
    }
};
