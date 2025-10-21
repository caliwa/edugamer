import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer } from '../entities/player.js';
import { setupPlayerControls, resetControlState } from '../utils/controls.js';
import { showDialog, closeDialog, resetDialogState } from '../utils/dialog.js';

// Estado de la misión para el Nivel 2
const missionState = {
    keysCollected: 0,
    requiredKeys: 3,
};

export default function setupLevelSecond(k) {
    // Reseteamos los estados para asegurar una carga limpia
    resetControlState();
    resetDialogState();
    missionState.keysCollected = 0;

    loadCommonAssets(k);
    loadLevelAssets(k);
    k.setGravity(1800);

    k.scene("level2", () => {
        // Un mapa con más plataformas verticales
        k.addLevel([
            "                             ",
            "  K                          ",
            "  ===                        ",
            "        =====                ",
            "                             ",
            "                  K          ",
            "                =====        ",
            "                             ",
            "    =====         K          ",
            "============================",
        ], {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [ k.rect(40, 40), k.color(100, 80, 60), k.area(), k.body({ isStatic: true }), k.outline(2, k.rgb(61, 43, 31)) ],
                "K": () => [ k.sprite("key"), k.area(), k.scale(0.8), "collectible" ],
            }
        });

        const player = createPlayer(k, k.vec2(80, 312));
        
        setupPlayerControls(k, player);

        player.onCollide("collectible", (key) => { 
            k.destroy(key); 
            missionState.keysCollected++;

            // CUANDO SE CUMPLE LA CONDICIÓN...
            if (missionState.keysCollected >= missionState.requiredKeys) {
                k.wait(0.5, () => {
                    // ... DISPARAS EL EVENTO 'kaplay-completed'
                    Livewire.dispatch('kaplay-completed');
                    //
                });
            }
        });
    });

    k.go("level2");
}