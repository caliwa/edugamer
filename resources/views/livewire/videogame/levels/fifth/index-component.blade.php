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
            <flux:heading size="lg">Nivel 5 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>¡Has recolectado los informes! Ingresa el código para el reto final.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" variant="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- FASE 3: PUZZLE "PANEL DE CASOS" --}}
    @if ($stage === 'web_puzzle')
        <div
            wire:poll.1s="countdown"
            x-data="{
                feedback: null,
                lastTechnique: null,
                remaining: $wire.entangle('timeRemaining').live,
                formattedTime() {
                    if (this.remaining < 0) return '00:00';
                    const minutes = Math.floor(this.remaining / 60);
                    const seconds = this.remaining % 60;
                    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }"
            
            {{-- !! LA CORRECCIÓN ESTÁ AQUÍ !! --}}
            {{-- Se usa 'x-on:case-feedback.window' en lugar de '@case-feedback.window' --}}
            x-on:case-feedback.window="
                feedback = $event.detail.correct ? 'correct' : 'incorrect';
                lastTechnique = $event.detail.technique;
                
                if (feedback === 'incorrect') {
                    $el.querySelector('#mesa-de-trabajo').classList.add('animate-shake');
                    setTimeout(() => $el.querySelector('#mesa-de-trabajo').classList.remove('animate-shake'), 500);
                }
                
                setTimeout(() => {
                    feedback = null;
                    lastTechnique = null;
                }, 1000);
            "
            class="w-full max-w-5xl h-full flex flex-col"
        >
            <flux:card class="flex-grow flex flex-col">
                {{-- Encabezado --}}
                <div class="p-6 border-b border-slate-700">
                    <div class="flex justify-between items-center">
                        <flux:breadcrumbs>
                            <flux:breadcrumbs.item>Nivel 5</flux:breadcrumbs.item>
                            <flux:breadcrumbs.item>Reto: Panel de Casos</flux:breadcrumbs.item>
                        </flux:breadcrumbs>
                        <div :class="{'animate-pulse bg-red-500 text-white': remaining < 60}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                            <flux:icon name="clock" class="h-5 w-5" />
                            <span x-text="formattedTime()" class="font-mono"></span>
                        </div>
                    </div>
                </div>
                
                {{-- Contenido del Juego: 2 Columnas --}}
                <div class="flex-grow grid grid-cols-1 md:grid-cols-2 gap-0 h-full overflow-hidden">
                    
                    {{-- Columna 1: Inbox de Problemas --}}
                    <div class="flex flex-col border-r border-slate-700">
                        <div class="p-4 border-b border-slate-700">
                            <flux:heading size="md">Inbox de Problemas</flux:heading>
                            <flux:text size="sm">Selecciona un caso para analizar.</flux:text>
                        </div>
                        <div class="p-4 flex-grow overflow-y-auto space-y-3">
                            @foreach($allCases as $case)
                                @php
                                    $isSolved = in_array($case['id'], $solvedCases);
                                    $isFailed = in_array($case['id'], $failedCases);
                                    $isActive = $currentCaseId === $case['id'];
                                    
                                    $statusColor = $isSolved ? 'border-green-500 bg-green-900/30' :
                                                   ($isFailed ? 'border-red-500 bg-red-900/30' :
                                                   ($isActive ? 'border-primary-500 bg-primary-900/30' : 
                                                   'border-slate-700 hover:bg-slate-800/50'));
                                    
                                    $cursor = ($isSolved || $isFailed || $isActive) ? 'cursor-default' : 'cursor-pointer';
                                @endphp
                                <button
                                    wire:click="selectCase('{{ $case['id'] }}')"
                                    class="w-full text-left p-3 rounded-lg border-2 transition-all {{ $statusColor }} {{ $cursor }}"
                                    x-bind:disabled="{{ $isSolved || $isFailed || $isActive ? 'true' : 'false' }}"
                                >
                                    <div class="flex items-center">
                                        @if($isSolved) <flux:icon name="check-circle" class="h-5 w-5 text-green-400 mr-2" />
                                        @elseif($isFailed) <flux:icon name="x-circle" class="h-5 w-5 text-red-400 mr-2" />
                                        @elseif($isActive) <flux:icon name="arrow-right-circle" class="h-5 w-5 text-primary-400 mr-2" />
                                        @else <flux:icon name="document" class="h-5 w-5 text-slate-500 mr-2" />
                                        @endif
                                        <flux:text>{{ $case['problem'] }}</flux:text>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Columna 2: Mesa de Trabajo --}}
                    <div id="mesa-de-trabajo" class="flex flex-col transition-transform duration-100">
                        <div class="p-4 border-b border-slate-700">
                            <flux:heading size="md">Mesa de Trabajo</flux:heading>
                        </div>
                        
                        @if($currentCase)
                            <div class="p-6 space-y-6">
                                {{-- Info del Caso --}}
                                <div>
                                    <flux:text size="sm" class="font-semibold text-primary-400">{{ $currentCase['area'] }}</flux:text>
                                    <flux:heading size="lg" class="mt-1">"{{ $currentCase['problem'] }}"</flux:heading>
                                </div>
                                
                                {{-- Toolbox --}}
                                <flux:fieldset>
                                    <flux:legend>Toolbox de Técnicas</flux:legend>
                                    <div class="mt-4 space-y-3">
                                        @foreach($techniqueOptions as $tech)
                                            <button
                                                wire:click="solveCase('{{ $tech }}')"
                                                x-bind:disabled="feedback !== null"
                                                x-bind:class="{
                                                    'bg-green-600! border-green-500! text-white!': feedback === 'correct' && lastTechnique === '{{ $tech }}',
                                                    'bg-red-700! border-red-600! text-white!': feedback === 'incorrect' && lastTechnique === '{{ $tech }}',
                                                    'opacity-50': feedback !== null && lastTechnique !== '{{ $tech }}'
                                                }"
                                                class="w-full p-4 text-left rounded-lg border-2 border-slate-700 bg-slate-800/50 hover:bg-slate-700/50 transition-all duration-150 disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                                <flux:text>{{ $tech }}</flux:text>
                                            </button>
                                        @endforeach
                                    </div>
                                </flux:fieldset>
                            </div>
                        @else
                            {{-- Placeholder --}}
                            <div class="p-6 h-full flex items-center justify-center text-center">
                                <flux:text>
                                    @if($isGameFinished)
                                        ¡Todos los casos han sido procesados!
                                    @else
                                        Selecciona un problema del Inbox para comenzar a trabajar.
                                    @endif
                                </flux:text>
                            </div>
                        @endif
                    </div>

                </div>

                {{-- Footer: Aciertos y Botón de Finalizar --}}
                <div class="p-4 border-t border-slate-700">
                    <div class="flex justify-between items-center">
                        <flux:text>
                            Casos Resueltos: <strong>{{ count($solvedCases) }} / {{ $totalCases }}</strong>
                        </flux:text>
                        
                        @if($isGameFinished)
                            <div class="text-right">
                                <flux:text size="sm">Calificación Final:</flux:text>
                                <flux:heading size="lg">{{ number_format($finalScore, 1) }} / 5.0</flux:heading>
                            </div>
                        @else
                            <flux:text color="red" variant="primary">
                                Casos Fallidos: <strong>{{ count($failedCases) }}</strong>
                            </flux:text>
                        @endif
                        
                        @if($isGameFinished)
                            <flux:button 
                                wire:click="$set('stage', 'completed')" 
                                variant="primary">
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
                <flux:avatar size="xl" src="https://placehold.co/128x128/34d399/ffffff?text=🚀" />
                <flux:heading size="lg">¡Felicidades!</flux:heading>
                <flux:text>Has completado el Reto del Nivel 5.</flux:text>
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