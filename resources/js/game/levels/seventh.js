import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer } from '../entities/player.js';
import { setupPlayerControls, resetControlState } from '../utils/controls.js';

const missionState = {
    fragmentsCollected: 0,
    requiredFragments: 3,
    nodesActivated: 0,       // CONTADOR de nodos activados
    requiredNodes: 3,        // Necesitamos activar 3
    levelComplete: false,    // Bandera para evitar disparar el evento múltiples veces
};
const collectedIds = new Set(); // Para fragmentos
const activatedNodeIds = new Set(); // Para nodos

// Función para comprobar si se completó el nivel
function checkCompletion(k) {
    // Si ya se completó, no hacer nada más
    if (missionState.levelComplete) return;

    // Comprueba ambas condiciones
    const fragmentsOk = missionState.fragmentsCollected >= missionState.requiredFragments;
    const nodesOk = missionState.nodesActivated >= missionState.requiredNodes;

    if (fragmentsOk && nodesOk) {
        console.log("SUCCESS: All fragments collected and nodes activated. Dispatching event...");
        missionState.levelComplete = true; // Marca como completado
        k.wait(0.5, () => {
            Livewire.dispatch('kaplay-completed');
        });
    }
}

// Función para crear Nodos (sin cambios)
function createNodeComponents(k, id) {
     return [
        k.rect(30, 30), k.pos(5, 5), k.color(150, 150, 150),
        k.area(), k.body({ isStatic: true }), "node",
        { nodeId: id, activated: false }
    ];
}


export default function setupLevelSeventh(k) {
    resetControlState();
    missionState.fragmentsCollected = 0;
    missionState.nodesActivated = 0;   // Resetea contador de nodos
    missionState.levelComplete = false; // Resetea bandera
    collectedIds.clear();
    activatedNodeIds.clear(); // Resetea Set de nodos

    loadCommonAssets(k);
    loadLevelAssets(k);
    k.setGravity(1800);

    k.scene("level7", () => {

        // --- MAPA SIN SALIDA ('E') ---
        k.addLevel([
            "              ", // Fila 0
            "           3  ", // Fila 1 (Solo el nodo 3)
            "===       =====", // Fila 2
            "              ", // Fila 3
            "   F   2      ", // Fila 4
            " =====   ===  ", // Fila 5
            "              ", // Fila 6
            " 1     F      ", // Fila 7
            "==  =======   ", // Fila 8
            "              ", // Fila 9
            "     F        ", // Fila 10
            "==============", // Fila 11 (Top Y = 440)
        ], {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [ k.rect(40, 40), k.color(100, 80, 60), k.area(), k.body({ isStatic: true }), k.outline(2, k.rgb(61, 43, 31)), "platform" ],
                "F": () => [
                    k.sprite("fragment"), k.area(), k.scale(1),
                    { fragId: k.randi(1, 10000) }, "collectible"
                ],
                // Llaman a la función que devuelve el array de componentes
                "1": () => createNodeComponents(k, 1),
                "2": () => createNodeComponents(k, 2),
                "3": () => createNodeComponents(k, 3),
                // !! NO HAY 'E' !!
            }
        });

        const player = createPlayer(k, k.vec2(80, 390), "level7");
        setupPlayerControls(k, player);

        // --- LÓGICA DE RECOLECCIÓN ---
        player.onCollide("collectible", (frag) => {
            if (frag.fragId && !collectedIds.has(frag.fragId)) {
                collectedIds.add(frag.fragId);
                k.destroy(frag);
                missionState.fragmentsCollected++;
                console.log("Fragment collected! Total:", missionState.fragmentsCollected);
                checkCompletion(k); // Comprueba si se completó el nivel
            }
        });

        // --- LÓGICA DE ACTIVACIÓN DE NODOS (SIMPLIFICADA) ---
        player.onCollide("node", (node) => {
            // Si el nodo existe, no ha sido activado antes, y el nivel no está completo
            if (node.nodeId && !activatedNodeIds.has(node.nodeId) && !missionState.levelComplete) {
                activatedNodeIds.add(node.nodeId); // Añade al Set para no contarlo de nuevo
                node.color = k.rgb(0, 200, 0); // Verde
                missionState.nodesActivated++; // Incrementa el contador
                console.log("Node activated! Total:", missionState.nodesActivated);
                checkCompletion(k); // Comprueba si se completó el nivel
            }
        });

        // !! NO HAY LÓGICA DE SALIDA ('onCollide("exit", ...)') !!

    });

    k.go("level7");
}