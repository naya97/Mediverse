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
        Schema::create('patient_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable()
                ->constrained('patients')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('doctor_id')->nullable()
                ->constrained('doctors')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('review_id')->nullable()
                ->constrained('reviews')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_reviews');
    }
};
