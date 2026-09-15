<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('from_doctor_id')->constrained('doctors')->restrictOnDelete();
            $table->foreignId('to_department_id')->constrained('departments')->restrictOnDelete();
            $table->enum('urgency', ['low', 'mid', 'high']);
            $table->text('reason');
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
