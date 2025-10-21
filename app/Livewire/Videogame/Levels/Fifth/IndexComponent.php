<?php

namespace App\Livewire\Videogame\Levels\Fifth;

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

    // --- Propiedades para el Puzzle "Panel de Casos" ---

    // 1. La Matriz de Lógica (sin cambios)
    const MATRIX = [
        'Marketing' => [
            'Bajas ventas online' => [
                'options' => ['Brainstorming', 'Análisis FODA', 'Reporte Financiero'],
                'correct' => 'Brainstorming'
            ],
            'Poca interacción en redes' => [
                'options' => ['SCAMPER', 'Diagrama de Gantt', 'Mapas Mentales'],
                'correct' => 'SCAMPER'
            ]
        ],
        'Recursos Humanos' => [
            'Alta rotación de personal' => [
                'options' => ['Mapas Mentales', 'Balance General', '¿Qué pasaría si...?'],
                'correct' => 'Mapas Mentales'
            ],
            'Proceso de contratación lento' => [
                'options' => ['¿Qué pasaría si...?', 'Encuesta de Clima', 'Análisis de Flujo'],
                'correct' => '¿Qué pasaría si...?'
            ]
        ],
        'Operaciones' => [
            'Entregas de productos llegan tarde' => [
                'options' => ['Análisis Causa-Raíz', 'Brainstorming', 'Focus Group'],
                'correct' => 'Análisis Causa-Raíz'
            ]
        ]
    ];
    
    // 2. NUEVAS Propiedades de Estado
    public array $allCases = [];        // El "Inbox" con todos los casos
    public array $solvedCases = [];     // IDs de casos resueltos
    public array $failedCases = [];     // IDs de casos fallidos
    
    public ?string $currentCaseId = null; // El ID del caso seleccionado
    public ?array $currentCase = null;    // Los datos del caso (área, problema)
    public array $techniqueOptions = []; // Las 3 opciones para el caso
    public ?string $correctAnswer = null;  // La respuesta correcta para el caso
    
    public bool $isGameFinished = false; // Bloquea el juego al finalizar
    public int $totalCases = 0;

    // --- Temporizador ---
    const QUIZ_DURATION_SECONDS = 300;
    public int $timeRemaining;

    public function mount()
    {
        $this->level = Level::firstOrCreate(
            ['level_number' => 5],
            ['title' => 'Solución de Problemas', 'access_code' => 'SOLVE']
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
            $this->calculateFinalScore(); // Llama a la nueva función de score
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

    // --- Lógica del Puzzle "Panel de Casos" ---

    public function initializePuzzle()
    {
        $this->allCases = [];
        $this->solvedCases = [];
        $this->failedCases = [];
        $this->resetCurrentCase();
        $this->isGameFinished = false;
        $this->finalScore = 0.0;
        
        // 1. Aplanar la Matriz en el "Inbox"
        $i = 0;
        foreach (self::MATRIX as $area => $problems) {
            foreach ($problems as $problem => $details) {
                $this->allCases[] = [
                    'id' => "case_{$i}",
                    'area' => $area,
                    'problem' => $problem,
                ];
                $i++;
            }
        }
        
        shuffle($this->allCases); // ¡Desordena los casos!
        $this->totalCases = count($this->allCases);
    }

    // NUEVO: Se llama al hacer clic en un caso del "Inbox"
    public function selectCase(string $caseId)
    {
        // Si el caso ya fue intentado, no hagas nada
        if (in_array($caseId, $this->solvedCases) || in_array($caseId, $this->failedCases)) {
            return;
        }
        
        // Si ya hay un caso activo, no hagas nada (evita doble clic)
        if ($this->currentCaseId) {
            return;
        }

        // Buscar el caso en $allCases
        $foundCase = null;
        foreach ($this->allCases as $case) {
            if ($case['id'] === $caseId) {
                $foundCase = $case;
                break;
            }
        }

        if ($foundCase) {
            $this->currentCaseId = $caseId;
            $this->currentCase = $foundCase;
            
            // Cargar las opciones y la respuesta correcta desde la Matriz
            $area = $foundCase['area'];
            $problem = $foundCase['problem'];
            $this->techniqueOptions = self::MATRIX[$area][$problem]['options'];
            $this->correctAnswer = self::MATRIX[$area][$problem]['correct'];
        }
    }

    // NUEVO: Se llama al hacer clic en una "Técnica" del "Toolbox"
    public function solveCase(string $selectedTechnique)
    {
        // Si no hay un caso activo, no hagas nada
        if (!$this->currentCaseId) {
            return;
        }

        $isCorrect = ($selectedTechnique === $this->correctAnswer);

        if ($isCorrect) {
            $this->solvedCases[] = $this->currentCaseId;
        } else {
            $this->failedCases[] = $this->currentCaseId;
        }
        
        // Avisar al frontend para el "juice" (feedback visual)
        $this->dispatch('case-feedback', correct: $isCorrect, technique: $selectedTechnique);
        
        // Limpiar la "Mesa de Trabajo"
        $this->resetCurrentCase();
        
        // Comprobar si el juego ha terminado
        $this->checkGameFinished();
    }
    
    // Función de ayuda para limpiar la mesa
    private function resetCurrentCase()
    {
        $this->currentCaseId = null;
        $this->currentCase = null;
        $this->techniqueOptions = [];
        $this->correctAnswer = null;
    }
    
    // Función de ayuda para comprobar el fin del juego
    private function checkGameFinished()
    {
        if (count($this->solvedCases) + count($this->failedCases) === $this->totalCases) {
            $this->isGameFinished = true;
            $this->calculateFinalScore();
        }
    }

    // NUEVO: Renombrada de calculateScore a calculateFinalScore
    public function calculateFinalScore()
    {
        if ($this->finalScore > 0.0) return; // Evita doble cálculo

        $score = ($this->totalCases > 0) ? (count($this->solvedCases) / $this->totalCases) * 5.0 : 0.0;
        
        $this->finalScore = round($score, 1);

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
        return view('livewire.videogame.levels.fifth.index-component');
    }
}