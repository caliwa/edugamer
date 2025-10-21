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
            <flux:heading size="lg">Nivel 7 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>¡Has activado la red! Ingresa el código estratégico.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" variant="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif

{{-- FASE 3: PUZZLE "STRATEGY BUILDER" --}}
@if ($stage === 'web_puzzle')
        {{-- Calculamos el número de distractores en PHP para pasarlo a Alpine --}}
        @php
            $distractorCount = 0;
            foreach($this::COMPONENTS as $comp => $cat) {
                if ($cat === 'distractor') {
                    $distractorCount++;
                }
            }
            $totalComponents = count($this::COMPONENTS);
            $totalCategories = count($this::CATEGORIES);
        @endphp

        <div
            wire:poll.1s="countdown"
            {{-- x-data con lógica para deshabilitar botón --}}
            x-data="{
                remaining: $wire.entangle('timeRemaining').live,
                componentPool: $wire.entangle('componentPool').live,
                assignedComponents: $wire.entangle('assignedComponents').live,
                distractorCount: {{ $distractorCount }},
                totalCategories: {{ $totalCategories }},

                // Función Alpine para verificar si está incompleto
                isStrategyIncomplete() {
                    const poolHasCorrectItems = this.componentPool.length > this.distractorCount;
                    let filledSlots = 0;
                    for (const key in this.assignedComponents) {
                        if (this.assignedComponents[key] !== null) {
                            filledSlots++;
                        }
                    }
                    const hasEmptySlots = filledSlots < this.totalCategories;
                    return poolHasCorrectItems || hasEmptySlots;
                },

                formattedTime() {
                    if (this.remaining < 0) return '00:00';
                    const minutes = Math.floor(this.remaining / 60);
                    const seconds = this.remaining % 60;
                    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }"
            class="w-full max-w-6xl h-full flex flex-col" {{-- max-w-6xl para pantallas grandes --}}
        >
            <flux:card class="flex-grow flex flex-col">
                {{-- Encabezado --}}
                 <div class="p-4 md:p-6 border-b border-slate-700"> {{-- Menos padding en móvil --}}
                     <div class="flex flex-col md:flex-row justify-between md:items-center gap-2 md:gap-4"> {{-- Stack en móvil --}}
                        <flux:breadcrumbs>
                            <flux:breadcrumbs.item>Nivel 7</flux:breadcrumbs.item>
                            <flux:breadcrumbs.item>Reto: Constructor de Estrategias</flux:breadcrumbs.item>
                        </flux:breadcrumbs>
                         <div :class="{'animate-pulse bg-red-500 text-white': remaining < 60}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200 self-end md:self-center"> {{-- Alineación timer --}}
                            <flux:icon name="clock" class="h-5 w-5" />
                            <span x-text="formattedTime()" class="font-mono"></span>
                        </div>
                    </div>
                 </div>

                {{-- Contenido: Stack en móvil, 3 Columnas en MD+ --}}
                {{-- !! CORRECCIÓN AQUÍ !! grid-cols-1 por defecto, md:grid-cols-3 --}}
                <div class="flex-grow grid grid-cols-1 md:grid-cols-3 gap-0 h-full overflow-hidden">

                    {{-- Columna 1: Categorías (Slots) - Parte 1 --}}
                    {{-- Borde derecho solo en pantallas medianas+ --}}
                    <div class="flex flex-col md:border-r border-slate-700">
                        @foreach(array_slice($this::CATEGORIES, 0, 3, true) as $key => $title)
                            {{-- Borde inferior siempre --}}
                            <button
                                wire:click="selectCategory('{{ $key }}')"
                                class="flex-1 flex flex-col border-b border-slate-700 p-4 min-h-[80px] md:min-h-[100px] text-left transition-all {{-- Altura mínima reducida en móvil --}}
                                       {{ $selectedCategoryKey === $key ? 'ring-2 ring-primary-500 bg-primary-900/20' : '' }}
                                       {{ $isGameFinished ? 'opacity-70 cursor-default' : 'hover:bg-slate-800/50' }}"
                                x-bind:disabled="{{ $isGameFinished ? 'true' : 'false' }}"
                            >
                                <flux:heading size="sm" class="text-primary-400 mb-2">{{ $title }}</flux:heading>
                                @if($assignedComponents[$key])
                                    <button
                                        wire:click.stop="unassignFromCategory('{{ $key }}')"
                                        class="w-full text-left p-2 md:p-3 bg-slate-700 rounded-md transition-all hover:bg-slate-600/80 {{-- Menos padding en móvil --}}
                                               {{ $isGameFinished && isset(self::COMPONENTS[$assignedComponents[$key]]) && self::COMPONENTS[$assignedComponents[$key]] === $key ? 'ring-2 ring-green-500' : '' }}
                                               {{ $isGameFinished && isset(self::COMPONENTS[$assignedComponents[$key]]) && self::COMPONENTS[$assignedComponents[$key]] !== $key && self::COMPONENTS[$assignedComponents[$key]] !== 'distractor' ? 'ring-2 ring-red-500' : '' }}
                                               {{ $isGameFinished && isset(self::COMPONENTS[$assignedComponents[$key]]) && self::COMPONENTS[$assignedComponents[$key]] === 'distractor' ? 'ring-2 ring-amber-500 opacity-80' : '' }}"
                                        x-bind:disabled="{{ $isGameFinished ? 'true' : 'false' }}"
                                        title="Clic para devolver al pool"
                                    >
                                        <flux:text size="sm">{{ $assignedComponents[$key] }}</flux:text>
                                    </button>
                                @else
                                    <div class="flex-grow flex items-center justify-center text-slate-500 text-xs md:text-sm"> {{-- Texto más pequeño en móvil --}}
                                        {{ $selectedCategoryKey === $key ? 'Seleccionado' : 'Clic para seleccionar' }}
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    {{-- Columna 2: Categorías (Slots) - Parte 2 --}}
                    {{-- Borde derecho solo en pantallas medianas+ --}}
                    <div class="flex flex-col md:border-r border-slate-700">
                         @foreach(array_slice($this::CATEGORIES, 3, 3, true) as $key => $title)
                            {{-- Borde inferior siempre --}}
                            <button
                                wire:click="selectCategory('{{ $key }}')"
                                class="flex-1 flex flex-col border-b border-slate-700 p-4 min-h-[80px] md:min-h-[100px] text-left transition-all
                                       {{ $selectedCategoryKey === $key ? 'ring-2 ring-primary-500 bg-primary-900/20' : '' }}
                                       {{ $isGameFinished ? 'opacity-70 cursor-default' : 'hover:bg-slate-800/50' }}"
                                x-bind:disabled="{{ $isGameFinished ? 'true' : 'false' }}"
                            >
                                <flux:heading size="sm" class="text-primary-400 mb-2">{{ $title }}</flux:heading>
                                @if($assignedComponents[$key])
                                    <button
                                        wire:click.stop="unassignFromCategory('{{ $key }}')"
                                        class="w-full text-left p-2 md:p-3 bg-slate-700 rounded-md transition-all hover:bg-slate-600/80
                                               {{ $isGameFinished && isset(self::COMPONENTS[$assignedComponents[$key]]) && self::COMPONENTS[$assignedComponents[$key]] === $key ? 'ring-2 ring-green-500' : '' }}
                                               {{ $isGameFinished && isset(self::COMPONENTS[$assignedComponents[$key]]) && self::COMPONENTS[$assignedComponents[$key]] !== $key && self::COMPONENTS[$assignedComponents[$key]] !== 'distractor' ? 'ring-2 ring-red-500' : '' }}
                                               {{ $isGameFinished && isset(self::COMPONENTS[$assignedComponents[$key]]) && self::COMPONENTS[$assignedComponents[$key]] === 'distractor' ? 'ring-2 ring-amber-500 opacity-80' : '' }}"
                                        x-bind:disabled="{{ $isGameFinished ? 'true' : 'false' }}"
                                        title="Clic para devolver al pool"
                                    >
                                        <flux:text size="sm">{{ $assignedComponents[$key] }}</flux:text>
                                    </button>
                                @else
                                    <div class="flex-grow flex items-center justify-center text-slate-500 text-xs md:text-sm">
                                         {{ $selectedCategoryKey === $key ? 'Seleccionado' : 'Clic para seleccionar' }}
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    {{-- Columna 3: Component Pool (Opciones) --}}
                    {{-- Fondo diferente solo en pantallas medianas+ --}}
                    <div class="flex flex-col md:bg-slate-800/50">
                        <div class="p-4 border-b border-slate-700">
                            <flux:heading size="md">Componentes Estratégicos</flux:heading>
                            <flux:text size="sm">{{ $selectedCategoryKey ? 'Clic para asignar a: '.self::CATEGORIES[$selectedCategoryKey] : 'Selecciona una categoría primero' }}</flux:text>
                        </div>
                        {{-- Altura fija y scroll en móvil para evitar que se alargue demasiado --}}
                        <div class="p-4 flex-grow overflow-y-auto space-y-3 h-64 md:h-auto">
                            @if($isGameFinished)
                                 <div class="p-6 h-full flex items-center justify-center text-center">
                                     <flux:text>Juego finalizado. Calificación: {{ number_format($finalScore, 1) }} / 5.0</flux:text>
                                </div>
                            @else
                                @foreach($componentPool as $componentText)
                                     {{-- Botón de Componente --}}
                                    <button
                                        wire:click="assignSelectedComponent('{{ $componentText }}')"
                                        x-bind:disabled="!$wire.selectedCategoryKey"
                                        class="w-full text-left p-3 bg-slate-700 rounded-md transition-all hover:bg-slate-600/80 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <flux:text size="sm">{{ $componentText }}</flux:text>
                                    </button>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Footer: Botón de Finalizar --}}
                <div class="p-4 border-t border-slate-700">
                   <div class="flex justify-end items-center">
                        @if($isGameFinished)
                            <flux:button wire:click="$set('stage', 'completed')" variant="primary">Continuar</flux:button>
                        @else
                            <flux:button
                                wire:click="calculateScore"
                                variant="primary"
                                {{-- Llama a la función Alpine --}}
                                x-bind:disabled="isStrategyIncomplete()"
                            >
                                Finalizar Estrategia
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
                <flux:avatar size="xl" src="https://placehold.co/128x128/34d399/ffffff?text=N" />
                <flux:heading size="lg">¡Felicidades!</flux:heading>
                <flux:text>Has completado el Reto de Ruta N.</flux:text>
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