<div class="flex min-h-screen">
    <div class="flex-1 flex justify-center items-center p-8">
        <div class="w-full max-w-md space-y-6">
            <div class="flex justify-center opacity-50">
                <a href="/" class="group flex items-center gap-3">
                    <svg class="h-4 text-zinc-800 dark:text-white" viewBox="0 0 18 13" fill="none" xmlns="http://www.w.org/2000/svg"><g><line x1="1" y1="5" x2="1" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line><line x1="5" y1="1" x2="5" y2="8" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line><line x1="9" y1="5" x2="9" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line><line x1="13" y1="1" x2="13" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line><line x1="17" y1="5" x2="17" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line></g></svg>
                    <span class="text-xl font-semibold text-zinc-800 dark:text-white">flux</span>
                </a>
            </div>

            <flux:heading class="text-center" size="xl">Crea tu cuenta en Edugamer</flux:heading>
            <flux:separator />

            <div class="flex flex-col gap-4">
                <flux:input wire:model="cedula" label="Cédula" type="text" placeholder="Tu número de identificación" />

                <flux:input wire:model="username" label="Usuario" type="text" placeholder="Elige un nombre de usuario" />

                {{-- Nombres y Apellidos en una grilla para mejor layout --}}
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="firstName" label="Primer Nombre" type="text" placeholder="Ej: Juan" />
                    <flux:input wire:model="secondName" label="Segundo Nombre (Opcional)" type="text" placeholder="Ej: David" />
                    <flux:input wire:model="firstSurname" label="Primer Apellido" type="text" placeholder="Ej: Pérez" />
                    <flux:input wire:model="secondSurname" label="Segundo Apellido (Opcional)" type="text" placeholder="Ej: Gómez" />
                </div>

                <flux:input wire:model="password" label="Contraseña" type="password" placeholder="Mínimo 8 caracteres" />
                <flux:input wire:model="password_confirmation" label="Confirmar Contraseña" type="password" placeholder="Repite tu contraseña" />

                <flux:button wire:click="register" variant="primary" color="orange" class="w-full mt-4">Registrarme</flux:button>

                <div class="text-center">
                    <flux:link href="{{-- AQUÍ VA LA RUTA A TU LOGIN --}}" variant="subtle" class="text-sm">¿Ya tienes una cuenta? Inicia sesión</flux:link>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel derecho con la imagen (igual que en tu login) --}}
    <div class="flex-1 p-4 max-lg:hidden">
        <div class="text-white relative rounded-lg h-full w-full bg-zinc-900 flex flex-col items-start justify-end p-16" style="background-image: url('https://kaplayjs.com/_astro/kaplay-banner-logo.NXCErr6D_KQVl4.gif'); background-size: cover">
            <div class="flex gap-2 mb-4">
                <flux:icon.star variant="solid" /><flux:icon.star variant="solid" /><flux:icon.star variant="solid" /><flux:icon.star variant="solid" /><flux:icon.star variant="solid" />
            </div>
            <div class="mb-6 italic font-base text-amber-700 text-3xl xl:text-4xl">Edugamer</div>
            <div class="flex gap-4">
                <flux:avatar src="https://www.logo.wine/a/logo/Laravel/Laravel-Logo.wine.svg" size="xl" />
                <div class="flex flex-col justify-center font-medium">
                    <div class="text-lg">App Móvil</div>
                    <div class="text-zinc-300">Re-estructurando la educación</div>
                </div>
            </div>
        </div>
    </div>
</div>