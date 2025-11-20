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
            <flux:heading size="lg">Nivel 11 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>¡Construcción completada! Ingresa el código para validar la teoría.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" variant="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- FASE 3: PUZZLE CLASIFICADOR --}}
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
            x-on:classification-result.window="
                setTimeout(() => { @this.dispatch('load-next'); }, 600);
            "
            class="w-full max-w-5xl h-full flex flex-col"
        >
            <flux:card class="flex-grow flex flex-col overflow-hidden bg-slate-900 border-slate-800">
                {{-- Header --}}
                <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                    <flux:breadcrumbs>
                        <flux:breadcrumbs.item>Nivel 11</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item>Clasificador de Metodologías</flux:breadcrumbs.item>
                    </flux:breadcrumbs>
                    <div class="flex items-center gap-4">
                        <div class="text-sm text-slate-400">
                            Aciertos: <span class="text-white font-bold">{{ $correctCount }}</span> / {{ $totalConcepts }}
                        </div>
                        <div :class="{'animate-pulse bg-red-500 text-white': remaining < 60}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                            <flux:icon name="clock" class="h-5 w-5" />
                            <span x-text="formattedTime()" class="font-mono"></span>
                        </div>
                    </div>
                </div>

                {{-- Área de Juego --}}
                <div class="flex-grow flex flex-col items-center justify-center p-8 relative">
                    
                    @if(!$isGameFinished && $currentConcept)
                        {{-- TARJETA CENTRAL (CONCEPTO) --}}
                        <div 
                            class="w-full max-w-lg bg-white dark:bg-slate-800 p-8 rounded-xl shadow-2xl border-2 border-slate-600 text-center mb-12 transform transition-all duration-300"
                            x-data="{ result: null }"
                            x-on:classification-result.window="
                                result = $event.detail.result;
                                setTimeout(() => result = null, 600);
                            "
                            :class="{
                                'scale-110 border-green-500 bg-green-900/20': result === 'correct',
                                'scale-90 border-red-500 bg-red-900/20 animate-shake': result === 'incorrect'
                            }"
                        >
                            <flux:heading size="xl" class="text-slate-800 dark:text-slate-100">
                                {{ $currentConcept['text'] }}
                            </flux:heading>
                            <flux:text class="mt-2 text-slate-500">¿A qué metodología pertenece?</flux:text>
                        </div>

                        {{-- BOTONES DE OPCIONES --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-4xl">
                            @foreach($this::METHODOLOGIES as $key => $meta)
                                <button 
                                    wire:click="classify('{{ $key }}')"
                                    class="group relative flex items-center p-4 rounded-xl border-2 border-slate-700 bg-slate-800 hover:bg-{{ $meta['color'] }}-900/30 hover:border-{{ $meta['color'] }}-500 transition-all duration-200"
                                >
                                    <div class="p-3 rounded-full bg-{{ $meta['color'] }}-500/20 text-{{ $meta['color'] }}-400 group-hover:scale-110 transition-transform">
                                        <flux:icon name="{{ $meta['icon'] }}" class="w-6 h-6" />
                                    </div>
                                    <div class="ml-4 text-left">
                                        <flux:heading size="md" class="group-hover:text-{{ $meta['color'] }}-400 transition-colors">
                                            {{ $meta['title'] }}
                                        </flux:heading>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        {{-- PANTALLA FINAL --}}
                        <div class="text-center">
                            <flux:heading size="2xl" class="text-green-400 mb-4">¡Clasificación Completa!</flux:heading>
                            <flux:text size="lg" class="mb-8">
                                Has identificado correctamente <strong>{{ $correctCount }}</strong> de <strong>{{ $totalConcepts }}</strong> conceptos.
                            </flux:text>
                            <div class="p-6 bg-slate-800 rounded-xl border border-slate-700 inline-block min-w-[300px]">
                                <flux:text size="sm">Nota Final</flux:text>
                                <flux:heading size="3xl" class="text-primary-400">{{ number_format($finalScore, 1) }}</flux:heading>
                            </div>
                            <div class="mt-8">
                                <flux:button wire:click="$set('stage', 'completed')" variant="primary" class="w-full max-w-xs">
                                    Continuar
                                </flux:button>
                            </div>
                        </div>
                    @endif
                    
                </div>
            </flux:card>
        </div>
    @endif

    {{-- FASE 4: COMPLETADO --}}
    @if ($stage === 'completed')
        <flux:card class="w-full max-w-sm text-center p-6">
            <div class="flex flex-col items-center space-y-4">
                <flux:avatar size="xl" src="https://placehold.co/128x128/34d399/ffffff?text=11" />
                <flux:heading size="lg">¡Nivel 11 Dominado!</flux:heading>
                <flux:text>Dominas las metodologías de innovación.</flux:text>
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