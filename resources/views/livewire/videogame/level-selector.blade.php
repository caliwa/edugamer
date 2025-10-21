<div>
    <div class="relative w-[95%] mx-auto pt-8">
        <div class="relative bg-white rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-gray-700 dark:to-gray-800">
                <div class="flex items-center justify-between p-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white/20 rounded-lg">
                            <flux:icon.squares-2x2 variant="solid" class="text-white" />
                        </div>
                        <flux:heading size="xl" class="!text-white">
                            Mapa de Niveles
                        </flux:heading>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

                    @foreach ($levels as $level)
                        @php
                            // Determinamos el estado del nivel para aplicar estilos
                            $progress = $progressData[$level['number']] ?? null;
                            $isLocked = $level['number'] !== 1 && (!$progress || $progress['status'] === 'locked');
                            $isCompleted = $progress && $progress['status'] === 'completed';
                        @endphp

                        <button
                            wire:click="selectLevel({{ $level['number'] }})"
                            @if($isLocked) disabled @endif
                            class="group relative text-left bg-gradient-to-br from-{{ $level['color'] }}-50 to-{{ $level['color'] }}-100 dark:from-{{ $level['color'] }}-900/20 dark:to-{{ $level['color'] }}-800/20 rounded-xl p-6 transition-all duration-300 border border-{{ $level['color'] }}-200 dark:border-{{ $level['color'] }}-700/50
                                @if($isLocked)
                                    opacity-60 grayscale cursor-not-allowed
                                @else
                                    cursor-pointer hover:shadow-xl hover:scale-105
                                @endif"
                        >
                            {{-- Icono de estado (Bloqueado o Completado) --}}
                            <div class="absolute top-4 right-4 h-10 w-10 flex items-center justify-center bg-white/50 dark:bg-black/20 rounded-full text-{{ $level['color'] }}-600 dark:text-{{ $level['color'] }}-300 text-lg font-bold group-hover:opacity-100 transition-opacity
                                {{ $isLocked ? 'opacity-100' : 'opacity-50' }}">
                                @if($isLocked)
                                    <flux:icon name="lock-closed" class="h-6 w-6"/>
                                @elseif($isCompleted)
                                    <flux:icon name="check-circle" class="h-6 w-6 text-green-500"/>
                                @else
                                    {{ $level['number'] }}
                                @endif
                            </div>

                            <div class="relative z-10">
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="p-3 bg-{{ $level['color'] }}-500 rounded-lg shadow-lg">
                                        <flux:icon name="{{ $level['icon'] }}" class="text-white" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg">Nivel {{ $level['number'] }}</flux:heading>
                                        <flux:description>{{ $level['title'] }}</flux:description>
                                    </div>
                                </div>
                                <flux:description size="md" class="h-12">{{ $level['description'] }}</flux:description>
                                <flux:badge class="mt-4" color="{{ $level['color'] }}">
                                    Sesión {{ $level['number'] }}
                                </flux:badge>
                            </div>
                        </button>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</div>