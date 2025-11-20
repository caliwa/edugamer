import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer } from '../entities/player.js';
import { setupPlayerControls, resetControlState } from '../utils/controls.js';

const missionState = {
    coinsCollected: 0,
    totalCoins: 0,
    isGameOver: false
};

export default function setupLevelFifteenth(k) {
    resetControlState();
    missionState.coinsCollected = 0;
    missionState.totalCoins = 0;
    missionState.isGameOver = false;

    loadCommonAssets(k);
    k.setGravity(1600);

    k.scene("level15", () => {
        
        // --- MAPA ---
        // Hay exactamente 6 signos de '$' aquí.
        const mapLayout = [
            "                                    ",
            "                                    ",
            "       $        $                   ", 
            "      ===      ===         $        ",
            "                        ! ===       ",
            "   $       !          ===      $    ",
            "  ===     ===    ===          ===   ",
            "       !                            ",
            "      ===             !      !      ",
            "S               $    ===    ===     ",
            "====================================",
        ];

        // Contamos las monedas manualmente para asegurar que el UI (0/6) sea real
        let totalCoinsInMap = 0;
        for (const row of mapLayout) {
            for (const char of row) {
                if (char === '$') totalCoinsInMap++;
            }
        }
        missionState.totalCoins = totalCoinsInMap;

        // Crear nivel
        k.addLevel(mapLayout, {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [ 
                    k.rect(40, 40), 
                    k.color(80, 80, 90), 
                    k.area(), 
                    k.body({ isStatic: true }), 
                    k.outline(2, k.rgb(50, 50, 60)), 
                    "platform" 
                ],
                "S": () => [ k.rect(40, 40), k.color(0,0,0,0), "start" ],
                "$": () => [ 
                    k.circle(15), 
                    k.color(255, 215, 0), 
                    k.area(), 
                    k.anchor("center"), 
                    "income", 
                    { val: 1 } 
                ],
                "!": () => [ 
                    k.rect(30, 30), 
                    k.color(255, 50, 50), 
                    k.area(), 
                    k.anchor("center"), 
                    k.body({ isStatic: true }), 
                    "expense" 
                ],
            }
        });

        // Paredes invisibles (límite del mapa)
        k.add([k.rect(10, 480), k.pos(-10, 0), k.area(), k.body({ isStatic: true })]);
        k.add([k.rect(10, 480), k.pos(1440, 0), k.area(), k.body({ isStatic: true })]);

        // Jugador
        const player = createPlayer(k, k.vec2(60, 300), "level15");
        setupPlayerControls(k, player);

        // --- CÁMARA (NUEVO) ---
        // Esto hace que veas el resto del mapa y encuentres las monedas que faltan
        player.onUpdate(() => {
            // La cámara sigue al jugador en X, pero mantenemos Y fijo (o suave)
            // Clamp para no ver fuera del mapa a la izquierda
            const camX = Math.max(400, player.pos.x); 
            // Clamp para no ver fuera a la derecha (asumiendo ancho mapa ~1440)
            const finalCamX = Math.min(camX, 1040); 
            
            k.camPos(finalCamX, 240); // 240 es la mitad de la altura (480/2)
        });

        // --- UI (Fija en pantalla) ---
        const scoreLabel = k.add([
            k.text(`Ingresos: 0 / ${missionState.totalCoins}`, { size: 24 }),
            k.pos(20, 20),
            k.color(255, 255, 255),
            k.fixed() // Importante: se mantiene fijo aunque la cámara se mueva
        ]);

        k.add([
            k.text("¡Evita los gastos rojos!", { size: 18 }),
            k.pos(20, 50),
            k.color(255, 100, 100),
            k.fixed()
        ]);

        // --- COLISIONES ---

        // 1. Recolectar Monedas
        player.onCollide("income", (coin) => {
            k.destroy(coin);
            missionState.coinsCollected++;
            scoreLabel.text = `Ingresos: ${missionState.coinsCollected} / ${missionState.totalCoins}`;
            
            k.addKaboom(coin.pos);
            
            // Feedback visual
            k.add([
                k.text("+$", { size: 20 }),
                k.pos(coin.pos),
                k.color(0, 255, 0),
                k.move(0, -100),
                k.opacity(1),
                k.lifespan(0.5)
            ]);

            // --- VICTORIA INMEDIATA (NUEVO) ---
            // Si recolectó la última moneda, gana al instante
            if (missionState.coinsCollected >= missionState.totalCoins) {
                gameWin();
            }
        });

        // 2. Tocar Gasto
        player.onCollide("expense", (ex) => {
            gameOver("¡Gastos Excesivos!");
        });

        function gameWin() {
            if (missionState.isGameOver) return;
            missionState.isGameOver = true;

            k.add([
                k.text("¡RENTABILIDAD LOGRADA!", { size: 30 }),
                k.pos(k.center()), // Centro de la pantalla (relativo a cámara si no es fixed, pero usamos fixed para UI)
                k.anchor("center"),
                k.color(0, 255, 0),
                k.fixed()
            ]);
            
            k.wait(1.0, () => {
                Livewire.dispatch('kaplay-completed');
            });
        }

        function gameOver(reason) {
            if (missionState.isGameOver) return;
            missionState.isGameOver = true;
            
            k.shake(10);
            k.destroy(player);

            k.add([
                k.text(reason, { size: 40 }),
                k.pos(k.width() / 2, k.height() / 2 - 50),
                k.anchor("center"),
                k.color(255, 0, 0),
                k.fixed()
            ]);
            
            k.add([
                k.text("QUIEBRA", { size: 60 }),
                k.pos(k.width() / 2, k.height() / 2 + 20),
                k.anchor("center"),
                k.color(255, 0, 0),
                k.fixed()
            ]);

            k.wait(2, () => {
                k.go("level15");
            });
        }
    });

    k.go("level15");
}