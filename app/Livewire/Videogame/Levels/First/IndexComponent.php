<?php

namespace App\Livewire\Videogame\Levels\First;

use Flux\Flux;
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

    public array $answers = [];
    public array $questions = [];
    public float $finalScore = 0.0;
    
    // El tiempo total del quiz en segundos (5 minutos)
    const QUIZ_DURATION_SECONDS = 300;
    public int $timeRemaining;

    public function mount()
    {
        $this->level = Level::firstOrCreate(
            ['level_number' => 1],
            ['title' => 'Cultura del Espíritu Emprendedor', 'access_code' => 'LXM4A']
        );

        $user = Auth::user();
        if (!$user){
            Flux::toast('Usuario no autenticado. Por favor, inicia sesión.', 'error');
            return;
        } 

        $this->progress = LevelProgress::firstOrCreate(
            ['user_id' => $user->id, 'level_id' => $this->level->id],
            ['status' => 'unlocked']
        );

        if(is_null($this->progress->checkpoint)){
            $this->stage = 'kaplay';
            return;
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
    
    // Esta función será llamada cada segundo por wire:poll
    public function countdown()
    {
        if ($this->stage !== 'web_puzzle') {
            return;
        }
        
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
            $this->timeRemaining = self::QUIZ_DURATION_SECONDS - $this->progress->quiz_seconds_spent;
            $this->initializePuzzle();
        } else {
            $this->feedback = 'Código incorrecto. Intenta de nuevo.';
            $this->codeInput = '';
        }
    }

    public function calculateScore()
    {
        $this->initializePuzzle();

        $correctAnswers = 0;
        foreach ($this->questions as $index => $question) {
            if (isset($this->answers[$index]) && $this->answers[$index] == $question['correct']) {
                $correctAnswers++;
            }
        }

        $this->finalScore = round(($correctAnswers / count($this->questions)) * 5.0, 1);

        $this->progress->score = $this->finalScore;
        $this->progress->status = 'completed';
        $this->progress->checkpoint = 3;
        $this->progress->save();

        $this->unlockNextLevel();
        $this->stage = 'completed';
    }

    private function unlockNextLevel()
    {
        // Busca el siguiente nivel (1 + 1 = 2)
        $nextLevel = Level::where('level_number', $this->level->level_number + 1)->first();

        // Si lo encuentra...
        if ($nextLevel) {
            // ...crea el registro de progreso para tu usuario, marcándolo como desbloqueado.
            LevelProgress::firstOrCreate(
                ['user_id' => auth()->id(), 'level_id' => $nextLevel->id],
                ['status' => 'unlocked']
            );
        }
    }

    public function initializePuzzle()
    {
        $this->questions = [
            ['text' => '¿Qué Ley en Colombia fomenta la cultura del emprendimiento?', 'options' => ['Ley 100', 'Ley 1014', 'Ley 80', 'Ley 50'], 'correct' => 1],
            ['text' => 'La guía didáctica menciona una metodología para la elaboración de Modelos de Negocio, ¿cuál es?', 'options' => ['SCRUM', 'Lean Startup', 'Lienzo CANVAS', 'Design Thinking'], 'correct' => 2],
            ['text' => 'Una actitud del emprendedor es sentir la necesidad constante de...', 'options' => ['Descansar', 'Lograr nuevos retos y objetivos', 'Delegar todo', 'Evitar cambios'], 'correct' => 1],
        ];
    }

    public function render()
    {
        return view('livewire.videogame.levels.first.index-component');
    }
}