import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer } from '../entities/player.js';
import { setupPlayerControls, resetControlState } from '../utils/controls.js';

const missionState = {
    cardsCollected: 0,
    requiredCards: 4,
};
const collectedIds = new Set();

export default function setupLevelFourteenth(k) {
    resetControlState();
    missionState.cardsCollected = 0;
    collectedIds.clear();

    loadCommonAssets(k);
    loadLevelAssets(k); 
    k.setGravity(1800);

    k.scene("level14", () => {
        
        // --- Mapa: Centro de Negocios ---
        k.addLevel([
            "                    ",
            " C       ===        ", // Cards arriba
            "===                 ",
            "                  C ",
            "      C           ==", // Card medio
            "    ======          ",
            "             ===    ",
            "                    ",
            " S       C        E ", // Inicio, Card, Salida
            "====================",
        ], {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [ k.rect(40, 40), k.color(60, 70, 90), k.area(), k.body({ isStatic: true }), k.outline(2, k.rgb(40, 50, 70)), "platform" ],
                // Usamos 'scroll' teñido de blanco/gris para simular tarjeta de presentación
                "C": () => [ k.sprite("scroll"), k.color(240, 240, 240), k.area(), k.scale(0.8), { cId: k.randi(1,1000) }, "collectible" ], 
                "E": () => [ k.rect(40, 80), k.color(0, 255, 0, 0.3), k.area(), k.body({ isStatic: true }), "exit" ],
                "S": () => [ k.rect(40, 40), k.color(0,0,0,0), "start" ],
            }
        });

        const player = createPlayer(k, k.vec2(60, 300), "level14");
        setupPlayerControls(k, player);

        // Texto decorativo
        k.add([
            k.text("Networking: Recolecta 4 Contactos", { size: 20 }),
            k.pos(20, 20),
            k.fixed(),
            k.color(200, 200, 200)
        ]);

        // --- Lógica ---

        player.onCollide("collectible", (c) => {
            if (collectedIds.has(c.cId)) return;
            
            collectedIds.add(c.cId);
            k.destroy(c);
            missionState.cardsCollected++;
            
            k.addKaboom(c.pos);
            
            // Feedback flotante CORREGIDO
            k.add([
                k.text("+1 Contacto", { size: 16 }),
                k.pos(c.pos),
                k.color(100, 255, 100),
                k.move(0, -100),
                k.opacity(1), // Importante para lifespan
                k.lifespan(0.8)
            ]);
        });

        player.onCollide("exit", () => {
            if (missionState.cardsCollected >= missionState.requiredCards) {
                k.wait(0.5, () => {
                    Livewire.dispatch('kaplay-completed');
                });
            } else {
                k.add([
                    k.text(`Faltan: ${missionState.requiredCards - missionState.cardsCollected}`, { size: 20 }),
                    k.pos(player.pos.x, player.pos.y - 60),
                    k.color(255, 100, 100),
                    k.move(0, -50),
                    k.opacity(1), // Importante para lifespan
                    k.lifespan(1)
                ]);
            }
        });
    });

    k.go("level14");
}