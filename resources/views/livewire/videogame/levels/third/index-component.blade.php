<div class="w-full h-screen bg-slate-900 text-white flex flex-col items-center justify-center p-4">

    {{-- FASE 1: MINIJUEGO KAPLAY --}}
    @if ($stage === 'kaplay')
        <div wire:ignore class="relative w-full max-w-4xl aspect-video shadow-2xl">
            <canvas id="edugamer-canvas" class="w-full h-full"></canvas>
            
            {{-- Botones (copiados de los niveles anteriores) --}}
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
            <flux:heading size="lg">Nivel 3 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>¡Has recogido los pergaminos! Ingresa el código para el reto final.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" color="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- FASE 3: PUZZLE "ORDENAR LOS PASOS" --}}
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
            <flux:card class="flex-grow flex flex-col p-6">
                {{-- Encabezado --}}
                <div class="flex justify-between items-center pb-4 border-b border-slate-700">
                    <flux:breadcrumbs>
                        <flux:breadcrumbs.item>Nivel 3</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item>Reto: Plan de Negocios</flux:breadcrumbs.item>
                    </flux:breadcrumbs>
                    <div :class="{'animate-pulse bg-red-500 text-white': remaining < 60}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                        <flux:icon name="clock" class="h-5 w-5" />
                        <span x-text="formattedTime()" class="font-mono"></span>
                    </div>
                </div>
                
                {{-- Juego de Ordenar --}}
                <div class="flex-grow grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
                    
                    {{-- Columna 1: Pasos Disponibles --}}
                    <div class="space-y-4">
                        <flux:heading size="md">Pasos Disponibles</flux:heading>
                        <flux:text size="sm">Haz clic en los pasos en el orden correcto.</flux:text>
                        <div class="space-y-3">
                            @foreach($availableSteps as $step)
                                <flux:button 
                                    wire:click="addStep('{{ $step }}')" 
                                    wire:loading.attr="disabled"
                                    color="secondary" 
                                    class="w-full text-left justify-start">
                                    {{ $step }}
                                </flux:button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Columna 2: Tu Orden --}}
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <flux:heading size="md">Tu Orden</flux:heading>
                            <flux:button 
                                wire:click="resetSteps"
                                wire:loading.attr="disabled"
                                color="danger" 
                                variant="primary" 
                                size="sm">
                                Reiniciar
                            </flux:button>
                        </div>
                        
                        @if (empty($selectedSteps))
                            <div class="h-full flex items-center justify-center rounded-lg border-2 border-dashed border-slate-600 text-slate-500 p-8">
                                <flux:text>Tu orden aparecerá aquí...</flux:text>
                            </div>
                        @else
                            <ol class="list-decimal list-inside space-y-3">
                                @foreach($selectedSteps as $index => $step)
                                    <li class="p-3 rounded-md bg-slate-800 text-white font-medium">
                                        {{ $index + 1 }}. {{ $step }}
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </div>
                </div>

                {{-- Footer: Botón de Finalizar --}}
                <div class="pt-6 border-t border-slate-700 mt-8">
                    @php
                        $isQuizComplete = count($selectedSteps) === count($this::CORRECT_ORDER);
                    @endphp
                    <flux:button 
                        wire:click="calculateScore" 
                        {{-- :disabled="!{{ $isQuizComplete ? 'true' : 'false' }}" --}}
                        class="w-full" 
                        variant="primary">
                        Finalizar y Calificar
                    </flux:button>
                </div>
            </flux:card>
        </div>
    @endif

    {{-- FASE 4: NIVEL COMPLETADO --}}
    @if ($stage === 'completed')
        <flux:card class="w-full max-w-sm text-center p-6">
            <div class="flex flex-col items-center space-y-4">
                <flux:avatar size="xl" src="https://placehold.co/128x128/34d399/ffffff?text=🎉" />
                <flux:heading size="lg">¡Felicidades!</flux:heading>
                <flux:text>Has completado el Reto del Nivel 3.</flux:text>
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