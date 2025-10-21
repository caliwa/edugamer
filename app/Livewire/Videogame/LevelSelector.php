<?php

namespace App\Livewire\Videogame;

use App\Models\LevelProgress;
use Flux\Flux;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LevelSelector extends Component
{
    public array $levels = [];
    public array $progressData = [];

    public function mount()
    {
        // El mismo array de niveles que tenías en la vista
        $this->levels = [
            ['number' => 1, 'title' => 'Espíritu Emprendedor', 'description' => 'Introducción a la cultura y motivación del emprendimiento.', 'color' => 'purple', 'icon' => 'sparkles'],
            ['number' => 2, 'title' => 'Opción de Vida', 'description' => 'El ser humano frente a su proyecto de vida y entorno.', 'color' => 'emerald', 'icon' => 'user'],
            ['number' => 3, 'title' => 'Perfil del Emprendedor', 'description' => 'Análisis de emprendedores y empresarios exitosos.', 'color' => 'blue', 'icon' => 'identification'],
            ['number' => 4, 'title' => 'Creatividad', 'description' => 'Herramientas y técnicas para fomentar la generación de ideas.', 'color' => 'amber', 'icon' => 'light-bulb'],
            ['number' => 5, 'title' => 'Investigación de Mercados', 'description' => 'Pasos y elaboración de un estudio de mercado efectivo.', 'color' => 'sky', 'icon' => 'magnifying-glass'],
            ['number' => 6, 'title' => 'Tipos de Innovación', 'description' => 'Explorando la innovación básica, radical e incremental.', 'color' => 'rose', 'icon' => 'beaker'],
            ['number' => 7, 'title' => 'Aplicando la Innovación', 'description' => 'Taller práctico sobre la gestión de la innovación en una empresa.', 'color' => 'indigo', 'icon' => 'wrench-screwdriver'],
            ['number' => 8, 'title' => 'Generación de Ideas', 'description' => 'Diseño de matrices para la identificación de ideas empresariales.', 'color' => 'teal', 'icon' => 'chat-bubble-left-right'],
            ['number' => 9, 'title' => 'Evaluación de Progreso', 'description' => 'Parcial: Mide tus conocimientos y avance en el curso.', 'color' => 'slate', 'icon' => 'academic-cap'],
            ['number' => 10, 'title' => 'Ruta hacia la Idea', 'description' => 'Desarrollo y evaluación de ideas de negocio.', 'color' => 'orange', 'icon' => 'map'],
            ['number' => 11, 'title' => 'Modelo de Empresa', 'description' => 'Desarrollo del concepto y modelo de negocio (CANVAS).', 'color' => 'lime', 'icon' => 'building-office'],
            ['number' => 12, 'title' => 'Caracterización del Mercado', 'description' => 'Identificación del cliente objetivo, mapa de empatía y avatar.', 'color' => 'cyan', 'icon' => 'users'],
            ['number' => 13, 'title' => 'Fuentes de Ingresos', 'description' => 'Determinación de los modelos y fuentes de ingresos del proyecto.', 'color' => 'fuchsia', 'icon' => 'currency-dollar'],
            ['number' => 14, 'title' => 'Redes y Aliados Clave', 'description' => 'Estructura de networking y alianzas estratégicas.', 'color' => 'violet', 'icon' => 'puzzle-piece'],
            ['number' => 15, 'title' => 'Costos y Precios', 'description' => 'Identificación de factores que intervienen en costos y precios.', 'color' => 'pink', 'icon' => 'receipt-percent'],
            ['number' => 16, 'title' => 'Fomento a Mipymes', 'description' => 'Mecanismos de promoción e instituciones de apoyo.', 'color' => 'red', 'icon' => 'rocket-launch'],
        ];

        // Obtenemos todo el progreso del usuario de una sola vez
        $this->progressData = LevelProgress::where('user_id', Auth::id())
            ->with('level:id,level_number')
            ->get()
            ->keyBy('level.level_number') // Lo organizamos por número de nivel para un acceso fácil
            ->toArray();
    }

    /**
     * Un único método para manejar la selección de todos los niveles.
     */
    public function selectLevel(int $levelNumber)
    {
        // Buscamos el progreso para el nivel seleccionado
        $progress = $this->progressData[$levelNumber] ?? null;

        // 1. El Nivel 1 siempre es accesible para empezar
        if ($levelNumber === 1) {
            return $this->redirect('/levels/1');
        }

        // 2. ESTA ES LA LÓGICA CLAVE DE BLOQUEO
        // Si no existe un registro de progreso para ese nivel O si su estado es 'locked'
        if (!$progress || $progress['status'] === 'locked') {
            // Muestra una notificación de error y detiene la ejecución.
            Flux::toast('Nivel bloqueado', 'Debes completar los niveles anteriores para acceder a este.', variant: 'warning');
            return;
        }
        
        // 3. Si pasa la validación, el estudiante puede entrar al nivel.
        return $this->redirect('/levels/' . $levelNumber);
    }
    public function render()
    {
        return view('livewire.videogame.level-selector');
    }
}