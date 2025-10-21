import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer } from '../entities/player.js';
import { setupPlayerControls, resetControlState } from '../utils/controls.js';
import { showDialog, closeDialog, resetDialogState } from '../utils/dialog.js';

// Estado de la misión para el Nivel 4
const missionState = {
    lightbulbsCollected: 0,
    requiredLightbulbs: 4, // Necesitamos 4 ideas
};

export default function setupLevelFourth(k) {
    // Reseteamos los estados
    resetControlState();
    resetDialogState();
    missionState.lightbulbsCollected = 0;

    loadCommonAssets(k);
    loadLevelAssets(k); // Asegúrate de añadir el sprite 'lightbulb' en assets.js
    k.setGravity(1800);

    k.scene("level4", () => {
        // Un mapa más vertical, requiere más saltos
        k.addLevel([
            "    B     ",
            "   ===    ",
            "          ",
            "       B  ",
            "      ==  ",
            "          ",
            "    B     ",
            "   ===    ",
            "          ",
            " B        ",
            "==========",
        ], {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [ k.rect(40, 40), k.color(100, 80, 60), k.area(), k.body({ isStatic: true }), k.outline(2, k.rgb(61, 43, 31)) ],
                // B de "Bombilla" (Lightbulb)
                "B": () => [ k.sprite("lightbulb"), k.area(), k.scale(1), "collectible" ], 
            }
        });

        const player = createPlayer(k, k.vec2(80, 312), "level4");
        
        setupPlayerControls(k, player);

        player.onCollide("collectible", (lightbulb) => { 
            k.destroy(lightbulb); 
            missionState.lightbulbsCollected++;

            // Avisa a Livewire cuando se completó la recolección
            if (missionState.lightbulbsCollected >= missionState.requiredLightbulbs) {
                k.wait(0.5, () => {
                    Livewire.dispatch('kaplay-completed');
                });
            }
        });
    });

    // Inicia la escena del nivel 4
    k.go("level4");
}