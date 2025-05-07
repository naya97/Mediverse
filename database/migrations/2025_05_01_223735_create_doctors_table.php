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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('clinic_id')->constrained('clinics')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('photo')->nullable();
            $table->string('speciality')->nullable();
            $table->string('professional_title')->nullable();
            $table->float('finalRate')->nullable();
            $table->time('average_visit_duration')->nullable();
            $table->time('checkup_duration')->nullable();
            $table->float('visit_fee')->nullable();
            $table->string('sign')->nullable();
            $table->integer('experience')->nullable();
            $table->integer('treated')->default(0);
            $table->enum('status', ['available', 'notAvailable'])->default('notAvailable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
