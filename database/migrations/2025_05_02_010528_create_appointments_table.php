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
            $table->enum('status', ['visited', 'cancelled', 'pending'])->default('pending');
            $table->float('price')->default(0);
            $table->string('payment_intent_id')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->integer('reminder_offset')->default(12);
            $table->boolean('reminder_sent')->default(false);
            $table->enum('appointment_type',['visit','vaccination'])->default('visit');
            $table->integer('queue_number')->nullable();
            $table->boolean('is_referral')->default(false);
            $table->foreignId('referring_doctor')
                ->nullable()
                ->constrained('doctors', 'id')
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
        Schema::dropIfExists('appointments');
    }
};
