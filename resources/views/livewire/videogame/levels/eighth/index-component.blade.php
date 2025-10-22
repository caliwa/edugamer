<div class="w-full h-screen bg-slate-900 text-white flex flex-col items-center justify-center p-4">

    {{-- FASE 1: MINIJUEGO KAPLAY --}}
    @if ($stage === 'kaplay')
        {{-- ... (código Kaplay Nivel 8 - Asteroids) ... --}}
         <div wire:ignore class="relative w-full max-w-4xl aspect-video shadow-2xl">
            <canvas id="edugamer-canvas" class="w-full h-full"></canvas>
            <div class="absolute bottom-4 left-4 flex gap-4">
                <button id="leftBtn" class="w-16 h-16 rounded-full bg-amber-600/70 active:bg-amber-700/90 flex items-center justify-center text-2xl">🔄 L</button>
                <button id="rightBtn" class="w-16 h-16 rounded-full bg-amber-600/70 active:bg-amber-700/90 flex items-center justify-center text-2xl">R 🔄</button>
            </div>
            <div class="absolute bottom-4 right-4">
                 <button id="thrustBtn" class="w-16 h-16 rounded-full bg-emerald-600/70 active:bg-emerald-700/90 flex items-center justify-center text-2xl">🚀</button>
            </div>
        </div>
    @endif {{-- Closes kaplay --}}

    {{-- FASE 2: CÓDIGO DE ACCESO --}}
    @if ($stage === 'code_input')
        {{-- ... (código code_input sin cambios) ... --}}
        <flux:card class="w-full max-w-sm p-6">
            <flux:heading size="lg">Nivel 8 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>¡Has conectado las ideas! Ingresa el código de lanzamiento.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" variant="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif {{-- Closes code_input --}}

    @if ($stage === 'web_puzzle')
        <div
            wire:poll.1s="countdown" {{-- Reactiva el polling --}}
            {{-- Reactiva x-data para el timer y feedback --}}
            x-data="{
                feedback: $wire.entangle('clickFeedback').live,
                lastClicked: $wire.entangle('lastClicked').live,
                correctlyClicked: $wire.entangle('correctlyClicked').live,
                isFlashing: false,
                remaining: $wire.entangle('timeRemaining').live, // Entrelaza tiempo restante
                formattedTime() { // Función para formatear tiempo
                    if (this.remaining === undefined || this.remaining < 0) return '00:00';
                    const minutes = Math.floor(this.remaining / 60);
                    const seconds = this.remaining % 60;
                    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                },
                isCorrect(stepName) { return this.correctlyClicked.includes(stepName); },
                isFeedbackFlash(stepName) { return this.isFlashing && this.lastClicked === stepName; }
            }"
            {{-- Listeners sin cambios --}}
            @reset-feedback.window="
                isFlashing = true;
                setTimeout(() => {
                    isFlashing = false;
                    $wire.dispatch('reset-feedback-state');
                }, 800); // Duración del flash
            "
            @reset-sequence.window="
                isFlashing = true; // Activa flash rojo
                 setTimeout(() => {
                    isFlashing = false;
                    // Los pasos ya se resetearon en el backend,
                    // el entangle de correctlyClicked hará que desaparezcan los verdes
                    $wire.dispatch('reset-feedback-state');
                }, 800);
            "
            class="w-full max-w-2xl h-full flex flex-col"
        >
            <flux:card class="flex-grow flex flex-col">
                {{-- Encabezado --}}
                <div class="p-6 border-b border-slate-700">
                     <div class="flex justify-between items-center">
                        <flux:breadcrumbs>
                            <flux:breadcrumbs.item>Nivel 8</flux:breadcrumbs.item>
                            <flux:breadcrumbs.item>Reto: Design Thinking</flux:breadcrumbs.item>
                        </flux:breadcrumbs>
                        {{-- Muestra el Timer --}}
                        <div :class="{'animate-pulse bg-red-500 text-white': remaining < 15 && remaining > 0}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                           <flux:icon name="clock" class="h-5 w-5" />
                           <span x-text="formattedTime()" class="font-mono"></span>
                       </div>
                    </div>
                    <flux:heading size="xl" class="mt-4">Ordena los Pasos</flux:heading>
                    <flux:text size="sm">Haz clic en los pasos del Design Thinking en el orden correcto.</flux:text>
                </div>

                {{-- Contenido del Puzzle (sin cambios en la lógica interna) --}}
                <div class="p-6 flex-grow overflow-y-auto">
                     @if($isGameFinished)
                         {{-- Mensaje de Éxito o Tiempo Agotado --}}
                        <div class="h-full flex flex-col items-center justify-center text-center">
                            @if($finalScore >= 5.0)
                                <flux:icon name="check-badge" class="h-16 w-16 text-green-400" />
                                <flux:heading size="2xl" class="mt-4 text-green-400">¡Secuencia Correcta!</flux:heading>
                                <flux:text class="mt-2">Has ordenado los pasos del Design Thinking.</flux:text>
                            @else
                                <flux:icon name="clock" class="h-16 w-16 text-amber-400" />
                                <flux:heading size="2xl" class="mt-4 text-amber-400">¡Tiempo Agotado!</flux:heading>
                                <flux:text class="mt-2">No completaste la secuencia a tiempo.</flux:text>
                            @endif
                             <flux:text size="lg" class="mt-4">Tu calificación: {{ number_format($finalScore, 1) }} / 5.0</flux:text>
                        </div>
                     @else
                         {{-- Botones de Pasos --}}
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($stepsPool as $step)
                                <button
                                    wire:click="clickStep('{{ $step }}')"
                                    x-bind:class="{
                                        'bg-green-700 border-green-600 text-white cursor-default': isCorrect('{{ $step }}') && !isFeedbackFlash('{{ $step }}'),
                                        'bg-green-500 border-green-400 text-white animate-pulse': isFeedbackFlash('{{ $step }}') && feedback === true,
                                        'bg-red-600 border-red-500 text-white animate-pulse': isFeedbackFlash('{{ $step }}') && feedback === false,
                                        'bg-slate-700 border-slate-600 hover:bg-slate-600/80': !isCorrect('{{ $step }}') && !isFeedbackFlash('{{ $step }}')
                                    }"
                                    x-bind:disabled="isCorrect('{{ $step }}') || isFlashing"
                                    class="w-full p-4 rounded-lg border-2 text-center transition-all duration-150 disabled:opacity-80 disabled:cursor-not-allowed"
                                >
                                    <flux:text size="lg" class="font-medium">{{ $step }}</flux:text>
                                </button>
                            @endforeach {{-- Closes foreach --}}
                        </div>
                    @endif {{-- Closes @if($isGameFinished) --}}
                </div>

                {{-- Footer: Botón de Continuar (solo si está terminado) --}}
                <div class="p-4 border-t border-slate-700">
                    <div class="flex justify-end items-center">
                        @if($isGameFinished)
                            <flux:button wire:click="$set('stage', 'completed')" variant="primary">Continuar</flux:button>
                        @else
                            {{-- Muestra el progreso --}}
                            <flux:text>
                                Pasos correctos: <strong x-text="correctlyClicked.length"></strong> / {{ count(self::CORRECT_SEQUENCE) }}
                            </flux:text>
                        @endif
                    </div>
                </div>
            </flux:card>
        </div>
    @endif {{-- Closes web_puzzle --}}

    {{-- FASE 4: NIVEL COMPLETADO --}}
    @if ($stage === 'completed')
        {{-- ... (código FASE 4 sin cambios) ... --}}
         <flux:card class="w-full max-w-sm text-center p-6">
            <div class="flex flex-col items-center space-y-4">
                <flux:avatar size="xl" src="https://placehold.co/128x128/34d399/ffffff?text=🚀" />
                <flux:heading size="lg">¡Felicidades!</flux:heading>
                <flux:text>Has completado el Reto de Lanzamiento.</flux:text>
                <div class="pt-4">
                    <flux:text size="sm">Calificación (Proceso Completado):</flux:text>
                    <flux:heading size="2xl" class="font-bold">{{ number_format($progress->score, 1) }} / 5.0</flux:heading>
                </div>
            </div>
            <div class="mt-6">
                <flux:button href="{{ route('menu.home') }}" class="w-full" variant="subtle">
                    Volver al Menú
                </flux:button>
            </div>
        </flux:card>
    @endif {{-- Closes completed --}}

</div> {{-- Closes main wrapper --}}