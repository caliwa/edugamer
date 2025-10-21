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
}