<?php

namespace App\Livewire\Videogame\Levels\Eleventh;

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

    // --- Propiedades para el Puzzle "Clasificador de Modelos" ---
    
    // Las 4 Metodologías (Categorías)
    const METHODOLOGIES = [
        'canvas' => [
            'title' => 'Business Model Canvas',
            'color' => 'blue',
            'icon' => 'squares-plus'
        ],
        'lean' => [
            'title' => 'Lean Startup',
            'color' => 'green',
            'icon' => 'arrow-path'
        ],
        'blueprint' => [
            'title' => 'Service Blueprint',
            'color' => 'indigo',
            'icon' => 'map'
        ],
        'innovation' => [
            'title' => 'Innovation Canvas',
            'color' => 'rose',
            'icon' => 'light-bulb'
        ],
    ];

    // Los conceptos a clasificar
    const CONCEPTS = [
        ['text' => 'Estructura de 9 bloques fundamentales.', 'type' => 'canvas'],
        ['text' => 'Ciclo: Crear - Medir - Aprender.', 'type' => 'lean'],
        ['text' => 'Distingue entre Frontstage y Backstage.', 'type' => 'blueprint'],
        ['text' => 'Uso de Producto Mínimo Viable (MVP).', 'type' => 'lean'],
        ['text' => 'Define la "Línea de Visibilidad".', 'type' => 'blueprint'],
        ['text' => 'Foco en Propuesta de Valor y Socios Clave.', 'type' => 'canvas'],
        ['text' => 'Iterar y Pivotar rápidamente.', 'type' => 'lean'],
        ['text' => 'Mapea la experiencia del cliente paso a paso.', 'type' => 'blueprint'],
    ];

    public array $conceptQueue = []; // Cola de conceptos desordenados
    public ?array $currentConcept = null; // El concepto actual en pantalla
    
    public int $correctCount = 0;
    public int $totalConcepts = 0;
    
    public ?string $lastResult = null; // 'correct', 'incorrect' para feedback visual
    public bool $isGameFinished = false;

    // --- Temporizador ---
    const QUIZ_DURATION_SECONDS = 300;
    public int $timeRemaining;

    public function mount()
    {
        $this->level = Level::firstOrCreate(
            ['level_number' => 11],
            ['title' => 'Modelos Innovadores', 'access_code' => 'MODELS']
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
            $this->finishGame();
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
            $this->feedback = 'Código incorrecto.';
            $this->codeInput = '';
        }
    }

    // --- Lógica del Puzzle ---

    public function initializePuzzle()
    {
        $this->conceptQueue = self::CONCEPTS;
        shuffle($this->conceptQueue);
        $this->totalConcepts = count(self::CONCEPTS);
        $this->correctCount = 0;
        $this->isGameFinished = false;
        $this->finalScore = 0.0;
        
        $this->nextCard();
    }

    public function nextCard()
    {
        $this->currentConcept = array_pop($this->conceptQueue);
        
        if (!$this->currentConcept) {
            $this->finishGame();
        }
    }

    public function classify(string $methodologyKey)
    {
        if ($this->isGameFinished || !$this->currentConcept) return;

        if ($this->currentConcept['type'] === $methodologyKey) {
            $this->correctCount++;
            $this->lastResult = 'correct';
        } else {
            $this->lastResult = 'incorrect';
        }

        // Despacha evento para animación y luego llama a nextCard
        $this->dispatch('classification-result', result: $this->lastResult);
    }

    #[On('load-next')]
    public function loadNext()
    {
        $this->lastResult = null;
        $this->nextCard();
    }

    public function finishGame()
    {
        $this->isGameFinished = true;
        $this->currentConcept = null;

        $this->finalScore = ($this->totalConcepts > 0) 
                          ? round(($this->correctCount / $this->totalConcepts) * 5.0, 1) 
                          : 0.0;

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
        return view('livewire.videogame.levels.eleventh.index-component');
    }
}