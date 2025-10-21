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
            <flux:heading size="lg">Nivel 6 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>¡Has recolectado los datos! Ingresa el código de Ruta N.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" variant="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- FASE 3: PUZZLE "LANDING PAGE BUILDER" --}}
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
            <flux:card class="flex-grow flex flex-col">
                {{-- Encabezado --}}
                <div class="p-6 border-b border-slate-700">
                    <div class="flex justify-between items-center">
                        <flux:breadcrumbs>
                            <flux:breadcrumbs.item>Nivel 6</flux:breadcrumbs.item>
                            <flux:breadcrumbs.item>Reto: Landing Page (Ruta N)</flux:breadcrumbs.item>
                        </flux:breadcrumbs>
                        <div :class="{'animate-pulse bg-red-500 text-white': remaining < 60}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                            <flux:icon name="clock" class="h-5 w-5" />
                            <span x-text="formattedTime()" class="font-mono"></span>
                        </div>
                    </div>
                </div>
                
                {{-- Contenido del Juego: 2 Columnas --}}
                <div class="flex-grow grid grid-cols-1 md:grid-cols-2 gap-0 h-full overflow-hidden">
                    
                    {{-- Columna 1: Wireframe (Los Slots) --}}
                    <div class="flex flex-col border-r border-slate-700">
                        <div class="p-4 border-b border-slate-700">
                            <flux:heading size="md">Wireframe de la Startup</flux:heading>
                            <flux:text size="sm">Haz clic en un slot para activarlo.</flux:text>
                        </div>
                        <div class="p-6 flex-grow overflow-y-auto space-y-4">
                            
                            {{-- Slot 1: Hero --}}
                            <button 
                                wire:click="selectSlot('hero')"
                                class="w-full p-4 rounded-lg border-2 text-center transition-all
                                    {{ $selectedSlot === 'hero' ? 'border-primary-500 bg-primary-900/30 ring-2 ring-primary-500' : 'border-slate-700' }}
                                    {{ $isGameFinished && $heroContent === self::CORRECT_ANSWERS['hero'] ? 'border-green-500' : '' }}
                                    {{ $isGameFinished && $heroContent !== self::CORRECT_ANSWERS['hero'] ? 'border-red-500' : '' }}"
                                x-bind:disabled="{{ $isGameFinished ? 'true' : 'false' }}"
                            >
                                <flux:text size="sm" class="font-semibold text-slate-400">PROPUESTA DE VALOR (HERO)</flux:text>
                                <flux:text class="mt-2 text-lg h-12 flex items-center justify-center">
                                    {{ $heroContent ?? 'Vacío' }}
                                </flux:text>
                            </button>
                            
                            {{-- Slot 2: Info --}}
                            <button 
                                wire:click="selectSlot('info')"
                                class="w-full p-4 rounded-lg border-2 text-center transition-all
                                    {{ $selectedSlot === 'info' ? 'border-primary-500 bg-primary-900/30 ring-2 ring-primary-500' : 'border-slate-700' }}
                                    {{ $isGameFinished && $infoContent === self::CORRECT_ANSWERS['info'] ? 'border-green-500' : '' }}
                                    {{ $isGameFinished && $infoContent !== self::CORRECT_ANSWERS['info'] ? 'border-red-500' : '' }}"
                                x-bind:disabled="{{ $isGameFinished ? 'true' : 'false' }}"
                            >
                                <flux:text size="sm" class="font-semibold text-slate-400">DESCRIPCIÓN DEL PRODUCTO</flux:text>
                                <flux:text class="mt-2 text-lg h-12 flex items-center justify-center">
                                    {{ $infoContent ?? 'Vacío' }}
                                </flux:text>
                            </button>
                            
                            {{-- Slot 3: CTA --}}
                            <button 
                                wire:click="selectSlot('cta')"
                                class="w-full p-4 rounded-lg border-2 text-center transition-all
                                    {{ $selectedSlot === 'cta' ? 'border-primary-500 bg-primary-900/30 ring-2 ring-primary-500' : 'border-slate-700' }}
                                    {{ $isGameFinished && $ctaContent === self::CORRECT_ANSWERS['cta'] ? 'border-green-500' : '' }}
                                    {{ $isGameFinished && $ctaContent !== self::CORRECT_ANSWERS['cta'] ? 'border-red-500' : '' }}"
                                x-bind:disabled="{{ $isGameFinished ? 'true' : 'false' }}"
                            >
                                <flux:text size="sm" class="font-semibold text-slate-400">LLAMADO A LA ACCIÓN (CTA)</flux:text>
                                <flux:text class="mt-2 text-lg h-12 flex items-center justify-center">
                                    {{ $ctaContent ?? 'Vacío' }}
                                </flux:text>
                            </button>
                            
                        </div>
                    </div>

                    {{-- Columna 2: Content Pool (Las Opciones) --}}
                    <div class="flex flex-col">
                        <div class="p-4 border-b border-slate-700">
                            <flux:heading size="md">Pool de Contenido</flux:heading>
                            <flux:text size="sm">Haz clic en un contenido para asignarlo.</flux:text>
                        </div>
                        <div class="p-6 flex-grow overflow-y-auto space-y-3">
                            @if($isGameFinished)
                                <div class="p-6 h-full flex items-center justify-center text-center">
                                    <flux:text>
                                        Juego finalizado. Tu calificación es <strong>{{ number_format($finalScore, 1) }} / 5.0</strong>
                                    </flux:text>
                                </div>
                            @else
                                @forelse($contentPool as $content)
                                    <button
                                        wire:click="assignContent('{{ $content }}')"
                                        x-bind:disabled="!$wire.selectedSlot"
                                        class="w-full p-4 text-left rounded-lg border-2 border-slate-700 bg-slate-800/50 hover:bg-slate-700/50 transition-all duration-150 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <flux:text>{{ $content }}</flux:text>
                                    </button>
                                @empty
                                    <div class="p-6 text-center">
                                        <flux:text>¡Wireframe completo!</flux:text>
                                    </div>
                                @endforelse
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Footer: Botón de Finalizar --}}
                <div class="p-4 border-t border-slate-700">
                    <div class="flex justify-end items-center">
                        @if($isGameFinished)
                            <flux:button 
                                wire:click="$set('stage', 'completed')" 
                                variant="primary">
                                Continuar
                            </flux:button>
                        @else
                            <flux:button 
                                wire:click="calculateScore" 
                                variant="primary"
                                {{-- Deshabilita hasta que los 3 slots estén llenos --}}
                                x-bind:disabled="!$wire.heroContent || !$wire.infoContent || !$wire.ctaContent"
                            >
                                Publicar Landing Page y Calificar
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