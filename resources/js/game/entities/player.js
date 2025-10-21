// Constantes del jugador para fácil configuración
const SPEED = 250;
const JUMP_FORCE = 750;

export function createPlayer(k, initialPos, sceneName) {
    const player = k.add([
        k.sprite("player"),
        k.pos(initialPos),
        k.area(),
        k.body(),
        "player"
    ]);

    player.onUpdate(() => {
        if (player.pos.y > k.height()) {
            k.go(sceneName);
        }
    });

    return player;
}

export function getPlayerSpeed() {
    return SPEED;
}

export function getPlayerJumpForce() {
    return JUMP_FORCE;
}