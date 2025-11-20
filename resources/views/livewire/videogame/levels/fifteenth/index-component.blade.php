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
                {{-- Salto es crucial en este nivel para esquivar gastos --}}
                <button id="jumpBtn" class="w-16 h-16 rounded-full bg-emerald-600/70 active:bg-emerald-700/90 text-2xl">⬆️</button>
            </div>
        </div>
    @endif

    {{-- FASE 2: CÓDIGO --}}
    @if ($stage === 'code_input')
        <flux:card class="w-full max-w-sm p-6">
            <flux:heading size="lg">Nivel 15 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>Balance positivo alcanzado. Prepárate para el Pitch ante los inversionistas.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" variant="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- FASE 3: PUZZLE PITCH --}}
    @if ($stage === 'web_puzzle')
        <div
            wire:poll.1s="countdown"
            x-data="{
                remaining: $wire.entangle('timeRemaining').live,
                interest: $wire.entangle('investorInterest').live,
                status: $wire.entangle('lastAnswerStatus').live,
                formattedTime() {
                    if (this.remaining < 0) return '00:00';
                    const minutes = Math.floor(this.remaining / 60);
                    const seconds = this.remaining % 60;
                    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                },
                continueNext() {
                    setTimeout(() => { @this.dispatch('next-question'); }, 1500);
                }
            }"
            x-init="$watch('status', value => { if(value) continueNext(); })"
            class="w-full max-w-4xl h-full flex flex-col"
        >
            <flux:card class="flex-grow flex flex-col overflow-hidden bg-slate-900 border-slate-800 relative">
                
                {{-- Header --}}
                <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                    <flux:breadcrumbs>
                        <flux:breadcrumbs.item>Nivel 15</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item>El Pitch Financiero</flux:breadcrumbs.item>
                    </flux:breadcrumbs>
                    <div :class="{'animate-pulse bg-red-500 text-white': remaining < 60}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                        <flux:icon name="clock" class="h-5 w-5" />
                        <span x-text="formattedTime()" class="font-mono"></span>
                    </div>
                </div>

                {{-- Área Principal --}}
                <div class="flex-grow flex flex-col p-6 relative z-10">
                    
                    {{-- 1. Barra de Interés del Inversionista --}}
                    <div class="mb-8">
                        <div class="flex justify-between text-sm text-slate-400 mb-2">
                            <span>Interés de los Inversionistas</span>
                            <span x-text="interest + '%'"></span>
                        </div>
                        <div class="w-full bg-slate-700 rounded-full h-4 overflow-hidden">
                            <div 
                                class="h-full transition-all duration-500 ease-out"
                                :class="{
                                    'bg-red-500': interest < 30,
                                    'bg-amber-500': interest >= 30 && interest < 70,
                                    'bg-green-500': interest >= 70
                                }"
                                :style="`width: ${interest}%`"
                            ></div>
                        </div>
                    </div>

                    @if(!$isGameFinished && $investorInterest > 0)
                        
                        {{-- 2. El Inversionista (Pregunta) --}}
                        <div class="flex flex-col md:flex-row gap-6 items-start mb-8 animate-fade-in">
                            <div class="shrink-0 flex flex-col items-center">
                                <div class="w-20 h-20 rounded-full bg-slate-700 border-2 border-slate-500 flex items-center justify-center overflow-hidden">
                                    <flux:icon name="user" variant="solid" class="w-12 h-12 text-slate-400" />
                                </div>
                                <flux:text size="sm" class="mt-2 font-bold text-slate-300">{{ $this::PITCH_QUESTIONS[$currentQuestionIndex]['investor'] }}</flux:text>
                            </div>
                            
                            <div class="relative bg-white dark:bg-slate-800 p-6 rounded-2xl rounded-tl-none border border-slate-600 shadow-lg flex-grow">
                                <flux:heading size="lg" class="text-slate-800 dark:text-slate-100 leading-relaxed">
                                    "{{ $this::PITCH_QUESTIONS[$currentQuestionIndex]['dialog'] }}"
                                </flux:heading>
                                {{-- Triángulo del globo de texto --}}
                                <div class="absolute top-0 -left-2 w-4 h-4 bg-white dark:bg-slate-800 border-l border-t border-slate-600 transform -rotate-45"></div>
                            </div>
                        </div>

                        {{-- 3. Opciones de Respuesta --}}
                        <div class="grid gap-3">
                            @foreach($this::PITCH_QUESTIONS[$currentQuestionIndex]['options'] as $key => $text)
                                <button 
                                    wire:click="answerQuestion('{{ $key }}')"
                                    x-bind:disabled="status !== null"
                                    class="w-full text-left p-4 rounded-xl border-2 transition-all duration-200 group relative overflow-hidden
                                    {{ $lastAnswerStatus === null ? 'border-slate-600 bg-slate-800/50 hover:bg-slate-700 hover:border-primary-500' : '' }}
                                    {{ $lastAnswerStatus === 'correct' && $key === $this::PITCH_QUESTIONS[$currentQuestionIndex]['correct'] ? 'border-green-500 bg-green-900/30' : '' }}
                                    {{ $lastAnswerStatus === 'incorrect' && $key !== $this::PITCH_QUESTIONS[$currentQuestionIndex]['correct'] && $lastAnswerStatus !== null ? 'opacity-50' : '' }} 
                                    "
                                >
                                    <div class="relative z-10 flex items-center">
                                        <div class="w-8 h-8 rounded-full border-2 border-slate-500 flex items-center justify-center mr-4 text-sm font-bold group-hover:border-primary-400 group-hover:text-primary-400 transition-colors">
                                            {{ strtoupper($key) }}
                                        </div>
                                        <flux:text size="lg">{{ $text }}</flux:text>
                                    </div>
                                    
                                    {{-- Feedback Icon --}}
                                    @if($lastAnswerStatus === 'correct' && $key === $this::PITCH_QUESTIONS[$currentQuestionIndex]['correct'])
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-green-500">
                                            <flux:icon name="check-circle" class="w-6 h-6" />
                                        </div>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                    @elseif($investorInterest <= 0)
                         {{-- GAME OVER (Pitch Fallido) --}}
                        <div class="flex-grow flex flex-col items-center justify-center text-center">
                            <div class="w-24 h-24 bg-red-900/50 rounded-full flex items-center justify-center mb-6 animate-bounce">
                                <flux:icon name="hand-thumb-down" class="w-12 h-12 text-red-500" />
                            </div>
                            <flux:heading size="2xl" class="text-red-400 mb-2">Inversión Rechazada</flux:heading>
                            <flux:text class="mb-8 max-w-md">
                                "Tus respuestas demuestran que no conoces tus números. Vuelve cuando tengas claro tu modelo de costos."
                            </flux:text>
                            <flux:button wire:click="retryPuzzle" variant="primary">Intentar de Nuevo</flux:button>
                        </div>

                    @else
                        {{-- ÉXITO (Pitch Aprobado) --}}
                        <div class="flex-grow flex flex-col items-center justify-center text-center">
                            <div class="w-24 h-24 bg-green-900/50 rounded-full flex items-center justify-center mb-6">
                                <flux:icon name="currency-dollar" class="w-12 h-12 text-green-500" />
                            </div>
                            <flux:heading size="2xl" class="text-green-400 mb-2">¡Tenemos un Trato!</flux:heading>
                            <flux:text class="mb-8 max-w-md">
                                Has demostrado dominio sobre tus costos y precios. Los inversionistas están dentro.
                            </flux:text>
                            <flux:button wire:click="$set('stage', 'completed')" variant="primary">Firmar Acuerdo</flux:button>
                        </div>
                    @endif

                </div>

                {{-- Fondo Decorativo --}}
                <div class="absolute inset-0 pointer-events-none opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>

            </flux:card>
        </div>
    @endif

    {{-- FASE 4: COMPLETADO --}}
    @if ($stage === 'completed')
        <flux:card class="w-full max-w-sm text-center p-6">
            <div class="flex flex-col items-center space-y-4">
                <flux:avatar size="xl" src="https://placehold.co/128x128/34d399/ffffff?text=15" />
                <flux:heading size="lg">¡Financiamiento Logrado!</flux:heading>
                <flux:text>Has superado la etapa de costos y precios.</flux:text>
                <div class="pt-4">
                    <flux:text size="sm">Tu calificación es:</flux:text>
                    <flux:heading size="2xl" class="font-bold">{{ number_format($progress->score, 1) }} / 5.0</flux:heading>
                </div>
            </div>
            <div class="mt-6">
                <flux:button href="{{ route('menu.home') }}" class="w-full" variant="subtle">
                    Volver al Menú
                </flux:button>
            </div>
        </flux:card>
    @endif

</div>