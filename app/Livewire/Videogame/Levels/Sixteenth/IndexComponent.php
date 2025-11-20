<?php

namespace App\Livewire\Videogame\Levels\Sixteenth;

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

    // --- Propiedades para el Puzzle "Directorio de Apoyo" ---
    
    // Casos de uso
    const CASES = [
        [
            'situation' => 'Tienes una idea innovadora pero cero capital. Necesitas recursos no reembolsables para iniciar.',
            'options' => [
                'bancoldex' => 'Bancóldex (Crédito)',
                'fondo_emprender' => 'SENA / Fondo Emprender', // Correcta
                'dian' => 'DIAN',
            ],
            'correct' => 'fondo_emprender',
            'feedback' => 'El Fondo Emprender otorga capital semilla condonable a emprendedores.'
        ],
        [
            'situation' => 'Tu producto es un éxito local y quieres empezar a venderlo en Estados Unidos y Europa.',
            'options' => [
                'procolombia' => 'ProColombia', // Correcta
                'camara_comercio' => 'Cámara de Comercio',
                'supersociedades' => 'Supersociedades',
            ],
            'correct' => 'procolombia',
            'feedback' => 'ProColombia es la entidad encargada de promover las exportaciones y el turismo.'
        ],
        [
            'situation' => 'Necesitas formalizar tu empresa, obtener el NIT y el registro mercantil.',
            'options' => [
                'innpulsa' => 'iNNpulsa',
                'min_tic' => 'MinTIC',
                'camara_comercio' => 'Cámara de Comercio', // Correcta
            ],
            'correct' => 'camara_comercio',
            'feedback' => 'Las Cámaras de Comercio administran los registros públicos y la formalización.'
        ],
        [
            'situation' => 'Buscas acelerar tu Startup tecnológica con mentores y conexión a inversión de alto impacto.',
            'options' => [
                'innpulsa' => 'iNNpulsa Colombia', // Correcta
                'banco_agrario' => 'Banco Agrario',
                'icbf' => 'ICBF',
            ],
            'correct' => 'innpulsa',
            'feedback' => 'iNNpulsa es la agencia de emprendimiento e innovación del Gobierno Nacional.'
        ],
    ];

    public int $currentCaseIndex = 0;
    public int $correctAnswers = 0;
    public bool $isGameFinished = false;
    public ?string $selectedOption = null;
    public ?string $resultStatus = null; // 'correct', 'incorrect'

    // --- Temporizador ---
    const QUIZ_DURATION_SECONDS = 300;
    public int $timeRemaining;

    public function mount()
    {
        $this->level = Level::firstOrCreate(
            ['level_number' => 16],
            ['title' => 'Fomento a Mipymes', 'access_code' => 'SUCCESS']
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
            $this->feedback = 'Código incorrecto. Pista: Éxito en inglés.';
            $this->codeInput = '';
        }
    }

    public function initializePuzzle()
    {
        $this->currentCaseIndex = 0;
        $this->correctAnswers = 0;
        $this->isGameFinished = false;
        $this->resetSelection();
    }

    public function selectOption(string $key)
    {
        if ($this->resultStatus) return; // Evita doble clic

        $this->selectedOption = $key;
        $currentCase = self::CASES[$this->currentCaseIndex];
        
        if ($key === $currentCase['correct']) {
            $this->resultStatus = 'correct';
            $this->correctAnswers++;
        } else {
            $this->resultStatus = 'incorrect';
        }
    }

    public function nextCase()
    {
        $this->resetSelection();
        $this->currentCaseIndex++;

        if ($this->currentCaseIndex >= count(self::CASES)) {
            $this->finishGame();
        }
    }

    private function resetSelection()
    {
        $this->selectedOption = null;
        $this->resultStatus = null;
    }

    public function finishGame()
    {
        $this->isGameFinished = true;
        
        $total = count(self::CASES);
        $this->finalScore = ($total > 0) ? round(($this->correctAnswers / $total) * 5.0, 1) : 0.0;

        $this->progress->score = $this->finalScore;
        $this->progress->status = 'completed';
        $this->progress->checkpoint = 3;
        $this->progress->save();

        // Este es el último nivel, no hay unlockNextLevel, pero podríamos redirigir a un certificado.
    }

    public function render()
    {
        return view('livewire.videogame.levels.sixteenth.index-component');
    }
}