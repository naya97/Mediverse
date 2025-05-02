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
            $table->string('speciality');
            $table->string('professional_title');
            $table->float('finalRate');
            $table->time('average_visit_duration');
            $table->time('checkup_duration');
            $table->float('visit_fee');
            $table->string('sign');
            $table->integer('experience');
            $table->integer('treated')->default(0);
            $table->enum('status',['available','notAvailabe']);
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
