<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symptom_report_symptom', function (Blueprint $table) {
            $table->foreignId('symptom_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('symptom_id')->constrained()->cascadeOnDelete();
            $table->primary(['symptom_report_id', 'symptom_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symptom_report_symptom');
    }
};
