<?php

namespace App\Livewire\Videogame\Levels\Fourteenth;

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

    // --- Propiedades para el Puzzle "Match de Alianzas" ---
    
    // Los Aliados (Izquierda)
    const ALLIES = [
        'supplier' => ['name' => 'Proveedor de Materia Prima', 'icon' => 'truck'],
        'investor' => ['name' => 'Inversionista Ángel', 'icon' => 'currency-dollar'],
        'distributor' => ['name' => 'Cadena de Retail', 'icon' => 'shopping-bag'],
        'mentor' => ['name' => 'Experto en la Industria', 'icon' => 'academic-cap'],
        'influencer' => ['name' => 'Creador de Contenido', 'icon' => 'video-camera'],
    ];

    // Los Roles (Derecha)
    const ROLES = [
        'capital' => 'Aporta recursos financieros para el crecimiento.',
        'supply' => 'Garantiza la calidad y disponibilidad de insumos.',
        'knowledge' => 'Transfiere experiencia y evita errores comunes.',
        'promotion' => 'Genera visibilidad y confianza en la audiencia.',
        'sales' => 'Pone el producto al alcance del cliente final.',
    ];

    // Las Respuestas Correctas
    const CORRECT_MATCHES = [
        'supplier' => 'supply',
        'investor' => 'capital',
        'distributor' => 'sales',
        'mentor' => 'knowledge',
        'influencer' => 'promotion',
    ];

    public array $userMatches = []; // ['supplier' => 'supply', ...]
    public ?string $selectedAlly = null; // Aliado seleccionado actualmente
    public bool $isGameFinished = false;

    // --- Temporizador ---
    const QUIZ_DURATION_SECONDS = 300;
    public int $timeRemaining;

    public function mount()
    {
        $this->level = Level::firstOrCreate(
            ['level_number' => 14],
            ['title' => 'Redes y Aliados Clave', 'access_code' => 'NETWORK']
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
            $this->feedback = 'Código incorrecto. Pista: Red en inglés.';
            $this->codeInput = '';
        }
    }

    // --- Lógica del Puzzle ---

    public function selectAlly(string $allyKey)
    {
        if ($this->isGameFinished) return;
        
        // Si ya está emparejado, permitir desemparejar (limpiar)
        if (isset($this->userMatches[$allyKey])) {
            unset($this->userMatches[$allyKey]);
        }
        
        $this->selectedAlly = $allyKey;
    }

    public function selectRole(string $roleKey)
    {
        if ($this->isGameFinished || !$this->selectedAlly) return;

        // Verificar si este rol ya está asignado a otro aliado y limpiarlo
        $existingAlly = array_search($roleKey, $this->userMatches);
        if ($existingAlly) {
            unset($this->userMatches[$existingAlly]);
        }

        // Asignar
        $this->userMatches[$this->selectedAlly] = $roleKey;
        
        // Limpiar selección para el siguiente turno
        $this->selectedAlly = null;
    }

    public function calculateScore()
    {
        if ($this->isGameFinished) return;

        $correctCount = 0;
        foreach ($this->userMatches as $ally => $role) {
            if (isset(self::CORRECT_MATCHES[$ally]) && self::CORRECT_MATCHES[$ally] === $role) {
                $correctCount++;
            }
        }

        $totalPairs = count(self::CORRECT_MATCHES);
        $this->finalScore = ($totalPairs > 0) 
                          ? round(($correctCount / $totalPairs) * 5.0, 1) 
                          : 0.0;

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
        return view('livewire.videogame.levels.fourteenth.index-component');
    }
}