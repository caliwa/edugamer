import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer } from '../entities/player.js';
import { setupPlayerControls, resetControlState } from '../utils/controls.js';
import { showDialog, closeDialog, resetDialogState } from '../utils/dialog.js';

// Estado de la misión para el Nivel 3
const missionState = {
    scrollsCollected: 0,
    requiredScrolls: 3,
};

export default function setupLevelThird(k) {
    // Reseteamos los estados para asegurar una carga limpia
    resetControlState();
    resetDialogState();
    missionState.scrollsCollected = 0;

    loadCommonAssets(k);
    loadLevelAssets(k); // Asegúrate de añadir el sprite 'scroll' en assets.js
    k.setGravity(1800);

    k.scene("level3", () => {
        // Un mapa con más plataformas y saltos
        k.addLevel([
            " S                           ",
            "==                           ",
            "                             ",
            "       =====                 ",
            "                             ",
            "                  S          ",
            "                =====        ",
            "                             ",
            "    =====         S          ",
            "============================",
        ], {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [ k.rect(40, 40), k.color(100, 80, 60), k.area(), k.body({ isStatic: true }), k.outline(2, k.rgb(61, 43, 31)) ],
                "S": () => [ k.sprite("scroll"), k.area(), k.scale(1), "collectible" ],
            }
        });

        const player = createPlayer(k, k.vec2(80, 312));
        
        setupPlayerControls(k, player);

        player.onCollide("collectible", (scroll) => { 
            k.destroy(scroll); 
            missionState.scrollsCollected++;

            // Avisa a Livewire cuando se completó la recolección
            if (missionState.scrollsCollected >= missionState.requiredScrolls) {
                k.wait(0.5, () => {
                    Livewire.dispatch('kaplay-completed');
                });
            }
        });
    });

    // Inicia la escena del nivel 3
    k.go("level3");
}