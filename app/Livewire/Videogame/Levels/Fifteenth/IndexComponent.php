<?php

namespace App\Livewire\Videogame\Levels\Fifteenth;

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

    // --- Propiedades para el Puzzle "El Pitch Financiero" ---
    
    // Las preguntas de los inversionistas
    const PITCH_QUESTIONS = [
        [
            'investor' => 'El Analítico',
            'dialog' => 'Tu precio es de $50. ¿Cómo llegaste a ese número? No me digas que solo adivinaste.',
            'options' => [
                'a' => 'Miré a la competencia y cobré lo mismo.',
                'b' => 'Sumé mis costos totales, añadí un margen de ganancia y validé el valor percibido.', // Correcta
                'c' => 'Es lo que yo pagaría por el producto.',
            ],
            'correct' => 'b'
        ],
        [
            'investor' => 'La Escéptica',
            'dialog' => 'Si no vendes nada el próximo mes, ¿cuáles gastos tendrás que pagar de todos modos?',
            'options' => [
                'a' => 'Mis Costos Fijos: Arriendo, nómina administrativa e internet.', // Correcta
                'b' => 'Mis Costos Variables: Materia prima y comisiones.',
                'c' => 'Ninguno, si no vendo no gasto.',
            ],
            'correct' => 'a'
        ],
        [
            'investor' => 'El Agresivo',
            'dialog' => '¡Necesito escalar rápido! Si duplicas tus ventas mañana, ¿qué costos subirán proporcionalmente?',
            'options' => [
                'a' => 'El alquiler de la oficina.',
                'b' => 'Los salarios del gerente.',
                'c' => 'Los Costos Variables: Insumos, empaques y envíos.', // Correcta
            ],
            'correct' => 'c'
        ],
        [
            'investor' => 'La Visionaria',
            'dialog' => 'Última pregunta. ¿En qué momento exacto tu negocio deja de perder dinero y empieza a ganar?',
            'options' => [
                'a' => 'Cuando recupero la inversión inicial.',
                'b' => 'En el Punto de Equilibrio: Cuando los Ingresos Totales igualan a los Costos Totales.', // Correcta
                'c' => 'Cuando tengo el primer cliente.',
            ],
            'correct' => 'b'
        ],
    ];

    public int $currentQuestionIndex = 0;
    public int $investorInterest = 50; // Barra de vida (0-100)
    public bool $isGameFinished = false;
    public ?string $lastAnswerStatus = null; // 'correct', 'incorrect'

    // --- Temporizador ---
    const QUIZ_DURATION_SECONDS = 300;
    public int $timeRemaining;

    public function mount()
    {
        $this->level = Level::firstOrCreate(
            ['level_number' => 15],
            ['title' => 'Costos y Precios', 'access_code' => 'PITCH']
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
            $this->finishGame(false); // Se acabó el tiempo
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

    // --- Lógica del Pitch ---

    public function initializePuzzle()
    {
        $this->currentQuestionIndex = 0;
        $this->investorInterest = 50; // Empiezas neutral
        $this->isGameFinished = false;
        $this->finalScore = 0.0;
        $this->lastAnswerStatus = null;
    }

    public function answerQuestion(string $optionKey)
    {
        if ($this->isGameFinished || $this->lastAnswerStatus) return;

        $currentQ = self::PITCH_QUESTIONS[$this->currentQuestionIndex];
        $isCorrect = ($optionKey === $currentQ['correct']);

        if ($isCorrect) {
            $this->investorInterest = min(100, $this->investorInterest + 20); // Sube interés
            $this->lastAnswerStatus = 'correct';
        } else {
            $this->investorInterest = max(0, $this->investorInterest - 25); // Baja interés drásticamente (es un pitch duro)
            $this->lastAnswerStatus = 'incorrect';
        }

        // Si el interés llega a 0, Game Over inmediato (reinicio del puzzle)
        if ($this->investorInterest <= 0) {
            $this->dispatch('pitch-failed'); // Para animaciones
            return; 
        }
    }

    #[On('next-question')]
    public function nextQuestion()
    {
        $this->lastAnswerStatus = null;
        $this->currentQuestionIndex++;

        if ($this->currentQuestionIndex >= count(self::PITCH_QUESTIONS)) {
            $this->finishGame(true);
        }
    }
    
    // Reinicia solo el puzzle si fallan
    public function retryPuzzle()
    {
        $this->initializePuzzle();
    }

    public function finishGame(bool $success)
    {
        $this->isGameFinished = true;

        if ($success) {
            // Calificación basada en el interés final
            $this->finalScore = round(($this->investorInterest / 100) * 5.0, 1);
            
            $this->progress->score = $this->finalScore;
            $this->progress->status = 'completed';
            $this->progress->checkpoint = 3;
            $this->progress->save();

            $this->unlockNextLevel();
        } else {
            $this->finalScore = 0.0; // Falló
        }
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
        return view('livewire.videogame.levels.fifteenth.index-component');
    }
}