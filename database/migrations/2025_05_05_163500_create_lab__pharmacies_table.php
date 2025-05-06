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
        Schema::create('lab__pharmacies', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_lab')->default(true);
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('start_time')->nullable();
            $table->string('finish_time')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('latitude', 12, 8)->nullable();
            $table->decimal('longitude', 12, 8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab__pharmacies');
    }
};
