<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'pending'])->default('scheduled');
            $table->enum('urgency', ['low', 'mid', 'high'])->default('low');
            $table->string('reason')->nullable();
            $table->foreignId('ai_recommendation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('location')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
