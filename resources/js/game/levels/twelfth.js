import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer } from '../entities/player.js';
import { setupPlayerControls, resetControlState } from '../utils/controls.js';

const missionState = {
    profilesCollected: 0,
    requiredProfiles: 3,
};
const collectedIds = new Set();

export default function setupLevelTwelfth(k) {
    resetControlState();
    missionState.profilesCollected = 0;
    collectedIds.clear();

    loadCommonAssets(k);
    loadLevelAssets(k); // Usaremos 'clipboard' como el perfil del cliente
    k.setGravity(1800);

    k.scene("level12", () => {
        
        // --- MAPA: Un mercado multinivel ---
        k.addLevel([
            "                    ",
            "                    ",
            "     ===    ===     ", // Puestos altos
            "                    ",
            "                    ",
            " ===            === ", // Puestos medios
            "                    ",
            "                    ",
            "S                  E",
            "====================", 
        ], {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [ k.rect(40, 40), k.color(120, 100, 80), k.area(), k.body({ isStatic: true }), k.outline(2, k.rgb(60, 50, 40)), "platform" ],
                "E": () => [ k.rect(40, 80), k.color(0, 255, 0, 0.3), k.area(), k.body({ isStatic: true }), "exit" ],
                "S": () => [ k.rect(40, 40), k.color(0,0,0,0), "start" ],
            }
        });

        const player = createPlayer(k, k.vec2(60, 300), "level12");
        setupPlayerControls(k, player);

        // --- GENERADOR DE NPCs ---
        
        // Función para crear un cliente que camina
        function spawnCustomer(x, y, isTarget) {
            const customer = k.add([
                // Si es target, usamos el sprite del portapapeles (perfil), si no, un cuadrado genérico
                isTarget ? k.sprite("clipboard") : k.rect(30, 30), 
                k.pos(x, y),
                k.area(),
                k.body(),
                k.anchor("center"),
                isTarget ? k.scale(0.8) : k.color(150, 150, 150), // Target vs Ruido
                isTarget ? "target" : "noise",
                {
                    speed: k.rand(40, 80),
                    dir: k.choose([1, -1]),
                    timer: 0,
                    id: k.randi(1, 10000) // ID único para targets
                }
            ]);

            // IA simple: Caminar y dar la vuelta
            customer.onUpdate(() => {
                customer.move(customer.speed * customer.dir, 0);
                
                // Cambiar dirección aleatoriamente o al chocar (simplificado por tiempo)
                customer.timer += k.dt();
                if (customer.timer > k.rand(2, 4)) {
                    customer.dir *= -1;
                    customer.timer = 0;
                }
            });
        }

        // Spawneamos clientes en las plataformas
        spawnCustomer(200, 100, true);  // Target arriba
        spawnCustomer(600, 100, false); // Ruido arriba
        spawnCustomer(150, 250, false); // Ruido medio
        spawnCustomer(650, 250, true);  // Target medio
        spawnCustomer(400, 380, true);  // Target suelo

        // --- Lógica de Interacción ---

        // Al tocar un Target (Cliente Objetivo)
        player.onCollide("target", (c) => {
            if (collectedIds.has(c.id)) return;
            
            collectedIds.add(c.id);
            k.destroy(c);
            missionState.profilesCollected++;
            k.addKaboom(c.pos);
            
            // Feedback visual
            k.add([
                k.text("+1 Perfil", { size: 18 }),
                k.pos(c.pos),
                k.color(0, 255, 0),
                k.move(0, -100),
                k.opacity(1), // <-- CORRECCIÓN AÑADIDA
                k.lifespan(1)
            ]);
        });

        // Al tocar Ruido (Cliente equivocado) - Opcional: Pequeño empujón
        player.onCollide("noise", (n) => {
            player.jump(300); // Pequeño salto/empujón
            k.shake(2);
        });

        player.onCollide("exit", () => {
            if (missionState.profilesCollected >= missionState.requiredProfiles) {
                k.wait(0.5, () => {
                    Livewire.dispatch('kaplay-completed');
                });
            } else {
                k.add([
                    k.text(`Faltan: ${missionState.requiredProfiles - missionState.profilesCollected}`, { size: 20 }),
                    k.pos(player.pos.x, player.pos.y - 60),
                    k.color(255, 100, 100),
                    k.move(0, -50),
                    k.opacity(1), // <-- CORRECCIÓN AÑADIDA
                    k.lifespan(1)
                ]);
            }
        });
    });

    k.go("level12");
}