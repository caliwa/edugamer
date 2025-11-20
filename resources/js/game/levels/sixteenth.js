import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer } from '../entities/player.js';
import { setupPlayerControls, resetControlState } from '../utils/controls.js';
import { showDialog, closeDialog, resetDialogState } from '../utils/dialog.js';

const missionState = {
    visitsCount: 0,
    requiredVisits: 3,
    canExit: false
};
const visitedIds = new Set();

export default function setupLevelSixteenth(k) {
    resetControlState();
    resetDialogState();
    missionState.visitsCount = 0;
    missionState.canExit = false;
    visitedIds.clear();

    loadCommonAssets(k);
    // Sprite inline para el mentor
    k.loadSprite("mentor", "data:image/svg+xml," + encodeURIComponent(`<svg width="40" height="40" xmlns="http://www.w3.org/2000/svg"><circle cx="20" cy="20" r="18" fill="#4F46E5"/><path d="M12 20 L18 26 L28 14" fill="none" stroke="white" stroke-width="4"/></svg>`)); 
    
    k.setGravity(1600);

    k.scene("level16", () => {
        
        // --- MAPA ---
        k.addLevel([
            "                    ",
            "           E        ", // Salida
            "         =====      ",
            "   M                ", 
            "  ===               ",
            "            M       ", 
            "          =====     ",
            "                    ",
            "     M              ", 
            "   =====            ",
            "S                   ",
            "====================",
        ], {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [ k.rect(40, 40), k.color(80, 90, 100), k.area(), k.body({ isStatic: true }), k.outline(2, k.rgb(50, 60, 70)), "platform" ],
                "S": () => [ k.rect(40, 40), k.color(0,0,0,0), "start" ],
                "E": () => [ k.rect(40, 60), k.color(200, 200, 200), k.area(), k.body({ isStatic: true }), "exit" ], 
                "M": () => [ 
                    k.sprite("mentor"), 
                    k.area(), 
                    k.body({ isStatic: true }), 
                    "mentor",
                    { visited: false, id: k.randi(1, 1000) }
                ],
            }
        });

        const player = createPlayer(k, k.vec2(60, 350), "level16");
        setupPlayerControls(k, player);

        // UI: Contador
        const counterLabel = k.add([
            k.text("Visitas: 0 / 3", { size: 20 }),
            k.pos(20, 20),
            k.fixed(),
            k.color(255, 255, 255)
        ]);

        // --- INTERACCIÓN ---

        player.onCollide("mentor", (mentor) => {
            if (mentor.visited) return;

            mentor.visited = true;
            mentor.color = k.rgb(100, 255, 100); // Mentor se pone verde
            missionState.visitsCount++;
            
            counterLabel.text = `Visitas: ${missionState.visitsCount} / 3`;
            k.addKaboom(mentor.pos);

            showDialog(k, "¡Gracias por la visita! Aquí tienes mi sello de aprobación.");
            
            if (missionState.visitsCount >= missionState.requiredVisits) {
                missionState.canExit = true;
                
                // Cambiamos el color de la salida de forma segura
                k.get("exit").forEach((exitObj) => {
                    exitObj.color = k.rgb(0, 255, 0);
                });
                
                k.add([
                    k.text("¡SALIDA ABIERTA!", { size: 24 }),
                    k.pos(k.width()/2, 100),
                    k.anchor("center"),
                    k.color(0, 255, 0),
                    k.opacity(1), // <--- CORRECCIÓN AÑADIDA AQUÍ
                    k.lifespan(2),
                    k.fixed()
                ]);
            }
        });

        player.onCollide("exit", () => {
            if (missionState.canExit) {
                k.wait(0.5, () => {
                    Livewire.dispatch('kaplay-completed');
                });
            } else {
                showDialog(k, "Debes visitar a todos los mentores antes de graduarte.");
            }
        });

        // Botón de acción cierra diálogos
        const actionBtn = document.getElementById('actionBtn');
        if (actionBtn) {
            actionBtn.onclick = () => closeDialog(k);
        }
    });

    k.go("level16");
}