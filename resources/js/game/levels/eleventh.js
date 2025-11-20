import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer } from '../entities/player.js';
import { setupPlayerControls, resetControlState } from '../utils/controls.js';

const missionState = {
    blueprintsCollected: 0,
    requiredBlueprints: 3,
};
const collectedIds = new Set();

export default function setupLevelEleventh(k) {
    resetControlState();
    missionState.blueprintsCollected = 0;
    collectedIds.clear();

    loadCommonAssets(k);
    loadLevelAssets(k); 
    k.setGravity(1800);

    k.scene("level11", () => {
        
        // --- Mapa Base ---
        k.addLevel([
            "                    ",
            "                    ", 
            "    B               ", // Blueprint 1 (arriba izquierda)
            "                    ",
            "                    ",
            "               B    ", // Blueprint 2 (medio derecha)
            "                    ",
            "          B         ", // Blueprint 3 (abajo centro)
            "                    ",
            "S                  E", 
            "====================", 
        ], {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [ k.rect(40, 40), k.color(70, 80, 100), k.area(), k.body({ isStatic: true }), k.outline(2, k.rgb(40, 50, 70)), "platform" ],
                // Usamos 'scroll' pero azul para parecer un Blueprint
                "B": () => [ k.sprite("scroll"), k.color(100, 200, 255), k.area(), k.scale(1), { bId: k.randi(1,1000) }, "collectible" ], 
                "E": () => [ k.rect(40, 80), k.color(0, 255, 0, 0.3), k.area(), k.body({ isStatic: true }), "exit" ], 
                "S": () => [ k.rect(40, 40), k.color(0,0,0,0), "start" ],
            }
        });

        const player = createPlayer(k, k.vec2(60, 300), "level11");
        setupPlayerControls(k, player);

        // --- ELEVADORES DINÁMICOS ---
        
        function createElevator(x, y, range, speed, isHorizontal = false) {
            const platform = k.add([
                k.rect(80, 20),
                k.pos(x, y),
                k.color(255, 200, 50), // Amarillo construcción
                k.area(),
                k.body({ isStatic: true }),
                k.outline(2, k.rgb(150, 100, 0)),
                "elevator",
                {
                    startPos: isHorizontal ? x : y,
                    dir: 1,
                    range: range,
                    speed: speed,
                    isHorizontal: isHorizontal
                }
            ]);

            // Decoración tipo "precaución" en la plataforma
            platform.add([
                k.rect(80, 5),
                k.pos(0, 15),
                k.color(0, 0, 0),
            ]);

            platform.onUpdate(() => {
                if (platform.isHorizontal) {
                    if (platform.pos.x > platform.startPos + platform.range) platform.dir = -1;
                    else if (platform.pos.x < platform.startPos) platform.dir = 1;
                    platform.move(platform.speed * platform.dir, 0);
                } else {
                    if (platform.pos.y > platform.startPos + platform.range) platform.dir = -1;
                    else if (platform.pos.y < platform.startPos - platform.range) platform.dir = 1;
                    platform.move(0, platform.speed * platform.dir);
                }
            });
        }

        // 1. Elevador Vertical Izquierdo
        createElevator(150, 350, 150, 80, false);

        // 2. Plataforma Horizontal Central (Cinta transportadora)
        createElevator(300, 200, 150, 60, true);

        // 3. Elevador Vertical Derecho
        createElevator(650, 350, 100, 100, false);


        // --- Lógica ---
        player.onCollide("collectible", (b) => {
            if (collectedIds.has(b.bId)) return;
            collectedIds.add(b.bId);
            k.destroy(b);
            missionState.blueprintsCollected++;
            k.addKaboom(b.pos);
        });

        player.onCollide("exit", () => {
            if (missionState.blueprintsCollected >= missionState.requiredBlueprints) {
                k.wait(0.5, () => {
                    Livewire.dispatch('kaplay-completed');
                });
            } else {
                k.add([
                    k.text("Faltan Blueprints", { size: 18 }),
                    k.pos(player.pos.x, player.pos.y - 50),
                    k.lifespan(1),
                    k.color(255, 100, 100),
                    k.move(0, -50)
                ]);
            }
        });
    });

    k.go("level11");
}