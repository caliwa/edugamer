@assets
    {{-- Importación de TailwindCSS y Google Fonts --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Uncial+Antiqua&family=Vast+Shadow&display=swap" rel="stylesheet">

    <style>
        .font-title { font-family: 'Vast+Shadow', cursive; }
        .font-body { font-family: 'Uncial+Antiqua', cursive; }

        .bg-steampunk-workshop {
            background-color: #1a1412;
            background-image: 
                radial-gradient(ellipse at center, rgba(0,0,0,0) 0%, rgba(0,0,0,0.8) 100%),
                url('https://www.transparenttextures.com/patterns/worn-dots.png'),
                linear-gradient(to top, #3a2f20, #1a1412);
            background-attachment: fixed;
        }

        /* --- KEYFRAME ANIMATIONS --- */
        @keyframes rotate-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes rotate-medium-counter { from { transform: rotate(360deg); } to { transform: rotate(0deg); } }
        @keyframes rotate-fast { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        
        @keyframes steam-drift {
            0% { transform: translateY(20px) scale(0.8); opacity: 0; }
            20% { opacity: 0.6; }
            80% { opacity: 0.2; }
            100% { transform: translateY(-120px) scale(1.5); opacity: 0; }
        }

        @keyframes gauge-flicker {
            0%, 100% { transform: rotate(-15deg); }
            50% { transform: rotate(15deg); }
        }

        @keyframes core-pulse {
            0%, 100% { box-shadow: 0 0 20px 5px #ffab00, inset 0 0 10px #ffab00; opacity: 0.8; }
            50% { box-shadow: 0 0 35px 15px #ffab00, inset 0 0 15px #ffab00; opacity: 1; }
        }
        
        @keyframes text-glow {
             0%, 100% { text-shadow: 0 0 5px #ffc857, 0 0 10px #ffc857, 0 0 15px #ffc857, 0 0 20px #e09d00; }
             50% { text-shadow: 0 0 10px #ffda8d, 0 0 20px #ffda8d, 0 0 30px #ffda8d, 0 0 40px #e09d00; }
        }

        /* --- Clases de utilidad para animaciones --- */
        .animate-rotate-slow { animation: rotate-slow 50s linear infinite; }
        .animate-rotate-medium-counter { animation: rotate-medium-counter 22s linear infinite; }
        .animate-rotate-fast { animation: rotate-fast 10s linear infinite; }
        .animate-gauge-flicker { animation: gauge-flicker 1.5s ease-in-out infinite alternate; }
        .animate-core-pulse { animation: core-pulse 3s ease-in-out infinite; }
        .steam { animation: steam-drift 8s linear infinite; }
        .animate-text-glow { animation: text-glow 2.5s ease-in-out infinite; }

        /* --- Estilos de Componentes Visuales --- */
        .steampunk-console {
            background: linear-gradient(145deg, #5c4d3d, #3e3228);
            border: 4px solid #b8860b;
            border-image-source: linear-gradient(to top left, #b8860b, #e6b864, #b8860b);
            border-image-slice: 1;
            box-shadow: 
                inset 0 0 25px rgba(0,0,0,0.8), 
                0px 10px 40px rgba(0,0,0,0.7);
            transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        }
    </style>
@endassets

{{-- El div raíz del componente Livewire --}}
<div wire:click="advance" class="relative min-h-screen flex items-center justify-center p-4 bg-steampunk-workshop font-body text-[#d4c098] cursor-pointer group overflow-hidden">
    
    <!-- Efectos de vapor en el fondo -->
    <div class="absolute bottom-0 left-0 w-full h-1/2 pointer-events-none">
        <div class="steam absolute bottom-0 left-[10%]" style="animation-delay: 0s;"></div>
        <div class="steam absolute bottom-0 left-[80%]" style="animation-delay: -3s;"></div>
        <div class="steam absolute bottom-0 left-[45%]" style="animation-delay: -6s; transform: scale(0.8);"></div>
    </div>

    <!-- Engranajes decorativos del fondo -->
    <svg class="absolute top-[-10%] left-[-15%] w-1/2 max-w-xs text-[#d4c098]/10 animate-rotate-slow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.994 12.863c-.012.33-.04.658-.083.982l1.954 1.51c.322.25.398.701.148 1.023l-1.999 3.464c-.25.433-.747.575-1.18.325l-2.233-1.29c-.69.56-1.442 1.033-2.247 1.393l-.324 2.479c-.077.584-.564 1.023-1.161 1.023h-3.998c-.597 0-1.084-.439-1.161-1.023l-.324-2.479c-.805-.36-1.557-.833-2.247-1.393l-2.233 1.29c-.433.25-.93.108-1.18-.325L2.01 16.378c-.25-.433-.174-.884.148-1.133l1.954-1.51c-.042-.324-.071-.652-.083-.982a8.097 8.097 0 0 1 0-1.726c.012-.33.04-.658.083-.982l-1.954-1.51c-.322-.25-.398-.701-.148-1.023l1.999-3.464c.25-.433.747-.575 1.18-.325l2.233 1.29c.69-.56 1.442-1.033 2.247-1.393l.324-2.479c.077.584.564 1.023 1.161 1.023h3.998c.597 0 1.084.439 1.161 1.023l.324 2.479c.805.36 1.557.833 2.247 1.393l2.233-1.29c.433.25.93.108 1.18.325l1.999 3.464c.25.433.174.884-.148 1.133l-1.954 1.51c.042.324.071.652.083.982.023.284.035.57.035.863s-.012.579-.035.863zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"/></svg>
    <svg class="absolute bottom-[-15%] right-[-10%] w-2/3 max-w-md text-[#d4c098]/10 animate-rotate-medium-counter" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 8.5 A3.5 3.5 0 0 1 12 15.5 A3.5 3.5 0 0 1 12 8.5 M12 7 A5 5 0 0 0 12 17 A5 5 0 0 0 12 7z M19.4 11l-2.24 0 A6.95 6.95 0 0 0 16.3 8.3l1.58-1.58c.4-.4.4-1 0-1.4l-1-1c-.4-.4-1-.4-1.4 0L13.9 5.9A6.95 6.95 0 0 0 11.2 5.04L11.2 2.8c0-.55-.45-1-1-1h-1.4c-.55 0-1 .45-1 1l0 2.24A6.95 6.95 0 0 0 5.1 5.9L3.5 4.3c-.4-.4-1-.4-1.4 0l-1 1c-.4.4-.4 1 0 1.4l1.58 1.58A6.95 6.95 0 0 0 4.8 11l-2.24 0c-.55 0-1 .45-1 1v1.4c0 .55.45 1 1 1l2.24 0A6.95 6.95 0 0 0 5.7 17.3L4.1 18.9c-.4.4-.4 1 0 1.4l1 1c.4.4 1 .4 1.4 0l1.58-1.58A6.95 6.95 0 0 0 10.8 20.96l0 2.24c0 .55.45 1 1 1h1.4c.55 0 1-.45 1-1l0-2.24A6.95 6.95 0 0 0 16.3 18.1l1.58 1.58c.4.4 1 .4 1.4 0l1-1c.4-.4.4-1 0-1.4L18.3 15.1A6.95 6.95 0 0 0 19.2 12.4l2.24 0c.55 0 1-.45 1-1V10c0-.55-.45-1-1-1z"/></svg>

    <!-- La Consola Steampunk Central -->
    <div style="perspective: 1500px;">
        <div class="relative w-full max-w-sm steampunk-console rounded-2xl p-6 transform transition-transform duration-500 group-hover:scale-105 group-hover:-rotate-y-3 group-hover:-rotate-x-2">

            <!-- Decoraciones: Tuberías y Manómetro -->
            <div class="absolute -top-10 -left-12 w-24 h-48 border-l-8 border-t-8 border-[#6b5b44] rounded-tl-full z-0"></div>
            <div class="absolute -bottom-10 -right-12 w-24 h-48 border-r-8 border-b-8 border-[#6b5b44] rounded-br-full z-0"></div>
            
            <div class="absolute -top-8 -right-8 w-24 h-24 bg-[#3e3228] rounded-full p-2 border-4 border-[#b8860b] shadow-lg">
                <div class="relative w-full h-full bg-[#1a1412] rounded-full flex items-center justify-center">
                    <div class="absolute w-2 h-1/2 bg-red-500 rounded-full origin-bottom animate-gauge-flicker" style="box-shadow: 0 0 5px red;"></div>
                    <div class="w-2 h-2 bg-[#d4c098] rounded-full z-10"></div>
                </div>
            </div>

            <!-- Contenido Principal de la Consola -->
            <div class="relative z-10 text-center">
                <!-- Placa del Título -->
                <div class="bg-gradient-to-r from-[#5c4033] to-[#4a3f30] p-2 border-2 border-[#b8860b] rounded-md shadow-inner">
                    <h1 class="font-title text-5xl md:text-6xl text-transparent bg-clip-text bg-gradient-to-b from-yellow-100 to-amber-300 drop-shadow-lg"
                        style="text-shadow: 0 2px 4px #000;">
                        Edugamer
                    </h1>
                </div>

                <!-- Mecanismo Central -->
                <div class="my-8 h-36 relative flex justify-center items-center">
                    <div class="absolute w-28 h-28 rounded-full bg-black/30 animate-core-pulse"></div>
                    <svg class="w-36 h-36 text-[#d4c098]/30 animate-rotate-medium-counter absolute" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.994 12.863c-.012.33-.04.658-.083.982l1.954 1.51c.322.25.398.701.148 1.023l-1.999 3.464c-.25.433-.747.575-1.18.325l-2.233-1.29c-.69.56-1.442 1.033-2.247 1.393l-.324 2.479c-.077.584-.564 1.023-1.161 1.023h-3.998c-.597 0-1.084-.439-1.161-1.023l-.324-2.479c-.805-.36-1.557-.833-2.247-1.393l-2.233 1.29c-.433.25-.93.108-1.18-.325L2.01 16.378c-.25-.433-.174-.884.148-1.133l1.954-1.51c-.042-.324-.071-.652-.083-.982a8.097 8.097 0 0 1 0-1.726c.012-.33.04-.658.083-.982l-1.954-1.51c-.322-.25-.398-.701-.148-1.023l1.999-3.464c.25-.433.747-.575 1.18-.325l2.233 1.29c.69-.56 1.442-1.033 2.247-1.393l.324-2.479c.077.584.564 1.023 1.161 1.023h3.998c.597 0 1.084.439 1.161 1.023l.324 2.479c.805.36 1.557.833 2.247 1.393l2.233-1.29c.433.25.93.108 1.18.325l1.999 3.464c.25.433.174.884-.148 1.133l-1.954 1.51c.042.324.071.652.083.982.023.284.035.57.035.863s-.012.579-.035.863zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"/></svg>
                    <svg class="w-24 h-24 text-[#b8860b] animate-rotate-fast drop-shadow-lg absolute" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 8.5 A3.5 3.5 0 0 1 12 15.5 A3.5 3.5 0 0 1 12 8.5 M12 7 A5 5 0 0 0 12 17 A5 5 0 0 0 12 7z M19.4 11l-2.24 0 A6.95 6.95 0 0 0 16.3 8.3l1.58-1.58c.4-.4.4-1 0-1.4l-1-1c-.4-.4-1-.4-1.4 0L13.9 5.9A6.95 6.95 0 0 0 11.2 5.04L11.2 2.8c0-.55-.45-1-1-1h-1.4c-.55 0-1 .45-1 1l0 2.24A6.95 6.95 0 0 0 5.1 5.9L3.5 4.3c-.4-.4-1-.4-1.4 0l-1 1c-.4.4-.4 1 0 1.4l1.58 1.58A6.95 6.95 0 0 0 4.8 11l-2.24 0c-.55 0-1 .45-1 1v1.4c0 .55.45 1 1 1l2.24 0A6.95 6.95 0 0 0 5.7 17.3L4.1 18.9c-.4.4-.4 1 0 1.4l1 1c.4.4 1 .4 1.4 0l1.58-1.58A6.95 6.95 0 0 0 10.8 20.96l0 2.24c0 .55.45 1 1 1h1.4c.55 0 1-.45 1-1l0-2.24A6.95 6.95 0 0 0 16.3 18.1l1.58 1.58c.4.4 1 .4 1.4 0l1-1c-.4-.4.4-1 0-1.4L18.3 15.1A6.95 6.95 0 0 0 19.2 12.4l2.24 0c.55 0 1-.45 1-1V10c0-.55-.45-1-1-1z"/></svg>
                </div>

                <!-- Botón de Llamada a la Acción -->
                <div class="mt-4">
                    <p class="font-body text-2xl tracking-wider text-[#ffc857] animate-text-glow">
                        Click para Avanzar
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

