export function loadCommonAssets(k) {
    // NUEVO SPRITE DEL JUGADOR
    k.loadSprite("player", "data:image/svg+xml," + encodeURIComponent(`
        <svg width="40" height="48" viewBox="0 0 40 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 48L10 30L14 22L26 22L30 30L30 48L10 48Z" fill="#7A5C35"/>
            <rect x="10" y="20" width="20" height="18" fill="#A47D49"/>
            <rect x="15" y="19" width="10" height="4" fill="#D3A05A"/>
            <path d="M12 19C12 15.6863 14.6863 13 18 13H22C25.3137 13 28 15.6863 28 19V20H12V19Z" fill="#F0E0C4"/>
            <path d="M12 13L10 5H30L28 13" fill="#5A422A"/>
            <circle cx="15" cy="9" r="4" fill="#C0A060" stroke="#3D2B1F" stroke-width="1.5"/>
            <circle cx="25" cy="9" r="4" fill="#C0A060" stroke="#3D2B1F" stroke-width="1.5"/>
        </svg>
    `));
    // --- MANTÉN EL RESTO DE ASSETS ---
    k.loadSprite("turtle", "data:image/svg+xml," + encodeURIComponent(`<svg width="48" height="40" xmlns="http://www.w3.org/2000/svg"><path d="M 5 20 A 20 15 0 0 1 43 20 L 43 35 L 5 35 Z" fill="#6b8e23"/><circle cx="35" cy="18" r="5" fill="#9acd32"/><circle cx="35" cy="18" r="1" fill="black"/><path d="M 38 17 A 1 1 0 0 1 36 15" stroke="black" fill="none" stroke-width="1"/></svg>`));
}
//... resto del archivo
export function loadLevelAssets(k) {
    // Si un nivel tiene assets específicos, se cargan aquí
    k.loadSprite("gear", "data:image/svg+xml," + encodeURIComponent(`<svg width="24" height="24" viewBox="0 0 24 24" fill="#c0a060" xmlns="http://www.w3.org/2000/svg"><path d="M12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5zm7.43-3.5c.04-.32.07-.64.07-.97 0-.33-.03-.66-.07-1l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61-.25-1.17.59-1.69-.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.34-.07.67-.07 1s.03.66.07.97l-2.11 1.65c-.19-.15-.24-.42-.12-.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73-1.69-.98l-.38 2.65c.03.24.24.42.49.42h4c.25 0,.46-.18.49-.42l-.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65z"/></svg>`));
    k.loadSprite("key", "data:image/svg+xml," + encodeURIComponent(`
        <svg width="24" height="48" viewBox="0 0 24 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" stroke="#C0A060" stroke-width="4"/>
            <rect x="10" y="20" width="4" height="28" fill="#C0A060"/>
            <rect x="14" y="32" width="8" height="4" fill="#C0A060"/>
            <rect x="14" y="40" width="8" height="4" fill="#C0A060"/>
        </svg>
    `));

    // --- ¡AÑADIR ESTE NUEVO SPRITE! ---
    k.loadSprite("scroll", "data:image/svg+xml," + encodeURIComponent(`
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 3H30C33.866 3 37 6.13401 37 10V30C37 33.866 33.866 37 30 37H10C6.13401 37 3 33.866 3 30V10C3 6.13401 6.13401 3 10 3Z" fill="#F0E0C4"/>
            <path d="M10 5H30C32.7614 5 35 7.23858 35 10V30C35 32.7614 32.7614 35 30 35H10C7.23858 35 5 32.7614 5 30V10C5 7.23858 7.23858 5 10 5Z" fill="#FFF8E8"/>
            <rect x="10" y="12" width="20" height="2" rx="1" fill="#C0A060"/>
            <rect x="10" y="18" width="20" height="2" rx="1" fill="#D3A05A"/>
            <rect x="10" y="24" width="14" height="2" rx="1" fill="#C0A060"/>
        </svg>
    `));

    k.loadSprite("lightbulb", "data:image/svg+xml," + encodeURIComponent(`
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 2C14.4772 2 10 6.47715 10 12C10 15.6369 11.9765 18.8872 14.9984 20.8906V25C14.9984 26.1046 15.8938 27 16.9984 27H23.0016C24.1062 27 25.0016 26.1046 25.0016 25V20.8906C28.0235 18.8872 30 15.6369 30 12C30 6.47715 25.5228 2 20 2Z" fill="#FFEB3B" stroke="#F57F17" stroke-width="1.5"/>
            <rect x="16" y="27" width="8" height="6" rx="1" fill="#B0BEC5"/>
            <rect x="17" y="33" width="6" height="3" rx="1.5" fill="#78909C"/>
            <path d="M18 12L20 18L22 12L20 6L18 12Z" fill="#FFF9C4"/>
        </svg>
    `));

    k.loadSprite("clipboard", "data:image/svg+xml," + encodeURIComponent(`
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 4H30C31.1046 4 32 4.89543 32 6V34C32 35.1046 31.1046 36 30 36H10C8.89543 36 8 35.1046 8 34V6C8 4.89543 8.89543 4 10 4Z" fill="#BCAAA4"/>
            <path d="M14 2H26C27.1046 2 28 2.89543 28 4V8H12V4C12 2.89543 12.8954 2 14 2Z" fill="#795548"/>
            <rect x="14" y="12" width="12" height="2" rx="1" fill="#FFF"/>
            <rect x="14" y="18" width="12" height="2" rx="1" fill="#FFF"/>
            <rect x="14" y="24" width="8" height="2" rx="1" fill="#FFF"/>
        </svg>
    `));

    k.loadSprite("data", "data:image/svg+xml," + encodeURIComponent(`
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="4" y="8" width="32" height="24" rx="2" fill="#2196F3"/>
            <rect x="8" y="12" width="8" height="8" fill="#BBDEFB"/>
            <rect x="8" y="22" width="8" height="6" fill="#BBDEFB"/>
            <rect x="20" y="12" width="12" height="2" fill="#BBDEFB"/>
            <rect x="20" y="16" width="12" height="2" fill="#BBDEFB"/>
            <rect x="20" y="20" width="12" height="2" fill="#BBDEFB"/>
            <rect x="20" y="24" width="12" height="2" fill="#BBDEFB"/>
        </svg>
    `));

    k.loadSprite("fragment", "data:image/svg+xml," + encodeURIComponent(`
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 5L15 15L10 25L5 15L10 5Z" fill="#9C27B0"/>
            <path d="M20 10L25 20L20 30L15 20L20 10Z" fill="#BA68C8"/>
            <path d="M30 5L35 15L30 25L25 15L30 5Z" fill="#E1BEE7"/>
        </svg>
    `));
    // level 8
    k.loadSprite("ship", "data:image/svg+xml," + encodeURIComponent(`
        <svg width="40" height="30" viewBox="0 0 40 30" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 15L40 0V30L0 15Z" fill="#03A9F4"/>
            <path d="M30 10V20L40 15L30 10Z" fill="#FFC107"/>
        </svg>
    `));
    // Orbe de Idea
    k.loadSprite("idea_orb", "data:image/svg+xml," + encodeURIComponent(`
        <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="15" cy="15" r="14" fill="#FFEB3B" stroke="#FBC02D" stroke-width="2"/>
            <path d="M13 10L15 16L17 10L15 4L13 10Z" fill="#FFF9C4"/>
        </svg>
    `));
    // Asteroide simple
    k.loadSprite("asteroid", "data:image/svg+xml," + encodeURIComponent(`
       <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 0L30 5L40 20L35 30L20 40L10 35L0 20L5 10L20 0Z" fill="#795548"/>
            <circle cx="10" cy="10" r="3" fill="#A1887F"/>
             <circle cx="28" cy="25" r="4" fill="#A1887F"/>
       </svg>
    `));

    //level 9
    k.loadSprite("data_point", "data:image/svg+xml," + encodeURIComponent(`
        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="4" y="24" width="6" height="4" fill="#4CAF50"/>
            <rect x="13" y="16" width="6" height="12" fill="#2196F3"/>
            <rect x="22" y="8" width="6" height="20" fill="#FFC107"/>
            <path d="M2 28H30" stroke="#90A4AE" stroke-width="2"/>
        </svg>
    `));
    k.loadSprite("noise", "data:image/svg+xml," + encodeURIComponent(`
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 5L10 15L5 25L15 35L25 25L35 35L35 5L25 15L15 5L5 5Z" stroke="#F44336" stroke-width="3" fill="none"/>
            <path d="M10 30L20 20L30 30" stroke="#F44336" stroke-width="2" fill="none"/>
             <path d="M10 10L20 20L30 10" stroke="#F44336" stroke-width="2" fill="none"/>
        </svg>
    `));
    k.loadSprite("analysis_station", "data:image/svg+xml," + encodeURIComponent(`
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="2" y="4" width="36" height="24" rx="2" fill="#607D8B"/>
            <rect x="5" y="7" width="30" height="18" fill="#CFD8DC"/>
            <path d="M12 36H28L30 28H10L12 36Z" fill="#455A64"/>
            <circle cx="20" cy="38" r="2" fill="#37474F"/>
        </svg>
    `));
}