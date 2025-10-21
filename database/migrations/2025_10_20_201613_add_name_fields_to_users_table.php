<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hacemos que la cédula sea única y el email opcional
            $table->string('cedula')->unique()->after('password');
            $table->string('email')->nullable()->change();

            // Añadimos los nuevos campos para el nombre
            $table->string('first_name')->after('name');
            $table->string('second_name')->nullable()->after('first_name');
            $table->string('first_surname')->after('second_name');
            $table->string('second_surname')->nullable()->after('first_surname');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cedula', 'first_name', 'second_name', 'first_surname', 'second_surname']);
            $table->string('email')->nullable(false)->change();
        });
    }
};