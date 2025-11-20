<?php

namespace App\Livewire\Videogame\Levels\Twelfth;

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

    // --- Propiedades para el Puzzle "Mapa de Empatía" ---
    
    // Los 4 Cuadrantes
    const QUADRANTS = [
        'think_feel' => ['label' => '¿Qué Piensa y Siente?', 'icon' => 'heart', 'color' => 'rose'],
        'see' => ['label' => '¿Qué Ve?', 'icon' => 'eye', 'color' => 'blue'],
        'hear' => ['label' => '¿Qué Oye?', 'icon' => 'speaker-wave', 'color' => 'amber'],
        'say_do' => ['label' => '¿Qué Dice y Hace?', 'icon' => 'chat-bubble-left-right', 'color' => 'emerald'],
    ];

    // Las afirmaciones a clasificar
    const STATEMENTS = [
        ['id' => 1, 'text' => '"Me preocupa no tener suficiente dinero para fin de mes."', 'correct' => 'think_feel'],
        ['id' => 2, 'text' => 'Ve ofertas de la competencia en Instagram y TikTok.', 'correct' => 'see'],
        ['id' => 3, 'text' => 'Sus amigos le dicen que esa marca es de mala calidad.', 'correct' => 'hear'],
        ['id' => 4, 'text' => '"Recomendaré este producto a mis colegas."', 'correct' => 'say_do'],
        ['id' => 5, 'text' => 'Siente frustración cuando la app es lenta.', 'correct' => 'think_feel'],
        ['id' => 6, 'text' => 'Asiste a conferencias sobre tecnología.', 'correct' => 'say_do'],
    ];

    public array $statementQueue = []; 
    public ?array $currentStatement = null;
    public int $correctCount = 0;
    public int $totalStatements = 0;
    
    public ?string $lastResult = null; // 'correct' | 'incorrect'
    public bool $isGameFinished = false;

    // --- Temporizador ---
    const QUIZ_DURATION_SECONDS = 300;
    public int $timeRemaining;

    public function mount()
    {
        $this->level = Level::firstOrCreate(
            ['level_number' => 12],
            ['title' => 'Caracterización del Mercado', 'access_code' => 'AVATAR']
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
        $this->statementQueue = self::STATEMENTS;
        shuffle($this->statementQueue);
        $this->totalStatements = count(self::STATEMENTS);
        $this->correctCount = 0;
        $this->isGameFinished = false;
        $this->finalScore = 0.0;
        
        $this->nextCard();
    }

    public function nextCard()
    {
        $this->currentStatement = array_pop($this->statementQueue);
        
        if (!$this->currentStatement) {
            $this->finishGame();
        }
    }

    public function classify(string $quadrantKey)
    {
        if ($this->isGameFinished || !$this->currentStatement) return;

        if ($this->currentStatement['correct'] === $quadrantKey) {
            $this->correctCount++;
            $this->lastResult = 'correct';
        } else {
            $this->lastResult = 'incorrect';
        }

        // Despacha evento para animación
        $this->dispatch('empathy-result', result: $this->lastResult);
    }

    #[On('load-next-statement')]
    public function loadNext()
    {
        $this->lastResult = null;
        $this->nextCard();
    }

    public function finishGame()
    {
        $this->isGameFinished = true;
        $this->currentStatement = null;

        $this->finalScore = ($this->totalStatements > 0) 
                          ? round(($this->correctCount / $this->totalStatements) * 5.0, 1) 
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
        return view('livewire.videogame.levels.twelfth.index-component');
    }
}