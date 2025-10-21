<?php

namespace App\Livewire\Videogame\Levels\Third;

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

    // --- Propiedades para el Puzzle "Ordenar Pasos" ---
    const CORRECT_ORDER = [
        'Resumen Ejecutivo',
        'Análisis de Mercado',
        'Plan de Marketing',
        'Plan Operativo',
        'Análisis Financiero',
    ];
    
    // Pasos disponibles que el usuario ve
    public array $availableSteps = []; 
    // Pasos que el usuario ha seleccionado
    public array $selectedSteps = []; 

    // --- Propiedades para el Temporizador ---
    const QUIZ_DURATION_SECONDS = 180; // 3 minutos
    public int $timeRemaining;

    public function mount()
    {
        // Creamos el Nivel 3 si no existe
        $this->level = Level::firstOrCreate(
            ['level_number' => 3],
            ['title' => 'El Plan de Negocios', 'access_code' => 'PLN8B']
        );
        
        $user = Auth::user();
        if (!$user) return;

        $this->progress = LevelProgress::firstOrCreate(
            ['user_id' => $user->id, 'level_id' => $this->level->id],
            ['status' => 'locked'] // Por defecto, bloqueado
        );

        // Lógica de acceso
        if ($this->progress->status === 'locked') {
            // Aquí podrías añadir lógica para redirigir si no ha pasado el nivel 2
            // Por ahora, lo dejamos continuar si la ruta es correcta
        }

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
        if ($this->stage !== 'web_puzzle' || $this->finalScore > 0.0) return;
        
        $this->progress->increment('quiz_seconds_spent');
        $this->timeRemaining = self::QUIZ_DURATION_SECONDS - $this->progress->quiz_seconds_spent;

        if ($this->timeRemaining <= 0) {
            $this->calculateScore(); // Calcula score si se acaba el tiempo
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

    // --- Lógica del Puzzle "Ordenar Pasos" ---

    public function initializePuzzle()
    {
        // Reinicia las listas
        $this->selectedSteps = [];
        // Desordena los pasos para el usuario
        $this->availableSteps = collect(self::CORRECT_ORDER)->shuffle()->all();
    }

    // Añade un paso a la lista de "seleccionados"
    public function addStep(string $step)
    {
        $this->selectedSteps[] = $step;
        // Remueve el paso de "disponibles"
        $this->availableSteps = array_diff($this->availableSteps, [$step]);
    }

    // Reinicia el puzzle
    public function resetSteps()
    {
        $this->initializePuzzle();
    }

    public function calculateScore()
    {
        // Comprueba si el array de pasos seleccionados es idéntico al orden correcto
        if ($this->selectedSteps === self::CORRECT_ORDER) {
            $this->finalScore = 5.0;
        } else {
            $this->finalScore = 1.0; // Puntuación mínima por intento
        }

        $this->progress->score = $this->finalScore;
        $this->progress->status = 'completed';
        $this->progress->checkpoint = 3;
        $this->progress->save();

        $this->unlockNextLevel();
        $this->stage = 'completed';
    }
    
    private function unlockNextLevel()
    {
        // Busca el siguiente nivel (3 + 1 = 4)
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
        return view('livewire.videogame.levels.third.index-component');
    }
}