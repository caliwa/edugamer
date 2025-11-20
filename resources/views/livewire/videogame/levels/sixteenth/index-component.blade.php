<div class="w-full h-screen bg-slate-900 text-white flex flex-col items-center justify-center p-4">

    {{-- FASE 1: KAPLAY --}}
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

    {{-- FASE 2: CÓDIGO --}}
    @if ($stage === 'code_input')
        <flux:card class="w-full max-w-sm p-6">
            <flux:heading size="lg">Nivel 16 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>Has visitado a los expertos. Ingresa el código final.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" variant="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- FASE 3: PUZZLE INSTITUCIONAL --}}
    @if ($stage === 'web_puzzle')
        <div
            wire:poll.1s="countdown"
            x-data="{
                remaining: $wire.entangle('timeRemaining').live,
                formattedTime() {
                    if (this.remaining < 0) return '00:00';
                    const minutes = Math.floor(this.remaining / 60);
                    const seconds = this.remaining % 60;
                    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }"
            class="w-full max-w-4xl h-full flex flex-col"
        >
            <flux:card class="flex-grow flex flex-col overflow-hidden bg-slate-900 border-slate-800">
                {{-- Header --}}
                <div class="p-6 border-b border-slate-700 flex justify-between items-center">
                    <div>
                        <flux:breadcrumbs>
                            <flux:breadcrumbs.item>Nivel 16</flux:breadcrumbs.item>
                            <flux:breadcrumbs.item>Ecosistema de Apoyo</flux:breadcrumbs.item>
                        </flux:breadcrumbs>
                        <flux:text size="sm" class="mt-1 text-slate-400">Identifica la institución correcta para cada necesidad.</flux:text>
                    </div>
                    <div :class="{'animate-pulse bg-red-500 text-white': remaining < 60}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                        <flux:icon name="clock" class="h-5 w-5" />
                        <span x-text="formattedTime()" class="font-mono"></span>
                    </div>
                </div>

                {{-- Contenido del Quiz --}}
                <div class="flex-grow p-8 flex flex-col items-center justify-center">
                    
                    @if(!$isGameFinished)
                        {{-- Barra de Progreso --}}
                        <div class="w-full max-w-2xl bg-slate-800 h-2 rounded-full mb-8">
                            <div class="bg-primary-500 h-2 rounded-full transition-all duration-300" style="width: {{ ($currentCaseIndex / count($this::CASES)) * 100 }}%"></div>
                        </div>

                        {{-- Pregunta --}}
                        <div class="w-full max-w-2xl text-center mb-10 animate-fade-in">
                            <div class="inline-block p-3 rounded-full bg-slate-800 mb-4">
                                <flux:icon name="building-office-2" class="w-8 h-8 text-primary-400" />
                            </div>
                            <flux:heading size="xl" class="text-white leading-relaxed">
                                "{{ $this::CASES[$currentCaseIndex]['situation'] }}"
                            </flux:heading>
                        </div>

                        {{-- Opciones --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full max-w-3xl">
                            @foreach($this::CASES[$currentCaseIndex]['options'] as $key => $label)
                                <button 
                                    wire:click="selectOption('{{ $key }}')"
                                    @if($resultStatus) disabled @endif
                                    class="p-4 rounded-xl border-2 text-center transition-all duration-200 relative overflow-hidden
                                    {{ $selectedOption === $key && $resultStatus === 'correct' ? 'border-green-500 bg-green-900/20 text-green-100' : '' }}
                                    {{ $selectedOption === $key && $resultStatus === 'incorrect' ? 'border-red-500 bg-red-900/20 text-red-100' : '' }}
                                    {{ !$resultStatus ? 'border-slate-600 bg-slate-800 hover:border-primary-500 hover:bg-slate-700' : 'opacity-60' }}
                                    {{ $key === $this::CASES[$currentCaseIndex]['correct'] && $resultStatus ? 'border-green-500! bg-green-900/20! opacity-100!' : '' }}
                                    "
                                >
                                    <span class="relative z-10 font-medium">{{ $label }}</span>
                                </button>
                            @endforeach
                        </div>

                        {{-- Feedback y Botón Siguiente --}}
                        @if($resultStatus)
                            <div class="mt-8 w-full max-w-2xl p-4 rounded-lg bg-slate-800 border border-slate-700 text-center animate-fade-in-up">
                                <flux:text class="{{ $resultStatus === 'correct' ? 'text-green-400' : 'text-red-400' }} font-bold mb-2">
                                    {{ $resultStatus === 'correct' ? '¡Correcto!' : 'Incorrecto' }}
                                </flux:text>
                                <flux:text size="sm" class="text-slate-300 mb-4">
                                    {{ $this::CASES[$currentCaseIndex]['feedback'] }}
                                </flux:text>
                                <flux:button wire:click="nextCase" variant="primary">Siguiente</flux:button>
                            </div>
                        @endif

                    @else
                        {{-- Final --}}
                        <div class="text-center">
                            <div class="mb-6 inline-flex p-6 rounded-full bg-yellow-500/20">
                                <flux:icon name="trophy" class="w-16 h-16 text-yellow-400" />
                            </div>
                            <flux:heading size="2xl" class="text-white mb-2">¡Curso Completado!</flux:heading>
                            <flux:text class="text-slate-400 mb-6">Has recorrido todo el camino del emprendedor.</flux:text>
                            
                            <div class="grid grid-cols-2 gap-8 mb-8 text-left bg-slate-800 p-6 rounded-xl border border-slate-700">
                                <div>
                                    <flux:text size="sm" class="text-slate-500">Aciertos en este nivel</flux:text>
                                    <flux:heading size="lg" class="text-white">{{ $correctAnswers }} / {{ count($this::CASES) }}</flux:heading>
                                </div>
                                <div>
                                    <flux:text size="sm" class="text-slate-500">Nota Final</flux:text>
                                    <flux:heading size="lg" class="text-primary-400">{{ number_format($finalScore, 1) }}</flux:heading>
                                </div>
                            </div>

                            <flux:button wire:click="$set('stage', 'completed')" variant="primary" class="w-full">Finalizar</flux:button>
                        </div>
                    @endif
                    
                </div>
            </flux:card>
        </div>
    @endif

    {{-- FASE 4: CERTIFICADO / FIN --}}
    @if ($stage === 'completed')
        <flux:card class="w-full max-w-sm text-center p-6">
            <div class="flex flex-col items-center space-y-4">
                <flux:avatar size="xl" src="https://placehold.co/128x128/34d399/ffffff?text=GRAD" />
                <flux:heading size="lg">¡Felicidades, Emprendedor!</flux:heading>
                <flux:text>Has completado los 16 niveles del diplomado.</flux:text>
                <div class="pt-4">
                    <flux:text size="sm">Tu calificación final del nivel:</flux:text>
                    <flux:heading size="2xl" class="font-bold">{{ number_format($progress->score, 1) }} / 5.0</flux:heading>
                </div>
            </div>
            <div class="mt-6">
                <flux:button href="{{ route('menu.home') }}" class="w-full" variant="subtle">
                    Volver al Menú Principal
                </flux:button>
            </div>
        </flux:card>
    @endif

</div>