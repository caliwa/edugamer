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

<flux:heading size="lg">Nivel 1 Superado</flux:heading>

<div class="mt-4 space-y-4">

<flux:text>¡Excelente! Ingresa el código de acceso para continuar.</flux:text>

<flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>

@if($feedback) <flux:text color="danger" size="sm">{{ $feedback }}</flux:text> @endif

</div>

<div class="mt-6 flex justify-end">

<flux:button wire:click="verifyCode" color="primary">Verificar</flux:button>

</div>

</flux:card>

@endif



{{-- FASE 3: PUZZLE WEB GAMIFICADO --}}

@if ($stage === 'web_puzzle')
        <div
            wire:poll.1s="countdown"
            x-data="{
                totalQuestions: {{ count($questions) }},
                answeredQuestions: $wire.entangle('answers').live.length,
                remaining: $wire.entangle('timeRemaining').live,
                formattedTime() {
                    if (this.remaining < 0) return '00:00';
                    const minutes = Math.floor(this.remaining / 60);
                    const seconds = this.remaining % 60;
                    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }"
            class="w-full max-w-3xl h-full flex flex-col"
        >
            <flux:card class="flex-grow flex flex-col">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <flux:breadcrumbs>
                        <flux:breadcrumbs.item>Nivel 1</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item>Reto de Conocimiento</flux:breadcrumbs.item>
                    </flux:breadcrumbs>
                    <div class="flex justify-between items-center mt-4">
                        <flux:heading size="xl">Espíritu Emprendedor</flux:heading>
                        <div 
                            :class="{
                                'bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200': remaining >= 60,
                                'animate-pulse bg-red-500 text-white': remaining < 60
                            }"
                            class="inline-flex items-center gap-x-2 rounded-md px-3 py-1.5 text-base font-semibold"
                        >
                            <flux:icon name="clock" class="h-5 w-5" />
                            <span x-text="formattedTime()" class="font-mono"></span>
                        </div>
                    </div>
                    <div class="mt-4 w-full bg-slate-700 rounded-full h-2.5">
                        <div class="bg-primary-500 h-2.5 rounded-full transition-all duration-300" :style="`width: ${(answeredQuestions / totalQuestions) * 100}%`"></div>
                    </div>
                </div>

                <div class="p-6 flex-grow overflow-y-auto space-y-10">
                    @foreach($questions as $index => $question)
                        <div wire:key="question-{{ $index }}">
                            <flux:fieldset>
                                <flux:legend>{{ $index + 1 }}. {{ $question['text'] }}</flux:legend>
                                <flux:radio.group wire:model.live="answers.{{ $index }}" class="flex-col mt-4">
                                    @foreach ($question['options'] as $optionIndex => $optionText)
                                        <flux:radio value="{{ $optionIndex }}" label="{{ $optionText }}"/>
                                    @endforeach
                                </flux:radio.group>
                            </flux:fieldset>
                        </div>
                    @endforeach
                </div>

                <div class="p-6 border-t border-slate-200 dark:border-slate-700">
                    <flux:button wire:click="calculateScore" class="w-full" color="primary">
                        Finalizar y ver mi calificación
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

<flux:text>Has completado el Reto del Nivel 1.</flux:text>

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