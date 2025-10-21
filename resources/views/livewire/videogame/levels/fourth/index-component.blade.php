<div class="w-full h-screen bg-slate-900 text-white flex flex-col items-center justify-center p-4">

    {{-- FASE 1: MINIJUEGO KAPLAY --}}
    @if ($stage === 'kaplay')
        <div wire:ignore class="relative w-full max-w-4xl aspect-video shadow-2xl">
            <canvas id="edugamer-canvas" class="w-full h-full"></canvas>
            <div class="absolute bottom-4 left-4 flex gap-4">
                <button id="leftBtn" class="w-16 h-16 rounded-full bg-amber-600/70 active:bg-amber-700/90 flex items-center justify-center text-2xl">❮</button>
                <button id="rightBtn" class="w-16 h-16 rounded-full bg-amber-600/70 active:bg-amber-700/90 flex items-center justify-center text-2xl">❯</button>
            </div>
            <div class="absolute bottom-4 right-4 flex gap-4">
                <button id="actionBtn" class="w-16 h-16 rounded-full bg-sky-600/70 active:bg-sky-700/90 text-2xl">💬</button>
                <button id="jumpBtn" class="w-16 h-16 rounded-full bg-emerald-600/70 active:bg-emerald-700/90 text-2xl">⬆️</button>
            </div>
        </div>
    @endif

    {{-- FASE 2: CÓDIGO DE ACCESO --}}
    @if ($stage === 'code_input')
        <flux:card class="w-full max-w-sm p-6">
            <flux:heading size="lg">Nivel 4 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>¡Has recolectado las ideas! Ingresa el código para el reto final.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" color="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- FASE 3: PUZZLE "QUIZ RÁPIDO" (REPLANTEADO) --}}
    @if ($stage === 'web_puzzle')
        <div
            wire:poll.1s="countdown"
            x-data="{
                feedback: null, {{-- 'correct' o 'incorrect' --}}
                userSelection: null,
                remaining: $wire.entangle('timeRemaining').live,
                formattedTime() {
                    if (this.remaining < 0) return '00:00';
                    const minutes = Math.floor(this.remaining / 60);
                    const seconds = this.remaining % 60;
                    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }"
            @answer-feedback.window="
                feedback = $event.detail.correct ? 'correct' : 'incorrect';
                userSelection = $wire.feedbackStatus === 'correct' ? $wire.correctAnswer : $wire.userSelection;
                
                // Shake si es incorrecto
                if (feedback === 'incorrect') {
                    $el.classList.add('animate-shake');
                    setTimeout(() => $el.classList.remove('animate-shake'), 500);
                }
                
                // Espera 1.5 seg y luego pasa a la siguiente pregunta
                setTimeout(() => {
                    @this.dispatch('next-question');
                    feedback = null;
                    userSelection = null;
                }, 1500);
            "
            class="w-full max-w-2xl h-full flex flex-col"
        >
            <flux:card class="flex-grow flex flex-col">
                {{-- Encabezado --}}
                <div class="p-6 border-b border-slate-700">
                    <flux:breadcrumbs>
                        <flux:breadcrumbs.item>Nivel 4</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item>Reto: Técnicas de Creatividad</flux:breadcrumbs.item>
                    </flux:breadcrumbs>
                    <div class="flex justify-between items-center mt-4">
                        <flux:heading size="xl">¿Qué técnica es esta?</flux:heading>
                        <div :class="{'animate-pulse bg-red-500 text-white': remaining < 60}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                            <flux:icon name="clock" class="h-5 w-5" />
                            <span x-text="formattedTime()" class="font-mono"></span>
                        </div>
                    </div>
                </div>
                
                {{-- Contenido del Juego --}}
                <div class="p-6 flex-grow overflow-y-auto">

                    @if(!$isGameFinished)
                        {{-- La Pregunta (Descripción) --}}
                        <div class="mb-8 p-6 bg-slate-800 rounded-lg text-center">
                            <flux:text size="lg" class="font-medium text-white">
                                "{{ $currentQuestion }}"
                            </flux:text>
                        </div>
                        
                        {{-- Las Opciones (Técnicas) --}}
                        <div class="space-y-4">
                            @foreach($answerOptions as $tech)
                                <button
                                    wire:click="selectAnswer('{{ $tech }}')"
                                    x-bind:disabled="feedback !== null"
                                    x-bind:class="{
                                        'bg-green-600! border-green-500! text-white!': feedback === 'correct' && userSelection === '{{ $tech }}',
                                        'bg-red-700! border-red-600! text-white!': feedback === 'incorrect' && userSelection === '{{ $tech }}',
                                        'opacity-50 cursor-not-allowed': feedback !== null && userSelection !== '{{ $tech }}'
                                    }"
                                    class="w-full p-4 text-left rounded-lg border-2 border-slate-700 bg-slate-800/50 hover:bg-slate-700/50 transition-all duration-150 disabled:cursor-not-allowed"
                                >
                                    <flux:text>{{ $tech }}</flux:text>
                                </button>
                            @endforeach
                        </div>
                    @else
                        {{-- Estado Finalizado --}}
                        <div class="h-full flex flex-col items-center justify-center text-center">
                            <flux:heading size="2xl" class="text-green-400">¡Reto Completado!</flux:heading>
                            <flux:text class="mt-4">
                                Has acertado <strong>{{ $correctMatches }} de {{ count(self::DEFINITIONS) }}</strong> preguntas.
                            </flux:text>
                            <flux:text size="lg" class="mt-2">
                                Tu calificación es: <strong>{{ number_format($finalScore, 1) }} / 5.0</strong>
                            </flux:text>
                        </div>
                    @endif

                </div>

                {{-- Footer: Botón de Finalizar --}}
                <div class="p-6 border-t border-slate-700">
                    <div class="flex justify-between items-center">
                        <flux:text>
                            Aciertos: <strong>{{ $correctMatches }} / {{ count(self::DEFINITIONS) }}</strong>
                        </flux:text>
                        
                        {{-- El botón para cambiar de stage solo aparece al final --}}
                        @if($isGameFinished)
                            <flux:button 
                                wire:click="$set('stage', 'completed')" 
                                color="primary">
                                Continuar
                            </flux:button>
                        @endif
                    </div>
                </div>
            </flux:card>
        </div>
    @endif

    {{-- FASE 4: NIVEL COMPLETADO --}}
    @if ($stage === 'completed')
        <flux:card class="w-full max-w-sm text-center p-6">
            <div class="flex flex-col items-center space-y-4">
                <flux:avatar size="xl" src="https://placehold.co/128x128/34d399/ffffff?text=💡" />
                <flux:heading size="lg">¡Felicidades!</flux:heading>
                <flux:text>Has completado el Reto del Nivel 4.</flux:text>
                <div class="pt-4">
                    <flux:text size="sm">Tu calificación es:</flux:text>
                    <flux:heading size="2xl" class="font-bold">{{ number_format($progress->score, 1) }} / 5.0</flux:heading>
                </div>
            </div>
            <div class="mt-6">
                <flux:button href="{{ route('menu.home') }}" class="w-full" color="secondary">
                    Volver al Menú
                </flux:button>
            </div>
        </flux:card>
    @endif

</div>