<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('level_progress', function (Blueprint $table) {
            // Guardará los segundos totales que el usuario ha estado en el quiz
            $table->integer('quiz_seconds_spent')->default(0)->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('level_progress', function (Blueprint $table) {
            $table->dropColumn('quiz_seconds_spent');
        });
    }
};