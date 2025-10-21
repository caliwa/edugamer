<?php

namespace App\Livewire\Videogame\Levels\Second;

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

    // --- Propiedades para el Ahorcado ---
    const WORD_TO_GUESS = 'PROYECTO DE VIDA';
    const MAX_MISTAKES = 6;
    public array $lettersGuessed = [];
    public int $mistakes = 0;
    public string $gameStatus = 'playing'; // playing, won, lost

    // --- Propiedades para el Temporizador ---
    const QUIZ_DURATION_SECONDS = 180; // 3 minutos para el ahorcado
    public int $timeRemaining;

    public function mount()
    {
        $this->level = Level::where('level_number', 2)->firstOrFail();
        $user = Auth::user();
        if (!$user) return;

        $this->progress = LevelProgress::firstOrCreate(
            ['user_id' => $user->id, 'level_id' => $this->level->id],
            ['status' => 'locked'] // Por defecto, está bloqueado
        );

        // Lógica de acceso
        if ($this->progress->status === 'locked') {
            // Opcional: Redirigir o mostrar un mensaje de que no tiene acceso.
            // Por ahora, lo dejamos entrar si la ruta es correcta.
        }

        $this->stage = match ($this->progress->checkpoint) {
            0 => 'kaplay',
            1 => 'code_input',
            2 => 'web_puzzle',
            default => 'completed',
        };

        if ($this->stage === 'web_puzzle') {
            $this->timeRemaining = self::QUIZ_DURATION_SECONDS - $this->progress->quiz_seconds_spent;
        }
    }

    public function countdown()
    {
        if ($this->stage !== 'web_puzzle' || $this->gameStatus !== 'playing') return;
        
        $this->progress->increment('quiz_seconds_spent');
        $this->timeRemaining = self::QUIZ_DURATION_SECONDS - $this->progress->quiz_seconds_spent;

        if ($this->timeRemaining <= 0) {
            $this->gameStatus = 'lost';
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
        } else {
            $this->feedback = 'Código incorrecto. Intenta de nuevo.';
            $this->codeInput = '';
        }
    }

    public function guessLetter(string $letter)
    {
        if ($this->gameStatus !== 'playing' || in_array($letter, $this->lettersGuessed)) {
            return;
        }

        $this->lettersGuessed[] = $letter;

        // Comprueba si la letra es correcta ANTES de seguir
        $isCorrect = str_contains(self::WORD_TO_GUESS, $letter);

        if ($isCorrect === false) {
            $this->mistakes++;
            // ¡NUEVO! Despacha un evento de error
            $this->dispatch('letter-guessed', correct: false, letter: $letter);
        } else {
            // ¡NUEVO! Despacha un evento de acierto
            $this->dispatch('letter-guessed', correct: true, letter: $letter);
        }

        // Comprobamos el estado del juego DESPUÉS de actualizar todo
        $this->checkGameState();
    }

    private function checkGameState()
    {
        // Condición de derrota
        if ($this->mistakes >= self::MAX_MISTAKES) {
            $this->gameStatus = 'lost';
            $this->calculateScore();
            return;
        }

        // Condición de victoria
        $wordLetters = array_unique(str_split(str_replace(' ', '', self::WORD_TO_GUESS)));
        if (count(array_intersect($wordLetters, $this->lettersGuessed)) === count($wordLetters)) {
            $this->gameStatus = 'won';
            $this->calculateScore();
        }
    }

    public function calculateScore()
    {
        $this->finalScore = ($this->gameStatus === 'won') ? 5.0 : 1.0;

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

    public function render()
    {
        return view('livewire.videogame.levels.second.index-component');
    }
}