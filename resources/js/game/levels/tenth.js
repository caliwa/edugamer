import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer } from '../entities/player.js';
import { setupPlayerControls, resetControlState } from '../utils/controls.js';

const missionState = {
    mapsCollected: 0,
    requiredMaps: 3,
};
const collectedIds = new Set();

export default function setupLevelTenth(k) {
    resetControlState();
    missionState.mapsCollected = 0;
    collectedIds.clear();

    loadCommonAssets(k);
    loadLevelAssets(k); // Reutilizamos assets existentes (usaremos 'scroll' como mapa)
    k.setGravity(1700);

    k.scene("level10", () => {
        
        // Mapa estilo "Torre"
        k.addLevel([
            "                   ",
            "      M            ",
            "    =====          ",
            "                   ",
            "           ===     ",
            "   ===           M ",
            "               ====",
            " M                 ",
            "====      ===      ",
            "                   ",
            "      P            ", // P = Jugador (Player Start visual reference only)
            "===================",
        ], {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [ 
                    k.rect(40, 40), 
                    k.color(80, 70, 90), // Color más oscuro/serio para nivel 10
                    k.area(), 
                    k.body({ isStatic: true }), 
                    k.outline(2, k.rgb(50, 40, 60)), 
                    "platform" 
                ],
                // Usamos el sprite 'scroll' pero le cambiamos el color con k.color() para que parezca un plano azul
                "M": () => [ 
                    k.sprite("scroll"), 
                    k.color(100, 200, 255), // Tinte azulado (Blueprint)
                    k.area(), 
                    k.scale(1), 
                    k.pos(0, -10), // Flotando un poco
                    { mapId: k.randi(1, 10000) }, 
                    "collectible" 
                ],
            }
        });

        // Posición inicial
        const player = createPlayer(k, k.vec2(100, 380), "level10");
        setupPlayerControls(k, player);

        // Texto de instrucción en el fondo
        k.add([
            k.text("Recolecta los Planos", { size: 24 }),
            k.pos(k.width() / 2, 100),
            k.anchor("center"),
            k.color(200, 200, 200),
            k.fixed(),
            k.z(-10) // Detrás de todo
        ]);

        // Lógica de recolección
        player.onCollide("collectible", (mapItem) => { 
            if (collectedIds.has(mapItem.mapId)) return; 
            
            collectedIds.add(mapItem.mapId);
            k.destroy(mapItem); 
            missionState.mapsCollected++;
            
            // Efecto de sonido o visual simple
            k.addKaboom(mapItem.pos);

            if (missionState.mapsCollected >= missionState.requiredMaps) {
                k.wait(0.6, () => {
                    Livewire.dispatch('kaplay-completed');
                });
            }
        });
    });

    k.go("level10");
}