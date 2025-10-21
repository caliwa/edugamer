<?php

namespace App\Livewire\Videogame\Levels\Fourth;

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

    // --- Propiedades para el NUEVO Puzzle "Quiz Rápido" ---
    
    // 1. El mapa de respuestas correctas
    const DEFINITIONS = [
        'Brainstorming' => 'Generar muchas ideas en grupo, sin filtros ni críticas.',
        '¿Qué pasaría si...?' => 'Romper las reglas actuales e imaginar un escenario imposible.',
        'Mapas Mentales' => 'Organizar ideas visualmente con ramas y conexiones.',
        'SCAMPER' => 'Modificar una idea existente usando verbos (Sustituir, Combinar...).'
    ];

    // 2. Estado del Quiz
    public array $allQuestions = [];     // Todas las preguntas (descripciones)
    public array $answerOptions = [];    // Las 4 opciones (técnicas)
    
    public int $currentQuestionIndex = 0;
    public ?string $currentQuestion = null; // La descripción actual
    public ?string $correctAnswer = null;   // La técnica correcta
    
    public int $correctMatches = 0;       // Contador de aciertos
    public bool $isGameFinished = false;   // ¡SOLUCIONA EL BUG 5/4!
    public ?string $feedbackStatus = null; // 'correct', 'incorrect'

    // --- Propiedades para el Temporizador ---
    const QUIZ_DURATION_SECONDS = 240;
    public int $timeRemaining;

    public function mount()
    {
        $this->level = Level::firstOrCreate(
            ['level_number' => 4],
            ['title' => 'Generación de Ideas', 'access_code' => 'IDEAS']
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
            $this->finishGame(); // Se acaba el tiempo, finaliza
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

    // --- Lógica del NUEVO Puzzle "Quiz Rápido" ---

    public function initializePuzzle()
    {
        $this->correctMatches = 0;
        $this->currentQuestionIndex = 0;
        $this->isGameFinished = false;
        $this->feedbackStatus = null;
        $this->finalScore = 0.0; // Resetea el score
        
        // Obtenemos las 4 técnicas (opciones)
        $this->answerOptions = array_keys(self::DEFINITIONS);
        
        // Obtenemos las 4 preguntas (descripciones) y las desordenamos
        $questions = self::DEFINITIONS;
        $keys = array_keys($questions);
        shuffle($keys);
        $this->allQuestions = array_combine($keys, array_map(fn($k) => $questions[$k], $keys));

        $this->loadQuestion();
    }

    public function loadQuestion()
    {
        // Resetea el feedback
        $this->feedbackStatus = null;

        // Comprueba si se acabaron las preguntas
        if ($this->currentQuestionIndex >= count($this->allQuestions)) {
            $this->finishGame();
            return;
        }

        // Carga la siguiente pregunta
        $this->correctAnswer = array_keys($this->allQuestions)[$this->currentQuestionIndex];
        $this->currentQuestion = array_values($this->allQuestions)[$this->currentQuestionIndex];
    }

    // Se llama desde un botón en el frontend
    public function selectAnswer(string $selectedTechnique)
    {
        // --- ¡BLOQUEO CONTRA BUG 5/4! ---
        // 1. Si el juego terminó, no hagas nada.
        // 2. Si ya se dio feedback para esta pregunta, no hagas nada (evita doble clic).
        if ($this->isGameFinished || $this->feedbackStatus !== null) {
            return;
        }

        if ($selectedTechnique === $this->correctAnswer) {
            $this->feedbackStatus = 'correct';
            $this->correctMatches++;
        } else {
            $this->feedbackStatus = 'incorrect';
        }

        // Avisa al frontend para el "juice" (feedback visual y delay)
        $this->dispatch('answer-feedback', correct: ($this->feedbackStatus === 'correct'));
    }

    // Se llama desde el frontend (Alpine.js) después de la animación/delay
    #[On('next-question')] 
    public function nextQuestion()
    {
        $this->currentQuestionIndex++;
        $this->loadQuestion();
    }

    public function finishGame()
    {
        $this->isGameFinished = true;
        $this->calculateScore();
    }

    public function calculateScore()
    {
        // --- ¡BLOQUEO CONTRA BUG 5/4! ---
        // Si el score ya se calculó, no lo vuelvas a hacer.
        if ($this->finalScore > 0.0) {
            return;
        }

        $totalPossible = count(self::DEFINITIONS);
        $score = ($totalPossible > 0) ? ($this->correctMatches / $totalPossible) * 5.0 : 0.0;
        
        $this->finalScore = round($score, 1);

        $this->progress->score = $this->finalScore;
        $this->progress->status = 'completed';
        $this->progress->checkpoint = 3;
        $this->progress->save();

        $this->unlockNextLevel();
        // $this->stage = 'completed'; // No cambiamos de stage, solo mostramos el botón
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
        return view('livewire.videogame.levels.fourth.index-component');
    }
}