<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            ['number' => 1, 'title' => 'Espíritu Emprendedor'],
            ['number' => 2, 'title' => 'Opción de Vida'],
            ['number' => 3, 'title' => 'Perfil del Emprendedor'],
            ['number' => 4, 'title' => 'Creatividad'],
            ['number' => 5, 'title' => 'Investigación de Mercados'],
            ['number' => 6, 'title' => 'Tipos de Innovación'],
            ['number' => 7, 'title' => 'Aplicando la Innovación'],
            ['number' => 8, 'title' => 'Generación de Ideas'],
            ['number' => 9, 'title' => 'Evaluación de Progreso'],
            ['number' => 10, 'title' => 'Ruta hacia la Idea'],
            ['number' => 11, 'title' => 'Modelo de Empresa'],
            ['number' => 12, 'title' => 'Caracterización del Mercado'],
            ['number' => 13, 'title' => 'Fuentes de Ingresos'],
            ['number' => 14, 'title' => 'Redes y Aliados Clave'],
            ['number' => 15, 'title' => 'Costos y Precios'],
            ['number' => 16, 'title' => 'Fomento a Mipymes'],
        ];

        foreach ($levels as $level) {
            Level::firstOrCreate(
                ['level_number' => $level['number']],
                [
                    'title' => $level['title'],
                    // Genera un código de acceso aleatorio de 5 caracteres
                    // 'access_code' => strtoupper(Str::random(5)),
                    'access_code' => '12345',
                ]
            );
        }
    }
}