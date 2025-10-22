<div class="w-full h-screen bg-slate-900 text-white flex flex-col items-center justify-center p-4">

    {{-- FASE 1: MINIJUEGO KAPLAY --}}
   @if ($stage === 'kaplay')
        <div wire:ignore class="relative w-full max-w-4xl aspect-video shadow-2xl">
            <canvas id="edugamer-canvas" class="w-full h-full"></canvas>
            {{-- Botones Estándar --}}
            <div class="absolute bottom-4 left-4 flex gap-4">
                <button id="leftBtn" class="w-16 h-16 rounded-full bg-amber-600/70 active:bg-amber-700/90 flex items-center justify-center text-2xl">❮</button>
                <button id="rightBtn" class="w-16 h-16 rounded-full bg-amber-600/70 active:bg-amber-700/90 flex items-center justify-center text-2xl">❯</button>
            </div>
            <div class="absolute bottom-4 right-4 flex gap-4">
                <button id="actionBtn" class="w-16 h-16 rounded-full bg-sky-600/70 active:bg-sky-700/90 flex items-center justify-center text-2xl">💬</button>
                <button id="jumpBtn" class="w-16 h-16 rounded-full bg-emerald-600/70 active:bg-emerald-700/90 flex items-center justify-center text-2xl">⬆️</button>
            </div>
        </div>
    @endif {{-- Closes kaplay --}}

    {{-- FASE 2: CÓDIGO DE ACCESO --}}
    @if ($stage === 'code_input')
        <flux:card class="w-full max-w-sm p-6">
            <flux:heading size="lg">Nivel 9 Superado</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:text>¡Has analizado los datos! Ingresa el código del reporte.</flux:text>
                <flux:input wire:model="codeInput" wire:keydown.enter="verifyCode" label="Código de Acceso" placeholder="XXXXX"/>
                @if($feedback) <flux:text class="text-red-500!" size="sm">{{ $feedback }}</flux:text> @endif
            </div>
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="verifyCode" variant="primary">Verificar</flux:button>
            </div>
        </flux:card>
    @endif {{-- Closes code_input --}}

    {{-- FASE 3: PUZZLE "INNOVATION PATHWAY" --}}
    @if ($stage === 'web_puzzle')
        <div class="w-full max-w-4xl h-full flex flex-col"> {{-- Más ancho --}}
            <flux:card class="flex-grow flex flex-col">
                {{-- Encabezado --}}
                <div class="p-6 border-b border-slate-700">
                    <flux:breadcrumbs>
                        <flux:breadcrumbs.item>Nivel 9</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item>Reto: Innovation Pathway</flux:breadcrumbs.item>
                    </flux:breadcrumbs>
                    <flux:heading size="xl" class="mt-4">Define tu Camino de Innovación</flux:heading>
                    <flux:text size="sm">Selecciona una opción en cada etapa para avanzar.</flux:text>
                </div>

                {{-- Contenido del Puzzle --}}
                <div class="p-6 flex-grow overflow-y-auto space-y-8">

                    {{-- Visualización del Camino --}}
                    <div class="flex items-center justify-between space-x-2 md:space-x-4 px-2">
                        @foreach($pathwayStages as $num => $stageData)
                            <div class="flex flex-col items-center text-center">
                                {{-- Icono de Etapa --}}
                                <div @class([
                                    'w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center border-2 transition-all',
                                    'bg-green-600 border-green-400' => $currentStageNum > $num, // Completado
                                    'bg-primary-600 border-primary-400 ring-4 ring-primary-500/50' => $currentStageNum === $num, // Actual
                                    'bg-slate-700 border-slate-600' => $currentStageNum < $num, // Pendiente
                                ])>
                                    @if($currentStageNum > $num)
                                        <flux:icon name="check" class="w-5 h-5 md:w-6 md:h-6 text-white" />
                                    @else
                                        <span class="font-bold text-lg md:text-xl">{{ $num }}</span>
                                    @endif
                                </div>
                                {{-- Título de Etapa --}}
                                <span @class([
                                    'mt-2 text-xs md:text-sm font-medium',
                                    'text-green-400' => $currentStageNum > $num,
                                    'text-primary-400' => $currentStageNum === $num,
                                    'text-slate-400' => $currentStageNum < $num,
                                ])>{{ $stageData['title'] }}</span>
                            </div>
                            {{-- Línea conectora --}}
                            @if(!$loop->last)
                                <div @class([
                                    'flex-1 h-1 rounded',
                                    'bg-green-500' => $currentStageNum > $num + 1, // Completado hasta el siguiente
                                    'bg-primary-500' => $currentStageNum === $num + 1, // Llegando al siguiente
                                    'bg-slate-700' => $currentStageNum <= $num, // Pendiente
                                ])></div>
                            @endif
                        @endforeach {{-- Fin loop pathway visual --}}
                    </div>

                    {{-- Opciones de la Etapa Actual --}}
                    <div class="mt-10 pt-6 border-t border-slate-700">
                         @if($isGameFinished)
                            {{-- Mensaje Final --}}
                            <div class="text-center">
                                <flux:heading size="2xl" class="text-green-400">¡Camino Completado!</flux:heading>
                                <flux:text class="mt-2">Has definido los pasos clave de tu estrategia.</flux:text>
                                <div class="mt-4 space-y-1 text-left bg-slate-800 p-4 rounded-lg inline-block">
                                    @foreach($choicesMade as $key => $choice)
                                         @php $num = explode('_', $key)[1]; @endphp
                                         <p><strong class="text-primary-400">{{ $pathwayStages[$num]['title'] }}:</strong> {{ $choice }}</p>
                                    @endforeach
                                </div>
                            </div>
                         @else
                             {{-- Muestra las opciones de la etapa actual --}}
                             @php $currentStageData = $pathwayStages[$currentStageNum]; @endphp
                            <flux:fieldset>
                                <flux:legend>Etapa {{ $currentStageNum }}: {{ $currentStageData['title'] }}</flux:legend>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                    @foreach($currentStageData['options'] as $option)
                                        <flux:button
                                            wire:click="selectChoice({{ $currentStageNum }}, '{{ $option }}')"
                                            variant="subtle" {{-- O usa 'outline' o 'filled' --}}
                                            class="w-full text-center justify-center"
                                        >
                                            {{ $option }}
                                        </flux:button>
                                    @endforeach {{-- Fin loop opciones --}}
                                </div>
                            </flux:fieldset>
                         @endif {{-- Fin if($isGameFinished) --}}
                    </div>
                </div>

                {{-- Footer: Botón de Continuar (solo si está terminado) --}}
                <div class="p-4 border-t border-slate-700">
                    <div class="flex justify-end items-center">
                        @if($isGameFinished)
                            <flux:button wire:click="$set('stage', 'completed')" variant="primary">Continuar</flux:button>
                        @endif
                        {{-- No se necesita botón "Siguiente" explícito --}}
                    </div>
                </div>
            </flux:card>
        </div>
    @endif {{-- Closes web_puzzle --}}

    {{-- FASE 4: NIVEL COMPLETADO --}}
    @if ($stage === 'completed')
        <flux:card class="w-full max-w-sm text-center p-6">
            <div class="flex flex-col items-center space-y-4">
                <flux:avatar size="xl" src="https://placehold.co/128x128/34d399/ffffff?text=🎯" /> {{-- Changed icon --}}
                <flux:heading size="lg">¡Felicidades!</flux:heading>
                <flux:text>Has completado el Innovation Pathway.</flux:text> {{-- Updated text --}}
                <div class="pt-4">
                    <flux:text size="sm">Calificación (Proceso Completado):</flux:text>
                    <flux:heading size="2xl" class="font-bold">{{ number_format($progress->score, 1) }} / 5.0</flux:heading>
                </div>
            </div>
            <div class="mt-6">
                <flux:button href="{{ route('menu.home') }}" class="w-full" variant="subtle">
                    Volver al Menú
                </flux:button>
            </div>
        </flux:card>
    @endif {{-- Closes completed --}}

</div>