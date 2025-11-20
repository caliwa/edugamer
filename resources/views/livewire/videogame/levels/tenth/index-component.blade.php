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
            <flux:heading size="lg">Nivel 10 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>¡Has encontrado la brújula! Ingresa el código para priorizar tus ideas.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" variant="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- FASE 3: PUZZLE MATRIZ --}}
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
            class="w-full max-w-6xl h-full flex flex-col"
        >
            <flux:card class="flex-grow flex flex-col overflow-hidden">
                {{-- Encabezado --}}
                <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                    <flux:breadcrumbs>
                        <flux:breadcrumbs.item>Nivel 10</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item>Priorización de Ideas</flux:breadcrumbs.item>
                    </flux:breadcrumbs>
                    <div :class="{'animate-pulse bg-red-500 text-white': remaining < 60}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                        <flux:icon name="clock" class="h-5 w-5" />
                        <span x-text="formattedTime()" class="font-mono"></span>
                    </div>
                </div>

                <div class="flex-grow grid grid-cols-1 lg:grid-cols-3 gap-0 h-full overflow-hidden">
                    
                    {{-- Columna 1: Tareas Disponibles --}}
                    <div class="flex flex-col border-r border-slate-700 bg-slate-800/30 p-4">
                        <flux:heading size="md" class="mb-2">Tareas Pendientes</flux:heading>
                        <flux:text size="sm" class="mb-4">1. Selecciona una tarea.<br>2. Haz clic en su cuadrante correcto.</flux:text>
                        
                        <div class="space-y-3 overflow-y-auto flex-grow">
                            @if(empty($availableTasks) && !$isGameFinished)
                                <div class="text-center text-slate-500 p-4 border-2 border-dashed border-slate-700 rounded-lg">
                                    Todas las tareas asignadas. ¡Revisa y finaliza!
                                </div>
                            @endif

                            @foreach($availableTasks as $taskId)
                                <button 
                                    wire:click="selectTask('{{ $taskId }}')"
                                    class="w-full text-left p-3 rounded-lg border-2 transition-all duration-200
                                    {{ $selectedTaskId === $taskId ? 'border-primary-500 bg-primary-900/20 ring-2 ring-primary-500/50' : 'border-slate-600 bg-slate-700 hover:bg-slate-600' }}"
                                >
                                    <flux:text size="sm">{{ $this::TASKS[$taskId]['text'] }}</flux:text>
                                </button>
                            @endforeach
                        </div>

                        {{-- Footer con Botón --}}
                        <div class="mt-4 pt-4 border-t border-slate-700">
                             @if($isGameFinished)
                                <div class="text-center mb-4">
                                    <flux:heading size="lg">Nota: {{ number_format($finalScore, 1) }} / 5.0</flux:heading>
                                </div>
                                <flux:button wire:click="$set('stage', 'completed')" class="w-full" variant="primary">Continuar</flux:button>
                            @else
                                <flux:button 
                                    wire:click="calculateScore" 
                                    class="w-full" 
                                    variant="primary"
                                    x-bind:disabled="{{ count($availableTasks) > 0 ? 'true' : 'false' }}"
                                >
                                    Finalizar Priorización
                                </flux:button>
                            @endif
                        </div>
                    </div>

                    {{-- Columna 2 y 3: La Matriz (2x2) --}}
                    <div class="lg:col-span-2 grid grid-cols-2 grid-rows-2 gap-2 p-4 bg-slate-900">
                        
                        @foreach($this::QUADRANTS as $qKey => $qInfo)
                            <button 
                                wire:click="assignToQuadrant('{{ $qKey }}')"
                                class="relative flex flex-col items-start justify-start p-4 rounded-xl border-2 transition-all duration-200 group
                                {{ $selectedTaskId ? 'hover:bg-slate-800/80 cursor-pointer border-dashed border-primary-400' : 'border-slate-700 bg-slate-800/50 cursor-default' }}
                                "
                            >
                                {{-- Label del Cuadrante --}}
                                <div class="absolute top-3 left-3 opacity-50 group-hover:opacity-100 transition-opacity">
                                    <flux:heading size="sm" class="font-bold uppercase tracking-wide">{{ $qInfo['label'] }}</flux:heading>
                                    <flux:text size="xs">{{ $qInfo['desc'] }}</flux:text>
                                </div>

                                {{-- Contenido Asignado --}}
                                <div class="mt-10 w-full space-y-2 z-10">
                                    @foreach($placedTasks as $taskId => $placedQuadrant)
                                        @if($placedQuadrant === $qKey)
                                            <div 
                                                wire:click.stop="undoPlacement('{{ $taskId }}')"
                                                class="p-2 rounded bg-slate-700 border border-slate-600 text-xs shadow-sm hover:bg-red-900/50 hover:border-red-500 cursor-pointer relative"
                                                title="Clic para devolver a la lista"
                                            >
                                                {{ $this::TASKS[$taskId]['text'] }}
                                                
                                                {{-- Feedback visual al finalizar --}}
                                                @if($isGameFinished)
                                                    @if($this::TASKS[$taskId]['quadrant'] === $qKey)
                                                        <flux:icon name="check" class="w-4 h-4 text-green-500 absolute top-1 right-1"/>
                                                    @else
                                                        <flux:icon name="x-mark" class="w-4 h-4 text-red-500 absolute top-1 right-1"/>
                                                    @endif
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </button>
                        @endforeach

                        {{-- Ejes (Decorativo) --}}
                        <div class="absolute pointer-events-none inset-0 flex items-center justify-center lg:ml-[33%]">
                           {{-- Centro de la matriz --}}
                           <div class="w-12 h-12 bg-slate-900 rounded-full flex items-center justify-center border border-slate-600 z-20">
                                <flux:icon name="arrows-up-down" class="text-slate-500 w-6 h-6" />
                           </div>
                        </div>
                    </div>

                </div>
            </flux:card>
        </div>
    @endif

    {{-- FASE 4: COMPLETADO --}}
    @if ($stage === 'completed')
        <flux:card class="w-full max-w-sm text-center p-6">
            <div class="flex flex-col items-center space-y-4">
                <flux:avatar size="xl" src="https://placehold.co/128x128/34d399/ffffff?text=10" />
                <flux:heading size="lg">¡Nivel 10 Completado!</flux:heading>
                <flux:text>Has aprendido a priorizar ideas estratégicamente.</flux:text>
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