<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('/img/equisol.ico') }}">
    
    <title>{{ $title ?? config('app.name', 'Page Title') }}</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
    
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body>
    <div class="flex h-screen">
        <livewire:layouts.sidebar-component />
        <div class="relative w-full flex flex-col overflow-y-auto overflow-x-auto">
            <livewire:layouts.navbar-component />
            
            <main class="w-full">
                    {{ $slot }}
            </main>
        </div>
    </div>
    
    @livewireScripts
    @fluxScripts
    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist
</body>
</html>