<?php

namespace App\Livewire\Videogame\Levels\Ninth;

use App\Models\Level;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\LevelProgress;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.blank')]
class IndexComponent extends Component
{
    public Level $level;
    public LevelProgress $progress;

    public string $stage = 'kaplay';
    public string $codeInput = '';
    public string $feedback = '';
    public float $finalScore = 5.0; // Puntuación fija por completar

    // --- Propiedades para el Puzzle "Innovation Pathway" ---
    const PATHWAY_STAGES = [
        1 => ['title' => 'Descubrimiento del Problema', 'options' => ['Sostenibilidad', 'Educación', 'Bienestar', 'Comunidad']],
        2 => ['title' => 'Enfoque de Ideación', 'options' => ['App Tecnológica', 'Programa Comunitario', 'Producto Físico', 'Servicio Digital']],
        3 => ['title' => 'Método de Validación', 'options' => ['Entrevistas Usuario', 'Encuestas Online', 'Análisis Mercado', 'Prototipo Simple (MVP)']],
        4 => ['title' => 'Siguiente Paso (Refinamiento)', 'options' => ['Mejorar UX/UI', 'Añadir Característica Clave', 'Buscar Socios', 'Validar Precios']],
    ];

    public int $currentStageNum = 1; // Etapa actual (1-4)
    public array $choicesMade = [];  // Almacena ['stage_1' => 'Sostenibilidad', 'stage_2' => 'App Tecnológica', ...]
    public bool $isGameFinished = false;

    // --- mount(), kaplayCompleted(), verifyCode() ---
    // (Ensure they call initializePuzzle when entering web_puzzle)
    public function mount()
    {
        $this->level = Level::firstOrCreate(['level_number' => 9], ['title' => 'Informe y Validación', 'access_code' => 'REPORT']);
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $this->progress = LevelProgress::firstOrCreate(['user_id' => $user->id, 'level_id' => $this->level->id], ['status' => 'locked']);
        $this->stage = match ($this->progress->checkpoint ?? 0) { 0 => 'kaplay', 1 => 'code_input', 2 => 'web_puzzle', default => 'completed' };
        if ($this->stage === 'web_puzzle') { $this->initializePuzzle(); }
        if ($this->progress->status === 'completed') { $this->stage = 'completed'; $this->finalScore = $this->progress->score ?? 5.0; }
    }
    #[On('kaplay-completed')]
    public function kaplayCompleted()
    {
        Log::info('kaplayCompleted method in Ninth triggered!');
        if ($this->progress->checkpoint < 1) { $this->progress->checkpoint = 1; $this->progress->save(); }
        $this->stage = 'code_input';
    }
    public function verifyCode()
    {
        if (strtoupper($this->codeInput) === $this->level->access_code) {
            if ($this->progress->checkpoint < 2) { $this->progress->checkpoint = 2; $this->progress->save(); }
            $this->stage = 'web_puzzle';
            $this->initializePuzzle();
        } else {
            $this->feedback = 'Código incorrecto.'; $this->codeInput = '';
        }
    }

    // --- Lógica del Puzzle "Innovation Pathway" ---
    public function initializePuzzle()
    {
        $this->currentStageNum = 1;
        $this->choicesMade = [];
        $this->isGameFinished = false;
        $this->finalScore = 5.0;
    }

    // Se llama al hacer clic en una opción de la etapa actual
    public function selectChoice(int $stageNum, string $choice)
    {
        // Solo permite seleccionar en la etapa actual y si el juego no ha terminado
        if ($this->isGameFinished || $stageNum !== $this->currentStageNum) {
            return;
        }

        // Guarda la elección
        $this->choicesMade['stage_'.$stageNum] = $choice;

        // Avanza a la siguiente etapa o finaliza
        if ($this->currentStageNum < count(self::PATHWAY_STAGES)) {
            $this->currentStageNum++;
        } else {
            $this->finishPuzzle();
        }
    }

    public function finishPuzzle()
    {
        if ($this->isGameFinished) return;
        $this->isGameFinished = true;
        Log::info('Puzzle Finished!');

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
        // Pasa las constantes y el estado a la vista
        return view('livewire.videogame.levels.ninth.index-component', [
            'pathwayStages' => self::PATHWAY_STAGES,
        ]);
    }
}