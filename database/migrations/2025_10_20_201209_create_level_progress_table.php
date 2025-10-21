<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('level_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('level_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('locked'); // locked, unlocked, completed
            $table->integer('checkpoint')->default(0); // 0: kaplay, 1: code, 2: puzzle, etc.
            $table->decimal('score', 3, 1)->nullable(); // Calificación de 0.0 a 5.0
            $table->timestamps();

            $table->unique(['user_id', 'level_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('level_progress');
    }
};