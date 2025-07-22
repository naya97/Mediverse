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
        Schema::create('vaccination_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('patients')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('vaccine_id')->constrained('vaccines');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments');
            $table->integer('dose_number')->nullable(); // رقم الجرعة (يعني مثلا تاني جرعة تم اخذها)
            $table->text('notes')->nullable();
            $table->boolean('isTaken')->default(false);
            $table->string('when_to_take')->nullable();
            $table->enum('recommended', ['now', 'upcoming'])->default('upcoming')->nullable();
            $table->date('next_vaccine_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccination_records');
    }
};
