<?php

namespace App\Livewire\Videogame\Levels\Third;

use App\Models\Level;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\LevelProgress;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
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

    // --- Propiedades para el Puzzle "Empresarios" ---
    
    // Los 5 correctos
    const CORRECT_ENTREPRENEURS = [
        'Jesús Ramírez Johns',
        'Gabriel Echavarría Misas',
        'Pedro Estrada González',
        'Julio Ernesto Urrea Urrea',
        'José María Acevedo Alzate',
    ];
    
    // Los 5 distractores
    const DISTRACTORS = [
        'Juan Carlos Restrepo',
        'María Elena Jaramillo',
        'Luis Fernando Zuluaga',
        'Ana Lucía Gaviria',
        'Carlos Alberto Arango',
    ];

    // Esta propiedad contendrá los 10 nombres, desordenados
    public array $allEntrepreneurs = []; 
    
    // Esta propiedad guardará las selecciones del usuario
    // #[Locked] // Usamos Locked para que no se pueda manipular desde el frontend
    public array $selectedNames = []; 

    // --- Propiedades para el Temporizador ---
    const QUIZ_DURATION_SECONDS = 180; // 3 minutos
    public int $timeRemaining;

    public function mount()
    {
        $this->level = Level::firstOrCreate(
            ['level_number' => 3],
            ['title' => 'El Plan de Negocios', 'access_code' => 'PLN8B']
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
        if ($this->stage !== 'web_puzzle' || $this->finalScore > 0.0) return;
        
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

    // --- Lógica del Puzzle "Empresarios" ---

    public function initializePuzzle()
    {
        // Fusiona correctos y distractores
        $all = array_merge(self::CORRECT_ENTREPRENEURS, self::DISTRACTORS);
        // Desordena la lista
        $this->allEntrepreneurs = collect($all)->shuffle()->all();
        // Reinicia las selecciones
        $this->selectedNames = [];
    }

    // Método para reiniciar el puzzle
    public function resetPuzzle()
    {
        $this->initializePuzzle();
    }
    
    // Método para actualizar las selecciones (se llama desde el 'updated' hook)
    public function updatedSelectedNames()
    {
        // Mantiene solo los últimos 5 seleccionados si el usuario intenta marcar más
        // CORRECCIÓN: Contamos la propiedad $this->selectedNames directamente
        if (count($this->selectedNames) > 5) {
            
            // Limitamos la propiedad $this->selectedNames
            $this->selectedNames = array_slice($this->selectedNames, -5, 5);
        }
    }

    public function calculateScore()
    {
        // Obtenemos los nombres que el usuario seleccionó y SON correctos
        $correctlySelected = array_intersect($this->selectedNames, self::CORRECT_ENTREPRENEURS);
        
        // Obtenemos los nombres que el usuario seleccionó y NO SON correctos
        $incorrectlySelected = array_diff($this->selectedNames, self::CORRECT_ENTREPRENEURS);

        // --- Lógica de Calificación ---
        // El puntaje máximo SÓLO si:
        // 1. Seleccionó los 5 correctos (count == 5)
        // 2. No seleccionó NINGÚN distractor (count == 0)
        if (count($correctlySelected) === 5 && count($incorrectlySelected) === 0) {
            $this->finalScore = 5.0;
        } else {
            // Cualquier otro caso (4 correctos, o 5 correctos + 1 distractor, etc.)
            // Otorga un puntaje mínimo por el intento.
            // Opcional: podrías hacerlo proporcional (ej: count($correctlySelected) * 1.0)
            $this->finalScore = 1.0;
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