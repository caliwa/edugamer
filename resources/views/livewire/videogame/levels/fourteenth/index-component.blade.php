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
            <flux:heading size="lg">Nivel 14 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>¡Red de contactos iniciada! Ingresa el código para formalizar las alianzas.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" variant="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- FASE 3: PUZZLE MATCHING --}}
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
            <flux:card class="flex-grow flex flex-col overflow-hidden bg-slate-900 border-slate-800">
                
                {{-- Header --}}
                <div class="p-6 border-b border-slate-700 flex justify-between items-center">
                    <div>
                        <flux:breadcrumbs>
                            <flux:breadcrumbs.item>Nivel 14</flux:breadcrumbs.item>
                            <flux:breadcrumbs.item>Networking Estratégico</flux:breadcrumbs.item>
                        </flux:breadcrumbs>
                        <flux:text size="sm" class="mt-2">Selecciona un <strong>Aliado</strong> y luego su <strong>Rol</strong> correspondiente.</flux:text>
                    </div>
                    <div :class="{'animate-pulse bg-red-500 text-white': remaining < 60}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                        <flux:icon name="clock" class="h-5 w-5" />
                        <span x-text="formattedTime()" class="font-mono"></span>
                    </div>
                </div>

                {{-- Área de Juego (2 Columnas) --}}
                <div class="flex-grow grid grid-cols-1 md:grid-cols-2 gap-8 p-6 overflow-y-auto">
                    
                    {{-- Columna Aliados (Izquierda) --}}
                    <div class="space-y-4">
                        <flux:heading size="md" class="text-center text-slate-300 uppercase tracking-wide">Aliados Potenciales</flux:heading>
                        
                        @foreach($this::ALLIES as $key => $data)
                            @php
                                $isMatched = isset($userMatches[$key]);
                                $isSelected = $selectedAlly === $key;
                                
                                $borderColor = 'border-slate-700';
                                if($isSelected) $borderColor = 'border-amber-400 bg-amber-900/20 ring-2 ring-amber-400/50';
                                elseif($isMatched) $borderColor = 'border-blue-500 bg-blue-900/20';
                            @endphp

                            <button 
                                wire:click="selectAlly('{{ $key }}')"
                                @if($isGameFinished) disabled @endif
                                class="w-full flex items-center justify-between p-4 rounded-xl border-2 transition-all duration-200 hover:scale-[1.02] {{ $borderColor }}"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-slate-800 rounded-lg">
                                        <flux:icon name="{{ $data['icon'] }}" class="w-6 h-6 text-slate-300" />
                                    </div>
                                    <flux:text class="font-semibold text-slate-200">{{ $data['name'] }}</flux:text>
                                </div>
                                
                                @if($isMatched)
                                    <flux:icon name="link" class="w-5 h-5 text-blue-400" />
                                @endif

                                @if($isGameFinished && $isMatched)
                                    @if(isset($this::CORRECT_MATCHES[$key]) && $this::CORRECT_MATCHES[$key] === $userMatches[$key])
                                        <flux:icon name="check" class="w-5 h-5 text-green-500 ml-2" />
                                    @else
                                        <flux:icon name="x-mark" class="w-5 h-5 text-red-500 ml-2" />
                                    @endif
                                @endif
                            </button>
                        @endforeach
                    </div>

                    {{-- Columna Roles (Derecha) --}}
                    <div class="space-y-4">
                        <flux:heading size="md" class="text-center text-slate-300 uppercase tracking-wide">Roles Estratégicos</flux:heading>
                        
                        @foreach($this::ROLES as $key => $description)
                            @php
                                // Buscar si este rol ya fue asignado
                                $matchedAllyKey = array_search($key, $userMatches);
                                $isAssigned = $matchedAllyKey !== false;
                                
                                $bgColor = 'bg-slate-800/50';
                                if($isAssigned) $bgColor = 'bg-blue-900/20 border-blue-500';
                            @endphp

                            <button 
                                wire:click="selectRole('{{ $key }}')"
                                @if($isGameFinished) disabled @endif
                                class="w-full text-left p-4 rounded-xl border-2 border-slate-700 transition-all duration-200 hover:bg-slate-800 {{ $bgColor }}
                                {{ $selectedAlly && !$isAssigned ? 'animate-pulse ring-1 ring-slate-500' : '' }}"
                            >
                                <flux:text class="text-sm text-slate-300">{{ $description }}</flux:text>
                                
                                @if($isAssigned && isset($this::ALLIES[$matchedAllyKey]))
                                    <div class="mt-2 pt-2 border-t border-blue-500/30 flex items-center gap-2 text-blue-300 text-xs font-bold">
                                        <flux:icon name="link" class="w-3 h-3" />
                                        Vinculado con: {{ $this::ALLIES[$matchedAllyKey]['name'] }}
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>

                </div>

                {{-- Footer --}}
                <div class="p-6 border-t border-slate-700 bg-slate-800/30">
                    <div class="flex justify-between items-center">
                        <flux:text>
                            Parejas formadas: <span class="font-bold text-white">{{ count($userMatches) }}</span> / {{ count($this::ALLIES) }}
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
                                x-bind:disabled="{{ count($userMatches) !== count($this::ALLIES) ? 'true' : 'false' }}"
                            >
                                Confirmar Alianzas
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
                <flux:avatar size="xl" src="https://placehold.co/128x128/34d399/ffffff?text=14" />
                <flux:heading size="lg">¡Red Construida!</flux:heading>
                <flux:text>Has diseñado una estructura de apoyo sólida para tu negocio.</flux:text>
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