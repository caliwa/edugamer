import { getPlayerSpeed, getPlayerJumpForce } from '../entities/player.js';

let isMovingLeft = false;
export let isMovingRight = false; // Mantenemos exportación por si acaso, aunque no lo usemos activamente
export let isJumpButtonPressed = false;

export function resetControlState() {
    isMovingLeft = false;
    isMovingRight = false;
    isJumpButtonPressed = false;
}

function jump(k) {
    const p = k.get("player")[0];
    // Añade comprobación por si p no existe o no tiene isGrounded (para niveles top-down futuros si los hubiera)
    if (p && typeof p.isGrounded === 'function' && p.isGrounded()) {
        p.jump(getPlayerJumpForce());
    }
}

function setupButtonListeners(k) {
    const leftBtn = document.getElementById('leftBtn');
    const rightBtn = document.getElementById('rightBtn');
    const jumpBtn = document.getElementById('jumpBtn');

    if (!leftBtn || !rightBtn || !jumpBtn || (leftBtn && leftBtn.dataset.listenerAttached)) return;

    const preventDefault = (e) => { e.preventDefault(); e.stopPropagation(); };

    // --- Botones Izquierda/Derecha ---
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

    // --- Botón de Salto ---
    jumpBtn.addEventListener('touchstart', (e) => { preventDefault(e); isJumpButtonPressed = true; jump(k); });
    jumpBtn.addEventListener('mousedown', (e) => { preventDefault(e); isJumpButtonPressed = true; jump(k); });
    jumpBtn.addEventListener('touchend', (e) => { preventDefault(e); isJumpButtonPressed = false; });
    jumpBtn.addEventListener('touchcancel', (e) => { isJumpButtonPressed = false; });
    jumpBtn.addEventListener('mouseup', (e) => { preventDefault(e); isJumpButtonPressed = false; });
    jumpBtn.addEventListener('mouseleave', (e) => { isJumpButtonPressed = false; });

    leftBtn.dataset.listenerAttached = 'true';
}

export function setupPlayerControls(k, player) {
    setupButtonListeners(k);

    // --- Teclado para Izquierda/Derecha/Salto ---
    k.onKeyPress("left", () => { isMovingLeft = true; });
    k.onKeyPress("a", () => { isMovingLeft = true; });
    k.onKeyRelease("left", () => { isMovingLeft = false; });
    k.onKeyRelease("a", () => { isMovingLeft = false; });

    k.onKeyPress("right", () => { isMovingRight = true; });
    k.onKeyPress("d", () => { isMovingRight = true; });
    k.onKeyRelease("right", () => { isMovingRight = false; });
    k.onKeyRelease("d", () => { isMovingRight = false; });

    // Salto con teclado (para niveles de plataforma)
    k.onKeyPress(["space", "up", "w"], () => {
         const p = k.get("player")[0];
         // Solo intenta saltar si existe isGrounded (niveles de plataforma)
         if (p && typeof p.isGrounded === 'function' && p.isGrounded()) {
            jump(k);
         }
         isJumpButtonPressed = true; // Útil para Jetpack (Nivel 6)
    });
     k.onKeyRelease(["space", "up", "w"], () => {
         isJumpButtonPressed = false;
     });

    // Movimiento lateral aplicado aquí para niveles de plataforma
    k.onUpdate(() => {
        const p = k.get("player")[0]; // Obtiene el jugador actual
        if (!p) return;

        // Solo aplica movimiento L/R si el nivel TIENE gravedad (es plataforma)
        if (k.getGravity() > 0) {
            if (isMovingLeft) p.move(-getPlayerSpeed(), 0);
            if (isMovingRight) p.move(getPlayerSpeed(), 0);
        }
    });
}