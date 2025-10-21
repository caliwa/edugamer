<div class="w-full h-screen bg-slate-900 text-white flex flex-col items-center justify-center p-4">

    {{-- FASE 1: MINIJUEGO KAPLAY --}}
    @if ($stage === 'kaplay')
        <div wire:ignore class="relative w-full max-w-4xl aspect-video shadow-2xl">
            <canvas id="edugamer-canvas" class="w-full h-full"></canvas>
            {{-- Botones del juego --}}
        </div>
    @endif

    {{-- FASE 2: CÓDIGO DE ACCESO --}}
    @if ($stage === 'code_input')
        <flux:card class="w-full max-w-sm p-6">
            <flux:heading size="lg">Nivel 2 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>¡Has recogido las llaves! Ingresa el código para el siguiente reto.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" color="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- FASE 3: AHORCADO WEB GAMIFICADO --}}
    @if ($stage === 'web_puzzle')
        @php
            $alphabet = range('A', 'Z');
            $wordArray = str_split($this::WORD_TO_GUESS);
        @endphp
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
            <flux:card class="flex-grow flex flex-col p-6">
                {{-- Encabezado --}}
                <div class="flex justify-between items-center">
                    <flux:breadcrumbs>
                        <flux:breadcrumbs.item>Nivel 2</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item>Reto: El Ahorcado</flux:breadcrumbs.item>
                    </flux:breadcrumbs>
                    <div :class="{'animate-pulse bg-red-500 text-white': remaining < 60}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                        <flux:icon name="clock" class="h-5 w-5" />
                        <span x-text="formattedTime()" class="font-mono"></span>
                    </div>
                </div>
                
                {{-- Juego del Ahorcado --}}
                <div class="flex-grow grid grid-cols-1 md:grid-cols-2 gap-8 items-center justify-center text-center mt-8">
                    
                    {{-- Dibujo del Ahorcado --}}
                    <div class="flex justify-center items-center">
                        <svg class="w-64 h-64" viewBox="0 0 100 120">
                            {{-- Base y poste --}}
                            <line x1="10" y1="110" x2="90" y2="110" stroke="currentColor" stroke-width="4" />
                            <line x1="30" y1="110" x2="30" y2="10" stroke="currentColor" stroke-width="4" />
                            <line x1="30" y1="10" x2="70" y2="10" stroke="currentColor" stroke-width="4" />
                            <line x1="70" y1="10" x2="70" y2="30" stroke="currentColor" stroke-width="4" />
                            {{-- Partes del cuerpo --}}
                            @if($mistakes > 0) <circle cx="70" cy="40" r="10" stroke="currentColor" stroke-width="3" fill="none" /> @endif
                            @if($mistakes > 1) <line x1="70" y1="50" x2="70" y2="80" stroke="currentColor" stroke-width="3" /> @endif
                            @if($mistakes > 2) <line x1="70" y1="60" x2="55" y2="70" stroke="currentColor" stroke-width="3" /> @endif
                            @if($mistakes > 3) <line x1="70" y1="60" x2="85" y2="70" stroke="currentColor" stroke-width="3" /> @endif
                            @if($mistakes > 4) <line x1="70" y1="80" x2="55" y2="100" stroke="currentColor" stroke-width="3" /> @endif
                            @if($mistakes > 5) <line x1="70" y1="80" x2="85" y2="100" stroke="currentColor" stroke-width="3" /> @endif
                        </svg>
                    </div>

                    {{-- Palabra y Teclado --}}
                    <div class="space-y-8">
                        {{-- Palabra a adivinar --}}
                        <div class="flex justify-center gap-2">
                            @foreach($wordArray as $char)
                                <div class="w-10 h-14 flex items-center justify-center text-3xl font-bold border-b-4 {{ $char === ' ' ? 'border-transparent' : 'border-slate-500' }}">
                                    @if(in_array($char, $lettersGuessed) || $char === ' ')
                                        {{ $char }}
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- Teclado Virtual --}}
                        <div class="flex flex-wrap justify-center gap-2 max-w-md mx-auto">
                            @foreach($alphabet as $letter)
                                <button
                                    wire:click="guessLetter('{{ $letter }}')"
                                    :disabled="{{ in_array($letter, $lettersGuessed) ? 'true' : 'false' }}"
                                    class="w-10 h-10 rounded-md text-lg font-bold bg-slate-700 hover:bg-primary-600 disabled:bg-slate-800 disabled:text-slate-500 disabled:cursor-not-allowed"
                                >
                                    {{ $letter }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Overlay de Resultado --}}
                @if($gameStatus !== 'playing')
                    <div class="absolute inset-0 bg-black/70 flex flex-col justify-center items-center backdrop-blur-sm">
                        <flux:heading size="3xl" class="{{ $gameStatus === 'won' ? 'text-green-400' : 'text-red-400' }}">
                            {{ $gameStatus === 'won' ? '¡Ganaste!' : '¡Perdiste!' }}
                        </flux:heading>
                        <flux:text class="mt-2">La frase era: {{ $this::WORD_TO_GUESS }}</flux:text>
                    </div>
                @endif
            </flux:card>
        </div>
    @endif

    {{-- FASE 4: NIVEL COMPLETADO --}}
    @if ($stage === 'completed')

    <flux:card class="w-full max-w-sm text-center p-6">

    <div class="flex flex-col items-center space-y-4">

    <flux:avatar size="xl" src="https://placehold.co/128x128/34d399/ffffff?text=🎉" />

    <flux:heading size="lg">¡Felicidades!</flux:heading>

    <flux:text>Has completado el Reto del Nivel 1.</flux:text>

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