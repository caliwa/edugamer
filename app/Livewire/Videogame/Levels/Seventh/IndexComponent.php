<?php

namespace App\Livewire\Videogame\Levels\Seventh;

use App\Models\Level;
use Livewire\Component;
use Livewire\Attributes\On;
// Removed: use Livewire\Attributes\Computed; 
use App\Models\LevelProgress;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.blank')]
class IndexComponent extends Component
{
    public Level $level;
    public LevelProgress $progress;

    public string $stage = 'kaplay';
    public string $codeInput = '';
    public string $feedback = '';
    public float $finalScore = 0.0;

    // --- Propiedades para el Puzzle "Strategy Builder" (Click Version) ---

    // 1. Las categorías (slots)
    const CATEGORIES = [
        'vision' => 'Visión y Metas',
        'generation' => 'Generación de Ideas',
        'selection' => 'Selección y Priorización',
        'implementation' => 'Implementación',
        'culture' => 'Cultura y Personas',
        'measurement' => 'Medición',
    ];

    // 2. Los componentes (opciones) y a qué categoría pertenecen
    const COMPONENTS = [
        'Definir objetivos claros de innovación (ej: % ingresos x nuevos servicios).' => 'vision',
        'Crear programa de "intraemprendimiento".' => 'culture',
        'Establecer "embudo de ideas" con criterios de evaluación.' => 'selection',
        'Formar equipos multifuncionales para pilotos.' => 'implementation',
        'Realizar "hackathons" o lluvias de ideas periódicas.' => 'generation',
        'Medir el ROI de proyectos de innovación.' => 'measurement',
        'Fomentar la colaboración externa (Open Innovation).' => 'generation',
        'Capacitar en metodologías ágiles.' => 'culture',
        // --- Distractores ---
        'Reducir costos operativos generales.' => 'distractor',
        'Actualizar software de contabilidad.' => 'distractor',
        'Contratar más personal de ventas.' => 'distractor',
        'Cumplir normativa legal.' => 'distractor',
    ];

    // 3. Estado del juego (Modificado)
    public array $componentPool = []; // Componentes disponibles
    public array $assignedComponents = []; // ['vision' => '...', 'generation' => null, ...]
    public ?string $selectedCategoryKey = null; // <-- Guarda la categoría activa
    public bool $isGameFinished = false;

    // --- Temporizador ---
    const QUIZ_DURATION_SECONDS = 360; // 6 minutos
    public int $timeRemaining;

    // !! COMPUTED PROPERTY REMOVED !!

    public function mount()
    {
        $this->level = Level::firstOrCreate(
            ['level_number' => 7],
            ['title' => 'Estrategia de Innovación', 'access_code' => 'STRATEGY']
        );
        
        $user = Auth::user();
        if (!$user) return;

        $this->progress = LevelProgress::firstOrCreate(
            ['user_id' => $user->id, 'level_id' => $this->level->id],
            ['status' => 'locked'] 
        );

        $this->stage = match ($this->progress->checkpoint) {
            0 => 'kaplay',
            1 => 'code_input',
            2 => 'web_puzzle',
            default => 'completed',
        };

        if ($this->stage === 'web_puzzle') {
            $this->initializePuzzle();
            $this->timeRemaining = self::QUIZ_DURATION_SECONDS - $this->progress->quiz_seconds_spent;
        }
    }

    public function countdown()
    {
        if ($this->stage !== 'web_puzzle' || $this->isGameFinished) return;
        
        $this->progress->increment('quiz_seconds_spent');
        $this->timeRemaining = self::QUIZ_DURATION_SECONDS - $this->progress->quiz_seconds_spent;

        if ($this->timeRemaining <= 0) {
            $this->calculateScore();
        }
    }

    #[On('kaplay-completed')]
    public function kaplayCompleted()
    {
        $this->progress->checkpoint = 1;
        $this->progress->save();
        $this->stage = 'code_input';
    }

    public function verifyCode()
    {
        if (strtoupper($this->codeInput) === $this->level->access_code) {
            $this->progress->checkpoint = 2;
            $this->progress->save();
            $this->stage = 'web_puzzle';
            $this->initializePuzzle();
            $this->timeRemaining = self::QUIZ_DURATION_SECONDS - $this->progress->quiz_seconds_spent;
        } else {
            $this->feedback = 'Código incorrecto. Intenta de nuevo.';
            $this->codeInput = '';
        }
    }

    // --- Lógica del Puzzle "Strategy Builder" (Click Version) ---

    public function initializePuzzle()
    {
        $this->componentPool = array_keys(self::COMPONENTS);
        shuffle($this->componentPool);
        $this->assignedComponents = array_fill_keys(array_keys(self::CATEGORIES), null);
        $this->selectedCategoryKey = null; // Ninguna categoría seleccionada al inicio
        $this->isGameFinished = false;
        $this->finalScore = 0.0;
    }

    // Se llama al hacer clic en una Categoría (Slot)
    public function selectCategory(string $categoryKey)
    {
        if ($this->isGameFinished) return;

        // Si ya hay algo asignado a esta categoría, primero desasígnalo
        if ($this->assignedComponents[$categoryKey]) {
            $this->unassignFromCategory($categoryKey);
            $this->selectedCategoryKey = null; // Deselecciona la categoría después de vaciarla
        } else {
            // Selecciona esta categoría como el objetivo
            $this->selectedCategoryKey = $categoryKey;
        }
    }

    // Se llama al hacer clic en un Componente del Pool
    public function assignSelectedComponent(string $component)
    {
        if ($this->isGameFinished || !$this->selectedCategoryKey) return; // Necesita una categoría seleccionada

        // 1. Si la categoría seleccionada YA tiene algo, devuélvelo al pool PRIMERO
        if ($this->assignedComponents[$this->selectedCategoryKey]) {
            $this->unassignFromCategory($this->selectedCategoryKey);
        }

        // 2. Asigna el componente clicado a la categoría seleccionada
        $this->assignedComponents[$this->selectedCategoryKey] = $component;

        // 3. Quita el componente del pool
        $this->componentPool = array_diff($this->componentPool, [$component]);

        // 4. Deselecciona la categoría después de asignar (listo para la siguiente)
        $this->selectedCategoryKey = null;
    }

    // Se llama al hacer clic en un Componente YA ASIGNADO a una categoría
    public function unassignFromCategory(string $categoryKey)
    {
        if ($this->isGameFinished) return;

        $componentToUnassign = $this->assignedComponents[$categoryKey];

        if ($componentToUnassign) {
            // Devuélvelo al pool si no está ya
            if (!in_array($componentToUnassign, $this->componentPool)) {
                $this->componentPool[] = $componentToUnassign;
                shuffle($this->componentPool); // Re-desordena el pool
            }
            // Vacía la categoría
            $this->assignedComponents[$categoryKey] = null;
            // Asegúrate que esta categoría no quede seleccionada
            if ($this->selectedCategoryKey === $categoryKey) {
                $this->selectedCategoryKey = null;
            }
        }
    }

    // Se llama con el botón "Finalizar"
    public function calculateScore()
    {
        if ($this->isGameFinished) return;

        $correctAssignments = 0;
        $totalPossibleCorrect = 0;
        
        // Cuenta cuántos componentes CORRECTOS están en su categoría CORRECTA
        foreach ($this->assignedComponents as $categoryKey => $assignedComponent) {
            if ($assignedComponent) {
                // Es un componente válido (no distractor) y está en su categoría?
                if (isset(self::COMPONENTS[$assignedComponent]) && self::COMPONENTS[$assignedComponent] === $categoryKey) {
                    $correctAssignments++;
                }
            }
        }
        
        // Cuenta el total de componentes no-distractores
        foreach (self::COMPONENTS as $component => $category) {
            if ($category !== 'distractor') {
                $totalPossibleCorrect++;
            }
        }
        
        $this->finalScore = ($totalPossibleCorrect > 0) 
                         ? round(($correctAssignments / $totalPossibleCorrect) * 5.0, 1) 
                         : 0.0;
                         
        $this->isGameFinished = true;

        $this->progress->score = $this->finalScore;
        $this->progress->status = 'completed';
        $this->progress->checkpoint = 3;
        $this->progress->save();

        $this->unlockNextLevel();
    }
    
    private function unlockNextLevel()
    {
        // No hay Nivel 7 aún, pero dejamos la lógica
        $nextLevel = Level::where('level_number', $this->level->level_number + 1)->first();
        if ($nextLevel) {
            LevelProgress::firstOrCreate(
                ['user_id' => auth()->id(), 'level_id' => $nextLevel->id],
                ['status' => 'unlocked']
            );
        }
    }

    public function render()
    {
        return view('livewire.videogame.levels.seventh.index-component');
    }
}