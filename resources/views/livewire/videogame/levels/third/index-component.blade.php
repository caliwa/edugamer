<div class="w-full h-screen bg-slate-900 text-white flex flex-col items-center justify-center p-4">

    {{-- FASE 1: MINIJUEGO KAPLAY --}}
    @if ($stage === 'kaplay')
        {{-- (Esta parte queda igual que en la propuesta anterior, con el canvas y los botones) --}}
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
        {{-- (Esta parte queda igual) --}}
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

    {{-- FASE 3: PUZZLE "SELECCIONAR EMPRESARIOS" --}}
@if ($stage === 'web_puzzle')
    <div
        wire:poll.1s="countdown"
        {{-- CAMBIO 1: 'selectedCount' se elimina de aquí... --}}
        x-data="{
            remaining: $wire.entangle('timeRemaining').live,
            selectedNames: $wire.entangle('selectedNames').live, {{-- ...y se entrelaza el array 'selectedNames' en su lugar --}}
            formattedTime() {
                if (this.remaining < 0) return '00:00';
                const minutes = Math.floor(this.remaining / 60);
                const seconds = this.remaining % 60;
                return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }
        }"
        class="w-full max-w-2xl h-full flex flex-col"
    >
        <flux:card class="flex-grow flex flex-col">
            {{-- Encabezado (sin cambios) --}}
            <div class="p-6 border-b border-slate-700">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item>Nivel 3</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>Reto: Fundadores</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <div class="flex justify-between items-center mt-4">
                    <flux:heading size="xl">Pioneros de Antioquia</flux:heading>
                    <div :class="{'animate-pulse bg-red-500 text-white': remaining < 60}" class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                        <flux:icon name="clock" class="h-5 w-5" />
                        <span x-text="formattedTime()" class="font-mono"></span>
                    </div>
                </div>
                <flux:text class="mt-4">
                    Selecciona los 5 empresarios que consideres correctos.
                </flux:text>
            </div>
            
            {{-- Juego de Selección --}}
            <div class="p-6 flex-grow overflow-y-auto">
                <flux:fieldset>
                    {{-- CAMBIO 2: Se usa 'selectedNames.length' para el contador --}}
                    <flux:legend>Selección de Empresarios (<span x-text="selectedNames.length"></span>/5)</flux:legend>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        @foreach($allEntrepreneurs as $name)
                            <flux:checkbox 
                                wire:model.live="selectedNames"
                                value="{{ $name }}"
                                label="{{ $name }}"
                                x-id="['checkbox-{{ $loop->index }}']"
                                {{-- CAMBIO 3: Se usa 'selectedNames.length' para la lógica del 'disabled' --}}
                                x-bind:disabled="selectedNames.length >= 5 && !$el.checked"
                            />
                        @endforeach
                    </div>
                </flux:fieldset>
            </div>

            {{-- Footer: Botón de Finalizar --}}
            <div class="p-6 border-t border-slate-700 flex justify-between items-center">
                <flux:button 
                    wire:click="resetPuzzle"
                    wire:loading.attr="disabled"
                    color="white" 
                    variant="primary">
                    Reiniciar
                </flux:button>
                <flux:button 
                    wire:click="calculateScore" 
                    wire:loading.attr="disabled"
                    {{-- CAMBIO 4: Se usa 'selectedNames.length' para el botón de finalizar --}}
                    x-bind:disabled="selectedNames.length !== 5"
                    color="primary">
                    Finalizar y Calificar
                </flux:button>
            </div>
        </flux:card>
    </div>
@endif

    {{-- FASE 4: NIVEL COMPLETADO --}}
    @if ($stage === 'completed')
        {{-- (Esta parte queda igual) --}}
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