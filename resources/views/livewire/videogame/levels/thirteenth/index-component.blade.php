<div class="w-full h-screen bg-slate-900 text-white flex flex-col items-center justify-center p-4">

    {{-- FASE 1: KAPLAY --}}
    @if ($stage === 'kaplay')
        <div wire:ignore class="relative w-full max-w-4xl aspect-video shadow-2xl">
            <canvas id="edugamer-canvas" class="w-full h-full"></canvas>
            
            {{-- Controles táctiles simplificados (Solo izquierda/derecha necesarios para este nivel) --}}
            <div class="absolute bottom-4 left-4 flex gap-4">
                <button id="leftBtn" class="w-20 h-20 rounded-full bg-amber-600/70 active:bg-amber-700/90 flex items-center justify-center text-3xl">❮</button>
                <button id="rightBtn" class="w-20 h-20 rounded-full bg-amber-600/70 active:bg-amber-700/90 flex items-center justify-center text-3xl">❯</button>
            </div>
            {{-- Botón de Salto no es necesario pero lo dejamos por consistencia o powerups --}}
            <div class="absolute bottom-4 right-4 flex gap-4">
                 <button id="jumpBtn" class="w-16 h-16 rounded-full bg-emerald-600/70 active:bg-emerald-700/90 text-2xl opacity-50 cursor-not-allowed">⬆️</button>
            </div>
        </div>
    @endif

    {{-- FASE 2: CÓDIGO --}}
    @if ($stage === 'code_input')
        <flux:card class="w-full max-w-sm p-6">
            <flux:heading size="lg">Nivel 13 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>¡Has recolectado el capital semilla! Ingresa el código de seguridad.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" variant="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- FASE 3: PUZZLE DE SELECCIÓN --}}
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
            class="w-full max-w-5xl h-full flex flex-col"
        >
            <flux:card class="flex-grow flex flex-col overflow-hidden bg-slate-900 border-slate-800">
                
                {{-- Header --}}
                <div class="p-6 border-b border-slate-700">
                    <div class="flex justify-between items-start">
                        <div>
                            <flux:heading size="xl" class="text-amber-400">Modelo de Ingresos</flux:heading>
                            <flux:text class="mt-2 text-slate-300">{{ $caseStudy }}</flux:text>
                            <flux:text size="sm" class="mt-1 text-slate-500">Selecciona las 3 fuentes de ingresos más viables.</flux:text>
                        </div>
                        <div :class="{'animate-pulse bg-red-500 text-white': remaining < 60}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                            <flux:icon name="clock" class="h-5 w-5" />
                            <span x-text="formattedTime()" class="font-mono"></span>
                        </div>
                    </div>
                </div>

                {{-- Grid de Opciones --}}
                <div class="p-6 flex-grow overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($this::REVENUE_MODELS as $key => $model)
                            @php
                                $isSelected = in_array($key, $selectedModels);
                                $isValid = $model['valid'];
                            @endphp

                            <button 
                                wire:click="toggleSelection('{{ $key }}')"
                                @if($isGameFinished) disabled @endif
                                class="relative flex flex-col text-left p-6 rounded-xl border-2 transition-all duration-200 h-full
                                {{ $isSelected ? 'border-amber-500 bg-amber-900/20 shadow-[0_0_15px_rgba(245,158,11,0.3)] transform -translate-y-1' : 'border-slate-700 bg-slate-800/50 hover:border-slate-500 hover:bg-slate-800' }}
                                {{ $isGameFinished && $isSelected && $isValid ? 'border-green-500! bg-green-900/20!' : '' }}
                                {{ $isGameFinished && $isSelected && !$isValid ? 'border-red-500! bg-red-900/20!' : '' }}
                                {{ $isGameFinished && !$isSelected ? 'opacity-50' : '' }}
                                "
                            >
                                <flux:heading size="lg" class="mb-2 {{ $isSelected ? 'text-amber-300' : 'text-slate-200' }}">
                                    {{ $model['title'] }}
                                </flux:heading>
                                <flux:text class="text-slate-400 leading-relaxed">
                                    {{ $model['desc'] }}
                                </flux:text>

                                {{-- Indicador de Selección --}}
                                <div class="absolute top-4 right-4">
                                    @if($isGameFinished)
                                        @if($isSelected)
                                            @if($isValid) <flux:icon name="check-circle" class="w-6 h-6 text-green-500" />
                                            @else <flux:icon name="x-circle" class="w-6 h-6 text-red-500" />
                                            @endif
                                        @endif
                                    @elseif($isSelected)
                                        <div class="w-6 h-6 rounded-full bg-amber-500 flex items-center justify-center">
                                            <flux:icon name="check" class="w-4 h-4 text-black" />
                                        </div>
                                    @else
                                        <div class="w-6 h-6 rounded-full border-2 border-slate-600"></div>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Footer --}}
                <div class="p-6 border-t border-slate-700 bg-slate-800/30">
                    <div class="flex justify-between items-center">
                        <flux:text>
                            Seleccionados: <span class="font-bold text-white">{{ count($selectedModels) }}</span> / 3
                        </flux:text>

                        @if($isGameFinished)
                            <div class="flex items-center gap-4">
                                <flux:heading size="lg">Nota: {{ number_format($finalScore, 1) }}</flux:heading>
                                <flux:button wire:click="$set('stage', 'completed')" variant="primary">Continuar</flux:button>
                            </div>
                        @else
                            <flux:button 
                                wire:click="calculateScore" 
                                variant="primary"
                                x-bind:disabled="{{ count($selectedModels) !== 3 ? 'true' : 'false' }}"
                            >
                                Validar Estrategia
                            </flux:button>
                        @endif
                    </div>
                </div>

            </flux:card>
        </div>
    @endif

    {{-- FASE 4: COMPLETADO --}}
    @if ($stage === 'completed')
        <flux:card class="w-full max-w-sm text-center p-6">
            <div class="flex flex-col items-center space-y-4">
                <flux:avatar size="xl" src="https://placehold.co/128x128/34d399/ffffff?text=$$" />
                <flux:heading size="lg">¡Modelo Definido!</flux:heading>
                <flux:text>Has establecido cómo generará dinero tu proyecto.</flux:text>
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