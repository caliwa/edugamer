import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer } from '../entities/player.js';
import { setupPlayerControls, resetControlState } from '../utils/controls.js';
import { showDialog, closeDialog, resetDialogState } from '../utils/dialog.js';

const missionState = {
    talkedToMentor: false,
    gearsCollected: 0,
    requiredGears: 3,
    machineFixed: false,
};

export default function setupLevelFirst(k) {
    resetControlState();
    resetDialogState();

    loadCommonAssets(k);
    loadLevelAssets(k);
    k.setGravity(1800);

    k.scene("workshop", () => {
        k.addLevel([
            "                                   ",
            "                                   ",
            "   g                               ",
            "  ===      b                       ",
            "           b     =======           ",
            "        g  b                       ",
            "     ======b                       ",
            "           b       g               ",
            " M         b   =========           ",
            "===================================",
        ], {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [ k.rect(40, 40), k.color(100, 80, 60), k.area(), k.body({ isStatic: true }), k.outline(2, k.rgb(61, 43, 31)), "platform" ],
                "g": () => [ k.sprite("gear"), k.area(), k.scale(1.2), "collectible" ],
                "b": () => [ k.rect(40, 40), k.color(60, 50, 40), k.outline(2, k.rgb(41, 33, 21)), "background" ],
                "M": () => [ k.rect(40, 80), k.color(150, 120, 90), k.area(), k.body({ isStatic: true }), k.outline(2, k.rgb(61, 43, 31)), "machine" ],
            }
        });

        const player = createPlayer(k, k.vec2(80, 320), "workshop");
        setupPlayerControls(k, player);

        const mentor = k.add([
            k.sprite("turtle"),
            // CORRECCIÓN: Nueva posición para el mentor, más cerca del jugador.
            k.pos(480, 400),
            k.area(),
            k.body({ isStatic: true }),
            "npc"
        ]);
        
        player.onCollide("collectible", (gear) => { 
            k.destroy(gear); 
            missionState.gearsCollected++;
            Livewire.dispatch('item-collected', { collected: missionState.gearsCollected, total: missionState.requiredGears });

            if (missionState.gearsCollected >= missionState.requiredGears) {
                k.wait(0.5, () => {
                    Livewire.dispatch('kaplay-completed');
                });
            }
        });

        player.onCollide("npc", () => {
            if (missionState.machineFixed) {
                showDialog(k, "¡La máquina funciona! Este es el primer paso en tu gran aventura. ¡Adelante!");
            } else if (missionState.gearsCollected >= missionState.requiredGears) {
                showDialog(k, "¡Excelente! Has encontrado todos los engranajes. Ahora, acércate a la máquina para repararla.");
                missionState.talkedToMentor = true;
            } else if (!missionState.talkedToMentor) {
                showDialog(k, `¡Hola, inventor! Para empezar, repara esa máquina. Necesitas ${missionState.requiredGears} engranajes. Búscalos.`);
                missionState.talkedToMentor = true;
            } else {
                showDialog(k, `Aún te faltan engranajes. Llevas ${missionState.gearsCollected} de ${missionState.requiredGears}.`);
            }
        });

        player.onCollide("machine", () => {
            if (missionState.machineFixed) {
                showDialog(k, "*Clank!* ¡La máquina ha sido reparada! Habla con el mentor.");
            } else {
                showDialog(k, "Parece que faltan piezas para poder repararla...");
            }
        });

        document.getElementById('actionBtn').onclick = () => closeDialog(k);
    });

    k.go("workshop");
}