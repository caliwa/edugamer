<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('level_progress', function (Blueprint $table) {
            // Guardará la fecha y hora exactas en que finaliza el tiempo para responder.
            $table->timestamp('quiz_ends_at')->nullable()->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('level_progress', function (Blueprint $table) {
            $table->dropColumn('quiz_ends_at');
        });
    }
};