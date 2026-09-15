<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('actor_type');
            $table->string('actor_name');
            $table->string('action');
            $table->json('meta')->nullable();
            $table->dateTime('logged_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
