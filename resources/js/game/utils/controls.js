import { getPlayerSpeed, getPlayerJumpForce } from '../entities/player.js';

let isMovingLeft = false;
let isMovingRight = false;

export function resetControlState() {
    isMovingLeft = false;
    isMovingRight = false;
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

    jumpBtn.addEventListener('touchstart', (e) => { preventDefault(e); jump(k); });
    jumpBtn.addEventListener('mousedown', (e) => { preventDefault(e); jump(k); });
    
    leftBtn.dataset.listenerAttached = 'true';
}

export function setupPlayerControls(k, player) {
    setupButtonListeners(k);

    // --- CORRECCIÓN: Listeners para el Teclado con los nombres correctos ---
    // Mover a la izquierda
    k.onKeyPress("left", () => { isMovingLeft = true; });
    k.onKeyPress("a", () => { isMovingLeft = true; });
    k.onKeyRelease("left", () => { isMovingLeft = false; });
    k.onKeyRelease("a", () => { isMovingLeft = false; });

    // Mover a la derecha
    k.onKeyPress("right", () => { isMovingRight = true; });
    k.onKeyPress("d", () => { isMovingRight = true; });
    k.onKeyRelease("right", () => { isMovingRight = false; });
    k.onKeyRelease("d", () => { isMovingRight = false; });

    // Saltar
    k.onKeyPress("space", () => jump(k));
    k.onKeyPress("up", () => jump(k));
    k.onKeyPress("w", () => jump(k));
    // --------------------------------------------------------------------

    k.onUpdate(() => {
        if (!player) return;
        if (isMovingLeft) player.move(-getPlayerSpeed(), 0);
        if (isMovingRight) player.move(getPlayerSpeed(), 0);
    });
}