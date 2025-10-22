import { loadCommonAssets, loadLevelAssets } from '../assets.js';

// !! ADJUSTED PHYSICS VALUES !!
const SHIP_THRUST = 500000;  // *** MASSIVELY INCREASED THRUST ***
const ROTATE_SPEED = 200;
const ASTEROID_SPEED = 50;
const DRAG = 1;             // *** NO DRAG (set to 1 temporarily) ***
const MAX_SPEED = 2000;      // *** INCREASED MAX SPEED ***

const missionState = {
    orbsCollected: 0,
    requiredOrbs: 3,
    levelComplete: false,
};
const collectedOrbIds = new Set();

export default function setupLevelEighth(k) {
    missionState.orbsCollected = 0;
    missionState.levelComplete = false;
    collectedOrbIds.clear();

    loadCommonAssets(k);
    loadLevelAssets(k);
    k.setGravity(0);

    k.scene("level8", () => {

        // --- Create Player Ship ---
        const playerShip = k.add([
            k.pos(k.center()),
            k.sprite("ship"),
            k.anchor("center"),
            k.area(),
            k.rotate(0),
            { vel: k.vec2(0, 0) }, // Current velocity vector
            "player"
        ]);

        // --- Ship Controls ---
        let rotatingLeft = false;
        let rotatingRight = false;
        let thrusting = false;

        // Keyboard Controls
        k.onKeyDown("left", () => rotatingLeft = true);
        k.onKeyDown("a", () => rotatingLeft = true);
        k.onKeyRelease("left", () => rotatingLeft = false);
        k.onKeyRelease("a", () => rotatingLeft = false);

        k.onKeyDown("right", () => rotatingRight = true);
        k.onKeyDown("d", () => rotatingRight = true);
        k.onKeyRelease("right", () => rotatingRight = false);
        k.onKeyRelease("d", () => rotatingRight = false);

        k.onKeyDown("up", () => thrusting = true);
        k.onKeyDown("w", () => thrusting = true);
        k.onKeyRelease("up", () => thrusting = false);
        k.onKeyRelease("w", () => thrusting = false);

         // Touch/Mouse Button Controls
        const leftBtn = document.getElementById('leftBtn');
        const rightBtn = document.getElementById('rightBtn');
        const thrustBtn = document.getElementById('thrustBtn');
        const preventDefault = (e) => { e.preventDefault(); e.stopPropagation(); };

        if (leftBtn) {
            leftBtn.addEventListener('touchstart', (e) => { preventDefault(e); rotatingLeft = true; });
            leftBtn.addEventListener('touchend', (e) => { preventDefault(e); rotatingLeft = false; });
            leftBtn.addEventListener('mousedown', (e) => { preventDefault(e); rotatingLeft = true; });
            leftBtn.addEventListener('mouseup', (e) => { preventDefault(e); rotatingLeft = false; });
            leftBtn.addEventListener('mouseleave', (e) => rotatingLeft = false);
        }
         if (rightBtn) {
            rightBtn.addEventListener('touchstart', (e) => { preventDefault(e); rotatingRight = true; });
            rightBtn.addEventListener('touchend', (e) => { preventDefault(e); rotatingRight = false; });
            rightBtn.addEventListener('mousedown', (e) => { preventDefault(e); rotatingRight = true; });
            rightBtn.addEventListener('mouseup', (e) => { preventDefault(e); rotatingRight = false; });
            rightBtn.addEventListener('mouseleave', (e) => rotatingRight = false);
         }
        if (thrustBtn) {
            thrustBtn.addEventListener('touchstart', (e) => { preventDefault(e); thrusting = true; });
            thrustBtn.addEventListener('touchend', (e) => { preventDefault(e); thrusting = false; });
            thrustBtn.addEventListener('mousedown', (e) => { preventDefault(e); thrusting = true; });
            thrustBtn.addEventListener('mouseup', (e) => { preventDefault(e); thrusting = false; });
            thrustBtn.addEventListener('mouseleave', (e) => { preventDefault(e); thrusting = false; });
        }


        // Update loop for rotation, thrust, drag, movement, and screen wrap
        playerShip.onUpdate(() => {
            // Apply rotation
            if (rotatingLeft) playerShip.angle -= ROTATE_SPEED * k.dt();
            if (rotatingRight) playerShip.angle += ROTATE_SPEED * k.dt();

            // Apply thrust (acceleration changes velocity)
            if (thrusting) {
                 const direction = k.Vec2.fromAngle(playerShip.angle);
                 // Apply force * dt
                 playerShip.vel = playerShip.vel.add(direction.scale(SHIP_THRUST * k.dt()));
            }

            // Apply drag/friction (Currently disabled with DRAG = 1)
            if (DRAG !== 1) { // Only apply if drag is enabled
                playerShip.vel = playerShip.vel.scale(DRAG);
            }


            // Optional: Limit speed
            if (playerShip.vel.len() > MAX_SPEED) {
                playerShip.vel = playerShip.vel.unit().scale(MAX_SPEED);
            }

            // Move the ship based on its current velocity * dt
            playerShip.move(playerShip.vel.scale(k.dt()));

            // Wrap around screen with margin
            if (playerShip.pos.x > k.width() + 10) playerShip.pos.x = -10;
            if (playerShip.pos.x < -10) playerShip.pos.x = k.width() + 10;
            if (playerShip.pos.y > k.height() + 10) playerShip.pos.y = -10;
            if (playerShip.pos.y < -10) playerShip.pos.y = k.height() + 10;
        });


        // --- Create Idea Orbs (Collectibles) ---
        for (let i = 0; i < missionState.requiredOrbs; i++) {
            k.add([
                k.pos(k.rand(k.width() * 0.1, k.width() * 0.9), k.rand(k.height() * 0.1, k.height() * 0.9)),
                k.sprite("idea_orb"),
                k.anchor("center"),
                k.area(),
                k.scale(0.7),
                { orbId: k.randi(1, 10000) }, // Unique ID
                "collectible"
            ]);
        }

        // --- Create Asteroids (Hazards) ---
         for (let i = 0; i < 5; i++) { // Add 5 asteroids
            k.add([
                k.pos(k.rand(0, k.width()), k.rand(0, k.height())),
                k.sprite("asteroid"),
                k.anchor("center"),
                k.area(),
                k.rotate(k.rand(0, 360)),
                k.move(k.rand(0, 360), ASTEROID_SPEED), // Random direction and speed
                k.offscreen({ wrap: true }), // Wrap when offscreen
                "hazard" // Collision tag
            ]);
        }


        // --- Collision Logic ---

        // Collect Orb
        playerShip.onCollide("collectible", (orb) => {
            if (orb.orbId && !collectedOrbIds.has(orb.orbId) && !missionState.levelComplete) {
                collectedOrbIds.add(orb.orbId);
                k.destroy(orb);
                missionState.orbsCollected++;
                console.log("Orb collected! Total:", missionState.orbsCollected);
                // Check for completion
                if (missionState.orbsCollected >= missionState.requiredOrbs) {
                    console.log("SUCCESS: All orbs collected. Level Complete.");
                    missionState.levelComplete = true;
                    k.wait(0.5, () => {
                        Livewire.dispatch('kaplay-completed');
                    });
                }
            }
        });

        // Hit Asteroid
        playerShip.onCollide("hazard", (asteroid) => {
            if (!missionState.levelComplete) { // Only reset if game not won
                console.log("FAIL: Hit asteroid. Restarting level.");
                // k.addKaboom(playerShip.pos); // Optional explosion effect
                k.go("level8"); // Restart current scene
            }
        });

    });

    k.go("level8");
}