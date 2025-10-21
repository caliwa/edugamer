import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer } from '../entities/player.js';
import { setupPlayerControls, resetControlState } from '../utils/controls.js';
// Ya no necesitamos 'dialog.js' para este nivel
// import { showDialog, closeDialog, resetDialogState } from '../utils/dialog.js';

const missionState = {
    reportsCollected: 0,
    requiredReports: 3,
};

// Guardamos los IDs de los reportes ya recolectados para evitar el bug de doble conteo
const collectedIds = new Set();

export default function setupLevelFifth(k) {
    resetControlState();
    // resetDialogState(); // Ya no se usa
    missionState.reportsCollected = 0;
    collectedIds.clear(); // Limpia el Set para un nuevo juego

    loadCommonAssets(k);
    loadLevelAssets(k);
    k.setGravity(1800);

    k.scene("level5", () => {
        
        // --- Plataforma Estática (Hub) ---
        k.addLevel([
            "                    ",
            "                    ",
            "                    ",
            "                    ",
            "                    ",
            "                    ",
            "                    ",
            "                    ",
            "       ======       ", // Hub estático para saltar
            "====================", // Piso
        ], {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [ k.rect(40, 40), k.color(100, 80, 60), k.area(), k.body({ isStatic: true }), k.outline(2, k.rgb(61, 43, 31)), "platform" ],
            }
        });

        // Coloca al jugador en el Hub estático
        const player = createPlayer(k, k.vec2(k.center().x, k.height() - 180), "level5");
        setupPlayerControls(k, player);

        // --- El Carrusel Giratorio ---

        // 1. El Pivote: Un objeto invisible en el centro que ROTA
        const pivot = k.add([
            k.pos(k.center().x, k.center().y - 50), // Un poco arriba del centro
            k.rotate(0), // Ángulo inicial
        ]);

        // 2. Añadir plataformas COMO HIJOS del pivote.
        // Al ser hijos, rotarán automáticamente con el pivote.
        
        // Plataforma 1 (Arriba del pivote)
        const p1 = pivot.add([
            k.rect(120, 20),
            k.pos(0, -120), // 120px arriba del pivote
            k.color(150, 100, 80),
            k.area(),
            k.body({ isStatic: true }),
            k.outline(2, k.rgb(61, 43, 31)),
            "platform",
        ]);
        // Añadir el coleccionable a la plataforma 1
        p1.add([
            k.sprite("clipboard"),
            k.pos(0, -25), // 25px arriba de su plataforma
            k.area(),
            k.scale(0.8),
            { reportId: 1 }, // ID único
            "collectible",
        ]);

        // Plataforma 2 (Abajo-Derecha del pivote)
        const p2 = pivot.add([
            k.rect(120, 20),
            k.pos(100, 50), // 100px a la derecha, 50px abajo
            k.color(150, 100, 80),
            k.area(),
            k.body({ isStatic: true }),
            k.outline(2, k.rgb(61, 43, 31)),
            "platform",
        ]);
        // Añadir el coleccionable a la plataforma 2
        p2.add([
            k.sprite("clipboard"),
            k.pos(0, -25),
            k.area(),
            k.scale(0.8),
            { reportId: 2 },
            "collectible",
        ]);

        // Plataforma 3 (Abajo-Izquierda del pivote)
        const p3 = pivot.add([
            k.rect(120, 20),
            k.pos(-100, 50), // 100px a la izquierda, 50px abajo
            k.color(150, 100, 80),
            k.area(),
            k.body({ isStatic: true }),
            k.outline(2, k.rgb(61, 43, 31)),
            "platform",
        ]);
        // Añadir el coleccionable a la plataforma 3
        p3.add([
            k.sprite("clipboard"),
            k.pos(0, -25),
            k.area(),
            k.scale(0.8),
            { reportId: 3 },
            "collectible",
        ]);

        // 3. La Rotación
        pivot.onUpdate(() => {
            // Rota a 20 grados por segundo. ¡Ajusta este valor para más/menos dificultad!
            pivot.angle += 20 * k.dt(); 
        });

        // Lógica de recolección
        player.onCollide("collectible", (report) => { 
            // Previene el bug de doble conteo si el jugador
            // se queda parado sobre el item por varios frames
            if (collectedIds.has(report.reportId)) {
                return; 
            }
            
            collectedIds.add(report.reportId);
            k.destroy(report); 
            missionState.reportsCollected++;

            if (missionState.reportsCollected >= missionState.requiredReports) {
                k.wait(0.5, () => {
                    Livewire.dispatch('kaplay-completed');
                });
            }
        });
    });

    k.go("level5");
}