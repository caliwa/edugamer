<?php

namespace App\Livewire\Videogame\Levels\Eighth;

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
    public float $finalScore = 0.0; // Cambiado a 0.0, se calculará

    // --- Propiedades para el Puzzle "Ordenar Pasos Design Thinking" ---
    const CORRECT_SEQUENCE = [ 'Empatizar', 'Definir', 'Idear', 'Prototipar', 'Probar', ];
    public array $stepsPool = [];
    public array $correctlyClicked = [];
    public ?string $lastClicked = null;
    public ?bool $clickFeedback = null;
    public bool $isGameFinished = false;

    // --- Temporizador (Reactivado) ---
    const QUIZ_DURATION_SECONDS = 60; // <-- 1 MINUTO
    public int $timeRemaining;

    public function mount()
    {
        $this->level = Level::firstOrCreate(
            ['level_number' => 8],
            ['title' => 'Lanzamiento de Idea (Design Thinking)', 'access_code' => 'LAUNCH']
        );

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $this->progress = LevelProgress::firstOrCreate(
            ['user_id' => $user->id, 'level_id' => $this->level->id],
            ['status' => 'locked']
        );

        $this->stage = match ($this->progress->checkpoint ?? 0) {
            0 => 'kaplay',
            1 => 'code_input',
            2 => 'web_puzzle',
            default => 'completed',
        };

        if ($this->stage === 'web_puzzle') {
            $this->initializePuzzle();
            // Inicializa el timer si entras a web_puzzle
            $this->timeRemaining = self::QUIZ_DURATION_SECONDS - ($this->progress->quiz_seconds_spent ?? 0);
        }
         if ($this->progress->status === 'completed') {
            $this->stage = 'completed';
            $this->finalScore = $this->progress->score ?? 0.0; // Carga score guardado
        }
    }

    // Método Countdown (Reactivado)
    public function countdown()
    {
        // Detiene si no está en el puzzle o ya terminó
        if ($this->stage !== 'web_puzzle' || $this->isGameFinished) return;

        // Incrementa el tiempo gastado
        $this->progress->increment('quiz_seconds_spent');
        // Actualiza el tiempo restante
        $this->timeRemaining = self::QUIZ_DURATION_SECONDS - $this->progress->quiz_seconds_spent;

        // Si se acaba el tiempo, finaliza el juego
        if ($this->timeRemaining <= 0) {
            $this->finishPuzzle(true); // Llama a finishPuzzle indicando que fue por tiempo
        }
    }


    #[On('kaplay-completed')]
    public function kaplayCompleted()
    {
        if ($this->progress->checkpoint < 1) {
            $this->progress->checkpoint = 1;
            $this->progress->save();
        }
        $this->stage = 'code_input';
    }

    public function verifyCode()
    {
        if (strtoupper($this->codeInput) === $this->level->access_code) {
             if ($this->progress->checkpoint < 2) {
                $this->progress->checkpoint = 2;
                $this->progress->save();
             }
            $this->stage = 'web_puzzle';
            $this->initializePuzzle();
             // Inicializa el timer al verificar código
             $this->timeRemaining = self::QUIZ_DURATION_SECONDS - ($this->progress->quiz_seconds_spent ?? 0);
        } else {
            $this->feedback = 'Código incorrecto.';
            $this->codeInput = '';
        }
    }

    // --- Lógica del Puzzle "Ordenar Pasos" ---

    public function initializePuzzle()
    {
        $this->stepsPool = self::CORRECT_SEQUENCE;
        shuffle($this->stepsPool);
        $this->correctlyClicked = [];
        $this->lastClicked = null;
        $this->clickFeedback = null;
        $this->isGameFinished = false;
        $this->finalScore = 0.0; // Reinicia score
         // Reinicia el tiempo gastado si se reinicia el puzzle
         if ($this->progress) {
             $this->progress->quiz_seconds_spent = 0;
             $this->progress->save();
             $this->timeRemaining = self::QUIZ_DURATION_SECONDS;
         }
    }

    public function clickStep(string $stepName)
    {
        if ($this->isGameFinished || $this->clickFeedback !== null) return;

        $nextCorrectIndex = count($this->correctlyClicked);

        if ($nextCorrectIndex < count(self::CORRECT_SEQUENCE) && self::CORRECT_SEQUENCE[$nextCorrectIndex] === $stepName) {
            // Correcto
            $this->correctlyClicked[] = $stepName;
            $this->lastClicked = $stepName;
            $this->clickFeedback = true;

            if (count($this->correctlyClicked) === count(self::CORRECT_SEQUENCE)) {
                $this->finishPuzzle(false); // Finaliza porque completó la secuencia
            } else {
                 $this->dispatch('reset-feedback');
            }
        } else {
            // Incorrecto
            $this->lastClicked = $stepName;
            $this->clickFeedback = false;
            $this->correctlyClicked = []; // Reinicia secuencia
            $this->dispatch('reset-sequence');
        }
    }

    #[On('reset-feedback-state')]
    public function resetFeedbackState()
    {
        $this->clickFeedback = null;
        $this->lastClicked = null;
    }

    // finishPuzzle ahora acepta un parámetro para saber si fue por tiempo
    public function finishPuzzle(bool $timedOut = false)
    {
        if ($this->isGameFinished) return;
        $this->isGameFinished = true;

        // Calcula el score: 5.0 si completó la secuencia ANTES de acabar el tiempo
        if (!$timedOut && count($this->correctlyClicked) === count(self::CORRECT_SEQUENCE)) {
             $this->finalScore = 5.0;
        } else {
             // Si se acabó el tiempo o no completó, score mínimo
             $this->finalScore = 1.0;
        }

        $this->progress->score = $this->finalScore;
        $this->progress->status = 'completed';
        $this->progress->checkpoint = 3;
        // Guarda el tiempo gastado al finalizar
        $this->progress->quiz_seconds_spent = self::QUIZ_DURATION_SECONDS - max(0, $this->timeRemaining); // Asegura que no sea negativo
        $this->progress->save();

        $this->unlockNextLevel();
    }

    private function unlockNextLevel()
    {
        // Busca el siguiente nivel (8 + 1 = 9)
        $nextLevel = Level::where('level_number', $this->level->level_number + 1)->first();

        // Si lo encuentra y el usuario está autenticado...
        if ($nextLevel && Auth::check()) {
            // ...crea o actualiza el registro de progreso para el usuario, marcándolo como desbloqueado.
            LevelProgress::firstOrCreate(
                ['user_id' => Auth::id(), 'level_id' => $nextLevel->id],
                ['status' => 'unlocked'] // Asegura que esté desbloqueado si se crea
            );
        }
    }

    public function render()
    {
        return view('livewire.videogame.levels.eighth.index-component');
    }
}