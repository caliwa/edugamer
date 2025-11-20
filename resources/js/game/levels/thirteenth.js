import { loadCommonAssets, loadLevelAssets } from '../assets.js';
import { createPlayer } from '../entities/player.js';
import { setupPlayerControls, resetControlState } from '../utils/controls.js';

const missionState = {
    score: 0,
    requiredScore: 5,
    isGameOver: false
};

export default function setupLevelThirteenth(k) {
    resetControlState();
    missionState.score = 0;
    missionState.isGameOver = false;

    loadCommonAssets(k);
    // No cargamos loadLevelAssets aquí porque dibujaremos los objetos proceduralmente
    k.setGravity(1800);

    k.scene("level13", () => {

        // --- Escenario Simple ---
        // Solo piso y paredes invisibles
        k.addLevel([
            "                    ",
            "                    ",
            "                    ",
            "                    ",
            "                    ",
            "                    ",
            "                    ",
            "                    ",
            "                    ",
            "====================",
        ], {
            tileWidth: 40,
            tileHeight: 40,
            tiles: {
                "=": () => [k.rect(40, 40), k.color(100, 100, 100), k.area(), k.body({ isStatic: true }), "platform"],
            }
        });

        // Paredes invisibles para que no se salga de la pantalla
        k.add([k.rect(10, 480), k.pos(-10, 0), k.area(), k.body({ isStatic: true })]);
        k.add([k.rect(10, 480), k.pos(800, 0), k.area(), k.body({ isStatic: true })]);

        // Jugador
        const player = createPlayer(k, k.vec2(400, 300), "level13");
        setupPlayerControls(k, player);

        // UI de Puntuación
        const scoreLabel = k.add([
            k.text(`Dinero: 0 / ${missionState.requiredScore}`, { size: 24 }),
            k.pos(20, 20),
            k.color(255, 255, 255),
            k.fixed()
        ]);

        // --- Generador de Objetos que Caen ---

        function spawnMoney() {
            const xPos = k.rand(40, 760); // Posición X aleatoria

            const money = k.add([
                k.circle(15), // Moneda amarilla
                k.pos(xPos, -20),
                k.color(255, 215, 0), // Gold
                k.outline(2, k.rgb(200, 150, 0)),
                k.area(),
                k.body({ gravityScale: 0.5 }), // Caen más lento que el jugador
                "money",
                { value: 1 }
            ]);
        }

        function spawnBill() {
            const xPos = k.rand(40, 760);

            const bill = k.add([
                k.rect(40, 20), // Billete verde
                k.pos(xPos, -20),
                k.color(50, 200, 50),
                k.outline(2, k.rgb(30, 100, 30)),
                k.area(),
                k.body({ gravityScale: 0.3 }), // Caen lento flotando
                "money",
                { value: 2 } // Vale más
            ]);
        }

        function spawnTax() {
            const xPos = k.rand(40, 760);

            k.add([
                k.rect(30, 30), // Impuesto rojo (cuadrado)
                k.pos(xPos, -20),
                k.color(200, 50, 50),
                k.outline(2, k.rgb(100, 0, 0)),
                k.area(),
                k.body({ gravityScale: 0.8 }), // Caen rápido
                "tax"
            ]);

            // Texto "TAX" dentro
            k.add([
                k.text("TAX", { size: 10, font: "monospace" }),
                k.pos(xPos + 5, -15), // Aproximado
                k.color(255, 255, 255),
                k.move(0, 500), // Hack para que se mueva (no es hijo, para simplificar)
                k.opacity(1), // <--- CORRECCIÓN AQUÍ
                k.lifespan(2)
            ]);
        }

        // Loop de generación
        k.loop(1.2, () => {
            if (missionState.isGameOver) return;

            const r = k.rand(0, 10);
            if (r < 5) spawnMoney();      // 50% chance moneda
            else if (r < 8) spawnTax();   // 30% chance impuesto
            else spawnBill();             // 20% chance billete
        });

        // --- Colisiones ---

        player.onCollide("money", (m) => {
            k.destroy(m);
            missionState.score += m.value;
            scoreLabel.text = `Dinero: ${missionState.score} / ${missionState.requiredScore}`;
            k.addKaboom(m.pos);

            checkWin();
        });

        player.onCollide("tax", (t) => {
            k.destroy(t);
            missionState.score = Math.max(0, missionState.score - 2); // Resta puntos
            scoreLabel.text = `Dinero: ${missionState.score} / ${missionState.requiredScore}`;
            k.shake(5);
            player.color = k.rgb(255, 0, 0); // Flash rojo
            k.wait(0.2, () => player.color = k.rgb(255, 255, 255));
        });

        // Limpieza al tocar el suelo (plataforma)
        k.onCollide("money", "platform", (m) => k.destroy(m));
        k.onCollide("tax", "platform", (t) => k.destroy(t));


        function checkWin() {
            if (missionState.score >= missionState.requiredScore && !missionState.isGameOver) {
                missionState.isGameOver = true;
                k.add([
                    k.text("¡META ALCANZADA!", { size: 40 }),
                    k.pos(k.center()),
                    k.anchor("center"),
                    k.color(0, 255, 0)
                ]);

                k.wait(1.5, () => {
                    Livewire.dispatch('kaplay-completed');
                });
            }
        }
    });

    k.go("level13");
}