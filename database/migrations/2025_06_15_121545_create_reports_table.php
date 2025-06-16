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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->enum('type', ['Technical issue', 'Offense', 'Privacy violation', ' Poor cleanliness', 'Bad experience', 'Billing issue', 'Mismanagement', 'Misdiagnosis', 'Unclear instructions', 'other'])->default('other');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
