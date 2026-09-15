<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('date_of_birth');
            $table->enum('area', ['North District', 'Central', 'South District']);
            $table->string('blood_type')->nullable();
            $table->json('allergies')->nullable();
            $table->json('conditions')->nullable();
            $table->foreignId('family_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
