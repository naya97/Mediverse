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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            // $table->foreignId('doctor_id')->constrained('doctors')
            //     ->cascadeOnDelete()
            //     ->cascadeOnUpdate();
            $table->foreignId('schedule_id')->nullable()
                ->constrained('schedules')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->time('timeSelected');
            $table->foreignId('parent_id')->nullable()
                ->constrained('appointments')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->date('reservation_date')->nullable();
            // $table->time('reservation_hour')->nullable();
            $table->enum('status',['visited','canceled','pending'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
