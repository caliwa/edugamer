<?php

namespace App\Livewire\Videogame\Levels\Sixth;

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

    // --- Propiedades para el Puzzle "Landing Page Builder" ---

    // 1. Contenido disponible (las 5 opciones)
    const CONTENT_POOL = [
        '¡Reduce el desperdicio, salva el planeta!', // Propuesta de Valor (Hero)
        'App que conecta restaurantes con bancos de alimentos.', // Descripción (Info)
        '¡Regístrate ya!', // Llamado a la Acción (CTA)
        'Sección de Precios Detallados', // Distractor
        'Fotos del Equipo Fundador', // Distractor
    ];
    
    // 2. Las respuestas correctas para cada slot
    const CORRECT_ANSWERS = [
        'hero' => '¡Reduce el desperdicio, salva el planeta!',
        'info' => 'App que conecta restaurantes con bancos de alimentos.',
        'cta' => '¡Regístrate ya!',
    ];

    // 3. Propiedades del estado del juego
    public array $contentPool = []; // Opciones restantes
    public ?string $selectedSlot = null; // 'hero', 'info', 'cta'
    
    // Contenido asignado por el usuario
    public ?string $heroContent = null;
    public ?string $infoContent = null;
    public ?string $ctaContent = null;
    
    public bool $isGameFinished = false;

    // --- Temporizador ---
    const QUIZ_DURATION_SECONDS = 300; // 5 minutos
    public int $timeRemaining;

    public function mount()
    {
        $this->level = Level::firstOrCreate(
            ['level_number' => 6],
            ['title' => 'Innovación y Startups (Ruta N)', 'access_code' => 'RUTAN']
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
            $this->initializePuzzle();
            $this->timeRemaining = self::QUIZ_DURATION_SECONDS - $this->progress->quiz_seconds_spent;
        } else {
            $this->feedback = 'Código incorrecto. Intenta de nuevo.';
            $this->codeInput = '';
        }
    }

    // --- Lógica del Puzzle "Landing Page Builder" ---

    public function initializePuzzle()
    {
        $this->contentPool = self::CONTENT_POOL;
        shuffle($this->contentPool); // Desordena las opciones
        
        $this->selectedSlot = 'hero'; // Selecciona el primer slot por defecto
        $this->heroContent = null;
        $this->infoContent = null;
        $this->ctaContent = null;
        
        $this->isGameFinished = false;
        $this->finalScore = 0.0;
    }

    // Se llama al hacer clic en un Slot del Wireframe
    public function selectSlot(string $slotName)
    {
        // Si el slot ya tiene contenido, devuélvelo al pool
        $this->unassignContent($slotName);
        
        $this->selectedSlot = $slotName;
    }
    
    // Se llama al hacer clic en una opción del "Content Pool"
    public function assignContent(string $content)
    {
        if (!$this->selectedSlot) return; // No hay slot seleccionado

        // 1. Quita el contenido del slot actual (si lo hay) y devuélvelo al pool
        $this->unassignContent($this->selectedSlot);

        // 2. Asigna el nuevo contenido al slot seleccionado
        $this->{$this->selectedSlot . 'Content'} = $content;
        
        // 3. Quita el contenido del pool
        $this->contentPool = array_diff($this->contentPool, [$content]);
        
        // 4. Selecciona el siguiente slot vacío (para mejor UX)
        if ($this->selectedSlot === 'hero' && !$this->infoContent) {
            $this->selectedSlot = 'info';
        } elseif ($this->selectedSlot === 'info' && !$this->ctaContent) {
            $this->selectedSlot = 'cta';
        } elseif ($this->selectedSlot === 'cta' && !$this->heroContent) {
            $this->selectedSlot = 'hero';
        } else {
            $this->selectedSlot = null; // Todos llenos
        }
    }
    
    // Función de ayuda para devolver contenido al pool
    private function unassignContent(string $slotName)
    {
        $contentToRemove = $this->{$slotName . 'Content'};
        if ($contentToRemove) {
            // Devuelve el contenido al pool si no está ya
            if (!in_array($contentToRemove, $this->contentPool)) {
                $this->contentPool[] = $contentToRemove;
            }
            // Vacía el slot
            $this->{$slotName . 'Content'} = null;
        }
    }

    // Se llama con el botón "Finalizar"
    public function calculateScore()
    {
        if ($this->isGameFinished) return;

        $score = 0;
        
        if ($this->heroContent === self::CORRECT_ANSWERS['hero']) {
            $score++;
        }
        if ($this->infoContent === self::CORRECT_ANSWERS['info']) {
            $score++;
        }
        if ($this->ctaContent === self::CORRECT_ANSWERS['cta']) {
            $score++;
        }

        // El score es proporcional a los 3 aciertos
        $this->finalScore = round(($score / count(self::CORRECT_ANSWERS)) * 5.0, 1);
        $this->isGameFinished = true;

        $this->progress->score = $this->finalScore;
        $this->progress->status = 'completed';
        $this->progress->checkpoint = 3;
        $this->progress->save();

        $this->unlockNextLevel();
    }
    
    private function unlockNextLevel()
    {
        // No hay Nivel 7 aún, pero dejamos la lógica
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
        return view('livewire.videogame.levels.sixth.index-component');
    }
}