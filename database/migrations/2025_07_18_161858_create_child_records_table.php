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
        Schema::create('child_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('patients')->cascadeOnUpdate()->cascadeOnUpdate();
            $table->foreignId('doctor_id')->constrained('doctors'); // رقم الطبيب المسؤول عن هذا الطفل
            $table->date('last_visit_date')->nullable();
            $table->date('next_visit_date')->nullable();
            $table->float('height_cm')->nullable();
            $table->float('weight_kg')->nullable();
            $table->float('head_circumference_cm')->nullable();
            $table->text('growth_notes')->nullable();
            $table->text('developmental_observations')->nullable(); // ملاحظات عالحركة والنطق والتفاعل
            $table->text('allergies')->nullable();
            $table->text('doctor_notes')->nullable();
            $table->enum('feeding_type', ['natural', 'formula', 'mixed'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_records');
    }
};
