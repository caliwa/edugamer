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
            <flux:heading size="lg">Nivel 12 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>¡Has identificado al cliente! Ingresa el código para crear su perfil.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" variant="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- FASE 3: PUZZLE MAPA DE EMPATÍA --}}
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
            x-on:empathy-result.window="
                setTimeout(() => { @this.dispatch('load-next-statement'); }, 800);
            "
            class="w-full max-w-5xl h-full flex flex-col"
        >
            <flux:card class="flex-grow flex flex-col overflow-hidden bg-slate-900 border-slate-800">
                {{-- Header --}}
                <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                    <flux:breadcrumbs>
                        <flux:breadcrumbs.item>Nivel 12</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item>Mapa de Empatía</flux:breadcrumbs.item>
                    </flux:breadcrumbs>
                    <div class="flex items-center gap-4">
                         <div class="text-sm text-slate-400">
                            Progreso: <span class="text-white font-bold">{{ $correctCount + ($lastResult === 'incorrect' ? 1 : 0) }}</span> / {{ $totalStatements }}
                        </div>
                        <div :class="{'animate-pulse bg-red-500 text-white': remaining < 60}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                            <flux:icon name="clock" class="h-5 w-5" />
                            <span x-text="formattedTime()" class="font-mono"></span>
                        </div>
                    </div>
                </div>

                {{-- Área de Juego --}}
                <div class="flex-grow flex flex-col relative p-4">
                    
                    @if(!$isGameFinished && $currentStatement)
                        
                        {{-- La Afirmación (Tarjeta Flotante) --}}
                        <div class="absolute top-4 left-1/2 transform -translate-x-1/2 z-20 w-full max-w-md">
                             <div 
                                class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-2xl border-2 border-slate-600 text-center transition-all duration-300"
                                x-data="{ result: null }"
                                x-on:empathy-result.window="
                                    result = $event.detail.result;
                                    setTimeout(() => result = null, 800);
                                "
                                :class="{
                                    'scale-110 border-green-500 bg-green-900/20': result === 'correct',
                                    'scale-90 border-red-500 bg-red-900/20 animate-shake': result === 'incorrect'
                                }"
                            >
                                <flux:heading size="lg" class="text-slate-800 dark:text-slate-100">
                                    {{ $currentStatement['text'] }}
                                </flux:heading>
                            </div>
                        </div>

                        {{-- El Mapa (Grid 2x2) --}}
                        <div class="flex-grow grid grid-cols-2 grid-rows-2 gap-4 mt-20">
                            @foreach($this::QUADRANTS as $key => $data)
                                <button 
                                    wire:click="classify('{{ $key }}')"
                                    class="relative flex flex-col items-center justify-center p-6 rounded-2xl border-2 border-dashed border-{{ $data['color'] }}-500/50 bg-slate-800/50 hover:bg-{{ $data['color'] }}-900/20 transition-all duration-200 group"
                                >
                                    <div class="p-4 rounded-full bg-{{ $data['color'] }}-500/20 mb-3 group-hover:scale-110 transition-transform">
                                        <flux:icon name="{{ $data['icon'] }}" class="w-8 h-8 text-{{ $data['color'] }}-400" />
                                    </div>
                                    <flux:heading size="lg" class="text-{{ $data['color'] }}-200">{{ $data['label'] }}</flux:heading>
                                </button>
                            @endforeach
                            
                            {{-- Avatar Central (Decorativo) --}}
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                                <div class="w-24 h-24 bg-slate-900 rounded-full border-4 border-slate-700 flex items-center justify-center shadow-xl">
                                    <flux:icon name="user" variant="solid" class="w-12 h-12 text-slate-500" />
                                </div>
                            </div>
                        </div>

                    @else
                        {{-- PANTALLA FINAL --}}
                        <div class="h-full flex flex-col items-center justify-center text-center">
                            <flux:heading size="2xl" class="text-green-400 mb-4">¡Perfil Completado!</flux:heading>
                            <flux:text size="lg" class="mb-8">
                                Has construido el mapa de empatía con éxito.
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
                <flux:avatar size="xl" src="https://placehold.co/128x128/34d399/ffffff?text=12" />
                <flux:heading size="lg">¡Cliente Identificado!</flux:heading>
                <flux:text>Ahora conoces a tu cliente objetivo a la perfección.</flux:text>
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