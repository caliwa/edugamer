import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer } from '../entities/player.js';
import { setupPlayerControls, resetControlState } from '../utils/controls.js';

// Simplified mission state: Only tracking data collection
const missionState = {
    dataCollected: 0,
    requiredData: 5,
    levelComplete: false,
};
const collectedDataIds = new Set();
// Removed station-related variables

// Simplified completion check: Only checks data
function checkCompletion(k) {
    if (missionState.levelComplete) return;
    const dataOk = missionState.dataCollected >= missionState.requiredData;

    if (dataOk) {
        console.log("SUCCESS: All data collected. Level Complete. Dispatching event..."); // Log añadido
        missionState.levelComplete = true;
        k.wait(0.5, () => {
            // !! ESTA LÍNEA ES CRUCIAL !!
            Livewire.dispatch('kaplay-completed');
            console.log("Livewire event 'kaplay-completed' dispatched."); // Log añadido
        });
    }
}

// Removed createStationComponents function

export default function setupLevelNinth(k) {
    resetControlState();
    missionState.dataCollected = 0;
    missionState.levelComplete = false; // Reset completion flag
    collectedDataIds.clear();
    // Removed station resets

    loadCommonAssets(k);
    loadLevelAssets(k); // Still load assets (might remove unused ones later)
    k.setGravity(1800);

    k.scene("level9", () => {

        // --- MAPA SIN ESTACIONES ---
        // Removed '1', '2', '3' from the map layout
        k.addLevel([
            "                ", // Analysis Hub (Stations 1, 2, 3)
            "=================", // Platform for stations
            "                X", // Space
            "                 ", // Noise Section 1
            "  D ===========D ", // Platform with Data
            "=  ==        X===", // Noise Section 2
            "                 ", // Space
            "X D   =====  D   ", // Platform with Data
            "======= X ====  =", // Transition Platform
            "                 ", // Space
            "        D        ", // Data Collection Area
            "=================", // Start Platform (Row 9, Top Y=360)
        ], {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [ k.rect(40, 40), k.color(100, 80, 60), k.area(), k.body({ isStatic: true }), k.outline(2, k.rgb(61, 43, 31)), "platform" ],
                "D": () => [
                    k.sprite("data_point"), k.area(), k.scale(0.8),
                    { dataId: k.randi(1, 10000) || Date.now() },
                    "collectible"
                ],
                "X": () => [ k.sprite("noise"), k.area(), k.body({ isStatic: true }), "hazard" ],
                // Removed definitions for "1", "2", "3"
            }
        });

        // Player setup (position might need slight adjustment if map changes significantly)
        const player = createPlayer(k, k.vec2(80, k.height() - 80), "level9");
        setupPlayerControls(k, player);

        // --- Lógica del Juego Simplificada ---

        // Recolectar Puntos de Datos (Calls checkCompletion)
        player.onCollide("collectible", (data) => {
            if (!data || typeof data.dataId === 'undefined') return;
            if (data.isDestroyed && data.isDestroyed()) return;
            if (collectedDataIds.has(data.dataId)) return;

            console.log("Collectible seems valid. Processing dataId:", data.dataId);
            collectedDataIds.add(data.dataId);
            k.destroy(data);
            missionState.dataCollected++;
            console.log("Data collected! Total:", missionState.dataCollected);

            // !! LLAMA A checkCompletion DIRECTAMENTE !!
            checkCompletion(k);

            // Removed the part that enabled stations
        });

        // !! REMOVED onCollide("station", ...) !!

         // Colisión con Ruido (Hazard) - Stays the same
        player.onCollide("hazard", (noise) => {
             if (!noise || (noise.isDestroyed && noise.isDestroyed())) return;

            if (!missionState.levelComplete) { // Only reset if level isn't already won
                console.log("FAIL: Hit noise. Restarting level.");
                k.go("level9"); // Reinicia
            }
        });

    });

    k.go("level9");
}