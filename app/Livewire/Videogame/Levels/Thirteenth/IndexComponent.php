<?php

namespace App\Livewire\Videogame\Levels\Thirteenth;

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

    // --- Propiedades para el Puzzle "Fuentes de Ingresos" ---
    
    // El Caso de Estudio
    public string $caseStudy = "Estás lanzando una App de Entrenamientos Personalizados y Nutrición.";

    // Las Opciones de Monetización
    const REVENUE_MODELS = [
        'subscription' => [
            'title' => 'Suscripción Mensual',
            'desc' => 'Cobrar una tarifa recurrente por acceso premium.',
            'valid' => true // Correcto
        ],
        'freemium' => [
            'title' => 'Modelo Freemium',
            'desc' => 'Versión base gratis, cobrar por funciones avanzadas.',
            'valid' => true // Correcto
        ],
        'selling_data' => [
            'title' => 'Venta de Datos',
            'desc' => 'Vender información privada de usuarios a terceros.',
            'valid' => false // Incorrecto (Ética/Privacidad)
        ],
        'donations' => [
            'title' => 'Donaciones',
            'desc' => 'Esperar que los usuarios donen voluntariamente.',
            'valid' => false // Incorrecto (No sostenible para una empresa)
        ],
        'in_app_purchases' => [
            'title' => 'Microtransacciones',
            'desc' => 'Vender planes de dieta específicos o rutinas únicas.',
            'valid' => true // Correcto
        ],
        'licensing' => [
            'title' => 'Licenciamiento de Software',
            'desc' => 'Alquilar el código fuente a otras empresas.',
            'valid' => false // Incorrecto (No es el foco B2C)
        ],
    ];

    public array $selectedModels = []; // IDs seleccionados
    public bool $isGameFinished = false;

    // --- Temporizador ---
    const QUIZ_DURATION_SECONDS = 300;
    public int $timeRemaining;

    public function mount()
    {
        $this->level = Level::firstOrCreate(
            ['level_number' => 13],
            ['title' => 'Fuentes de Ingresos', 'access_code' => 'MONEY']
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
            $this->feedback = 'Código incorrecto. Pista: Dinero en inglés.';
            $this->codeInput = '';
        }
    }

    // --- Lógica del Puzzle ---

    public function toggleSelection(string $modelKey)
    {
        if ($this->isGameFinished) return;

        if (in_array($modelKey, $this->selectedModels)) {
            // Deseleccionar
            $this->selectedModels = array_diff($this->selectedModels, [$modelKey]);
        } else {
            // Seleccionar (Máximo 3)
            if (count($this->selectedModels) < 3) {
                $this->selectedModels[] = $modelKey;
            }
        }
    }

    public function calculateScore()
    {
        if ($this->isGameFinished) return;

        $correctPicks = 0;
        
        foreach ($this->selectedModels as $key) {
            if (self::REVENUE_MODELS[$key]['valid']) {
                $correctPicks++;
            }
        }

        // Necesitas 3 correctas para el 5.0
        $this->finalScore = round(($correctPicks / 3) * 5.0, 1);
        
        // Si seleccionó menos de 3, penaliza
        if (count($this->selectedModels) < 3) {
            $this->finalScore = 1.0; 
        }

        $this->isGameFinished = true;

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
        return view('livewire.videogame.levels.thirteenth.index-component');
    }
}