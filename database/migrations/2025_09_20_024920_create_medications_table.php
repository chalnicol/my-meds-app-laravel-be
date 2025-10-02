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
            $table->string('brand_name')->unique();
            $table->string('generic_name');
            $table->string('dosage');
            $table->enum('drug_form', ['Tablet', 'Capsule', 'Liquid', 'Cream', 'Syrup', 'Injection', 'Drops', 'Ointment', 'Suppository', 'Other'])->default('Tablet');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->enum('frequency_type', ['Everyday', 'SpecificDays'])->default('Everyday');
            $table->json('frequency')->nullable(); // Store specific times as JSON
            $table->integer('remaining_stock')->default(0);
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
