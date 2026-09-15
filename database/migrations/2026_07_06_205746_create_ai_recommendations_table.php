<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('symptom_report_id')->constrained()->cascadeOnDelete();
            $table->enum('urgency', ['low', 'mid', 'high']);
            $table->decimal('confidence', 5, 2);
            $table->foreignId('recommended_department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('recommended_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->dateTime('suggested_time')->nullable();
            $table->json('reasoning');
            $table->string('diagnosis_hint')->nullable();
            $table->string('model_version')->default('MediCore AI v2.1');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
    }
};
