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
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('brandName');
            $table->string('genericName');
            $table->string('dosage');
            // $table->string('dosageUnit')->nullable();
            // $table->enum('timeOfDay', ['Morning', 'Afternoon', 'Evening', 'Night'])->default('Morning');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->enum('frequencyType', ['Everyday', 'SpecificDays'])->default('Everyday');
            $table->json('frequency')->nullable(); // Store specific times as JSON
            $table->json('dailySchedule')->nullable(); // Store selected days as JSON
            $table->integer('remainingStock')->default(0);
            // $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
