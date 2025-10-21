import { getPlayerSpeed, getPlayerJumpForce } from '../entities/player.js';

let isMovingLeft = false;
let isMovingRight = false;
export let isJumpButtonPressed = false; // <-- NUEVA VARIABLE EXPORTADA

export function resetControlState() {
    isMovingLeft = false;
    isMovingRight = false;
    isJumpButtonPressed = false; // <-- Resetéala también
}

function jump(k) {
    const p = k.get("player")[0];
    if (p && p.isGrounded()) {
        p.jump(getPlayerJumpForce());
    }
}

function setupButtonListeners(k) {
    const leftBtn = document.getElementById('leftBtn');
    const rightBtn = document.getElementById('rightBtn');
    const jumpBtn = document.getElementById('jumpBtn');
    
    if (!leftBtn || !rightBtn || !jumpBtn || leftBtn.dataset.listenerAttached) return;
    
    const preventDefault = (e) => { e.preventDefault(); e.stopPropagation(); };

    // --- Botones Izquierda/Derecha (sin cambios) ---
    leftBtn.addEventListener('mousedown', (e) => { preventDefault(e); isMovingLeft = true; });
    leftBtn.addEventListener('mouseup', (e) => { preventDefault(e); isMovingLeft = false; });
    leftBtn.addEventListener('mouseleave', (e) => { isMovingLeft = false; });
    leftBtn.addEventListener('touchstart', (e) => { preventDefault(e); isMovingLeft = true; });
    leftBtn.addEventListener('touchend', (e) => { preventDefault(e); isMovingLeft = false; });
    leftBtn.addEventListener('touchcancel', (e) => { isMovingLeft = false; });

    rightBtn.addEventListener('mousedown', (e) => { preventDefault(e); isMovingRight = true; });
    rightBtn.addEventListener('mouseup', (e) => { preventDefault(e); isMovingRight = false; });
    rightBtn.addEventListener('mouseleave', (e) => { isMovingRight = false; });
    rightBtn.addEventListener('touchstart', (e) => { preventDefault(e); isMovingRight = true; });
    rightBtn.addEventListener('touchend', (e) => { preventDefault(e); isMovingRight = false; });
    rightBtn.addEventListener('touchcancel', (e) => { isMovingRight = false; });

    // --- Botón de Salto (MODIFICADO) ---
    // Al presionar: intenta saltar (si está en el suelo) Y marca el botón como presionado.
    jumpBtn.addEventListener('touchstart', (e) => { 
        preventDefault(e); 
        isJumpButtonPressed = true;
        jump(k); // Intenta el salto inicial
    });
    jumpBtn.addEventListener('mousedown', (e) => { 
        preventDefault(e); 
        isJumpButtonPressed = true;
        jump(k); // Intenta el salto inicial
    });
    
    // Al soltar: marca el botón como no presionado.
    jumpBtn.addEventListener('touchend', (e) => { preventDefault(e); isJumpButtonPressed = false; });
    jumpBtn.addEventListener('touchcancel', (e) => { isJumpButtonPressed = false; });
    jumpBtn.addEventListener('mouseup', (e) => { preventDefault(e); isJumpButtonPressed = false; });
    jumpBtn.addEventListener('mouseleave', (e) => { isJumpButtonPressed = false; });
    
    leftBtn.dataset.listenerAttached = 'true';
}

export function setupPlayerControls(k, player) {
    setupButtonListeners(k);

    // --- Teclado (sin cambios relevantes aquí, aunque podrías quitar los onKeyPress/onKeyRelease de salto si quieres que SOLO funcione el botón) ---
    k.onKeyPress("left", () => { isMovingLeft = true; });
    k.onKeyPress("a", () => { isMovingLeft = true; });
    k.onKeyRelease("left", () => { isMovingLeft = false; });
    k.onKeyRelease("a", () => { isMovingLeft = false; });

    k.onKeyPress("right", () => { isMovingRight = true; });
    k.onKeyPress("d", () => { isMovingRight = true; });
    k.onKeyRelease("right", () => { isMovingRight = false; });
    k.onKeyRelease("d", () => { isMovingRight = false; });
    
    // --- MANTENEMOS ESTO PARA EL TECLADO ---
    k.onKeyPress(["space", "up", "w"], () => jump(k)); 
    // ------------------------------------

    k.onUpdate(() => {
        if (!player) return;
        if (isMovingLeft) player.move(-getPlayerSpeed(), 0);
        if (isMovingRight) player.move(getPlayerSpeed(), 0);
    });
}