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
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->foreignId('user_id')->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('clinic_id')->constrained('clinics')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('photo')->nullable();
            $table->string('speciality')->nullable();
            $table->string('professional_title')->nullable();
            $table->float('finalRate')->nullable();  //10*6     4*6      3*6       2*6         1*6
            $table->enum('average_visit_duration', ['10 min', '15 min', '20 min', '30 min', '60 min'])->nullable();
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
