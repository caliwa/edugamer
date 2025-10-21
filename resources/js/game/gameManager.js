import kaplay from "kaplay";

// Esta función es la que se llama desde app.js
export function initializeGame(levelName, canvasElement) {
    try {
        const k = kaplay({
            canvas: canvasElement,
            width: 800,
            height: 480,
            stretch: true,
            letterbox: true,
            background: [45, 35, 25],
        });

        // Importación dinámica: solo carga el JS del nivel actual.
        // Esto es genial para el rendimiento.
        import(`./levels/${levelName}.js`)
            .then(levelModule => {
                // Una vez cargado el módulo, ejecutamos su función 'default'
                // pasándole la instancia 'k' de Kaplay.
                levelModule.default(k);
            })
            .catch(err => {
                console.error(`Error al cargar el nivel '${levelName}':`, err);
                // Opcional: mostrar un mensaje de error en el canvas.
                k.add([k.text("Error al cargar nivel.")]);
            });

        return k; // Devolvemos la instancia para que pueda ser gestionada.

    } catch (e) {
        console.error("Error al inicializar Kaplay:", e);
        return null;
    }
}

// Función para limpiar la instancia del juego.
export function cleanupGame(k_instance) {
    if (k_instance) {
        k_instance.quit();
        console.log("Instancia de Kaplay destruida.");
    }
}