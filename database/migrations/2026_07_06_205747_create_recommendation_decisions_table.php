<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_recommendation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->restrictOnDelete();
            $table->enum('decision', ['accepted', 'overridden']);
            $table->text('override_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_decisions');
    }
};
