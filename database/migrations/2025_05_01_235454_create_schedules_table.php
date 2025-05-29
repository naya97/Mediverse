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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('doctor_id')->constrained('doctors')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('day');
            $table->enum('Shift', ['morning shift:from 9 AM to 3 PM', 'evening shift:from 3 PM to 9 PM']);
            $table->date('start_leave_date')->nullable();
            $table->date('end_leave_date')->nullable();
            $table->time('start_leave_time')->nullable();
            $table->time('end_leave_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
