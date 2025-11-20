<?php

namespace App\Livewire\Videogame\Levels\Tenth;

use App\Models\Level;
use Livewire\Component;
use Livewire\Attributes\On;
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

    // --- Propiedades para el Puzzle "Matriz de Impacto vs Esfuerzo" ---
    
    // Las tareas a clasificar
    const TASKS = [
        'automation' => [
            'text' => 'Automatizar correos de bienvenida a clientes.',
            'quadrant' => 'quick_win' // Alto Impacto, Bajo Esfuerzo
        ],
        'ai_core' => [
            'text' => 'Desarrollar un motor de IA propio desde cero.',
            'quadrant' => 'major_project' // Alto Impacto, Alto Esfuerzo
        ],
        'logo_color' => [
            'text' => 'Cambiar levemente el tono de azul del logo.',
            'quadrant' => 'filler' // Bajo Impacto, Bajo Esfuerzo
        ],
        'manual_migration' => [
            'text' => 'Migrar base de datos de 10 años manualmente.',
            'quadrant' => 'thankless' // Bajo Impacto, Alto Esfuerzo
        ],
    ];

    // Definición de los cuadrantes
    const QUADRANTS = [
        'quick_win' => ['label' => 'Quick Wins', 'desc' => 'Alto Impacto / Bajo Esfuerzo'],
        'major_project' => ['label' => 'Proyectos Mayores', 'desc' => 'Alto Impacto / Alto Esfuerzo'],
        'filler' => ['label' => 'Relleno', 'desc' => 'Bajo Impacto / Bajo Esfuerzo'],
        'thankless' => ['label' => 'Tareas Ingratas', 'desc' => 'Bajo Impacto / Alto Esfuerzo'],
    ];

    public array $availableTasks = []; // IDs de las tareas disponibles
    public array $placedTasks = [];    // ['automation' => 'quick_win', ...]
    
    public ?string $selectedTaskId = null; // Tarea seleccionada actualmente
    public bool $isGameFinished = false;

    // --- Temporizador ---
    const QUIZ_DURATION_SECONDS = 300;
    public int $timeRemaining;

    public function mount()
    {
        $this->level = Level::firstOrCreate(
            ['level_number' => 10],
            ['title' => 'Ruta hacia la Idea', 'access_code' => 'ROUTE10']
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

    // --- Lógica del Puzzle ---

    public function initializePuzzle()
    {
        $this->availableTasks = array_keys(self::TASKS);
        shuffle($this->availableTasks);
        $this->placedTasks = [];
        $this->selectedTaskId = null;
        $this->isGameFinished = false;
        $this->finalScore = 0.0;
    }

    public function selectTask(string $taskId)
    {
        if ($this->isGameFinished) return;
        $this->selectedTaskId = $taskId;
    }

    public function assignToQuadrant(string $quadrantId)
    {
        if ($this->isGameFinished || !$this->selectedTaskId) return;

        // Asigna la tarea al cuadrante
        $this->placedTasks[$this->selectedTaskId] = $quadrantId;

        // Quita de disponibles
        $this->availableTasks = array_diff($this->availableTasks, [$this->selectedTaskId]);
        
        // Resetea selección
        $this->selectedTaskId = null;
    }

    public function undoPlacement(string $taskId)
    {
        if ($this->isGameFinished) return;

        // Quita de colocados
        unset($this->placedTasks[$taskId]);

        // Devuelve a disponibles
        $this->availableTasks[] = $taskId;
    }

    public function calculateScore()
    {
        if ($this->isGameFinished) return;

        $correctPlacements = 0;
        
        foreach ($this->placedTasks as $taskId => $placedQuadrant) {
            if (self::TASKS[$taskId]['quadrant'] === $placedQuadrant) {
                $correctPlacements++;
            }
        }
        
        $totalTasks = count(self::TASKS);
        $this->finalScore = ($totalTasks > 0) 
                          ? round(($correctPlacements / $totalTasks) * 5.0, 1) 
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
        return view('livewire.videogame.levels.tenth.index-component');
    }
}