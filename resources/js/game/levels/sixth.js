import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer, getPlayerJumpForce } from '../entities/player.js';
// !! IMPORTA LA NUEVA VARIABLE !!
import { setupPlayerControls, resetControlState, isJumpButtonPressed } from '../utils/controls.js';

// --- Configuración del Jetpack (sin cambios) ---
const JETPACK_FORCE = 500; 
const MAX_FUEL = 2.0;      
let currentFuel = MAX_FUEL;
let fuelBar = null;        

const missionState = {
    dataCollected: 0,
    requiredData: 3,
};
const collectedIds = new Set();

function updateFuelBar(k) {
    if (!fuelBar) {
        k.add([ k.rect(100, 10), k.pos(k.width() - 110, 10), k.color(50, 50, 50), k.fixed(), k.z(100) ]);
        fuelBar = k.add([ k.rect(100, 10), k.pos(k.width() - 110, 10), k.color(100, 200, 100), k.fixed(), k.z(101) ]);
    }
    fuelBar.width = Math.max(0, (currentFuel / MAX_FUEL) * 100);
}

export default function setupLevelSixth(k) {
    resetControlState(); // Esto ahora resetea isJumpButtonPressed
    missionState.dataCollected = 0;
    collectedIds.clear();
    currentFuel = MAX_FUEL;
    fuelBar = null; 

    loadCommonAssets(k);
    loadLevelAssets(k);
    k.setGravity(1600); 

    k.scene("level6", () => {
        
        // --- MAPA CORREGIDO ---
        k.addLevel([
            " E ", // Fila 0
            "===", // Fila 1 (Top Y = 40)
            "   ", // Fila 2
            "  D", // Fila 3
            " = ", // Fila 4 (Top Y = 160)
            "   ", // Fila 5
            "D  ", // Fila 6
            " = ", // Fila 7 (Top Y = 280)
            "   ", // Fila 8
            "  D", // Fila 9
            " = ", // Fila 10 (Top Y = 400)
            "===", // Fila 11 (Top Y = 440) <-- Plataforma inicial
        ], {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [ k.rect(40, 40), k.color(100, 80, 60), k.area(), k.body({ isStatic: true }), k.outline(2, k.rgb(61, 43, 31)), "platform" ],
                "D": () => [ k.sprite("data"), k.area(), k.scale(1), { dataId: k.randi(1, 10000) }, "collectible" ],
                "E": () => [ k.rect(40, 40), k.color(0, 255, 0), k.area(), k.body({ isStatic: true }), "exit" ],
            }
        });

        const player = createPlayer(k, k.vec2(k.center().x, 390), "level6"); 
        setupPlayerControls(k, player); // Controla izquierda/derecha y el salto inicial del teclado

        // --- Lógica del Jetpack (MODIFICADA) ---

        // !! YA NO NECESITAMOS onKeyDown para el Jetpack !!
        // k.onKeyDown(["space", "up", "w"], () => { ... }); // <-- ELIMINA O COMENTA ESTO

        // La lógica de recarga y activación del jetpack AHORA va en onUpdate
        player.onUpdate(() => {
            // Recargar combustible al tocar el suelo
            if (player.isGrounded()) {
                 currentFuel = Math.min(MAX_FUEL, currentFuel + k.dt() * 2); 
            }
            
            // !! NUEVA LÓGICA DE JETPACK !!
            // Si el botón de salto (o la tecla de teclado) está presionado, hay fuel y no está en el suelo...
            if ((isJumpButtonPressed || k.isKeyDown("space") || k.isKeyDown("up") || k.isKeyDown("w")) && currentFuel > 0 && !player.isGrounded()) {
                player.jump(JETPACK_FORCE); // Aplica fuerza de jetpack
                currentFuel -= k.dt();      // Consume fuel
            }
            
            updateFuelBar(k); // Actualiza la barra visual
        });

        // Lógica de recolección (sin cambios)
        player.onCollide("collectible", (data) => { 
            if (collectedIds.has(data.dataId)) return; 
            collectedIds.add(data.dataId);
            k.destroy(data); 
            missionState.dataCollected++;
        });
        
        // Lógica de Salida (sin cambios)
        player.onCollide("exit", () => { 
            if (missionState.dataCollected >= missionState.requiredData) {
                k.wait(0.5, () => {
                    Livewire.dispatch('kaplay-completed');
                });
            }
        });
    });

    k.go("level6");
}