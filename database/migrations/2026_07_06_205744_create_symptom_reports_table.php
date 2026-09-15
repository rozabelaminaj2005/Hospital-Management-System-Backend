<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symptom_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->enum('duration', ['hours', 'day', 'days', 'week']);
            $table->unsignedTinyInteger('pain_level')->nullable();
            $table->boolean('used_ai')->default(true);
            $table->enum('status', ['submitted', 'analyzing', 'completed'])->default('submitted');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symptom_reports');
    }
};
