import './bootstrap';
// Importamos la función principal de nuestro gestor de juego.
import { initializeGame, cleanupGame } from './game/gameManager';

let currentLevelInstance = null;

Livewire.hook('component.init', ({ component, cleanup }) => {
    // Verificamos si el componente pertenece a los niveles del videojuego.
    const match = component.name.match(/^videogame\.levels\.(\w+)\.index-component$/);
    
    if (match) {
        const levelName = match[1]; // Extrae "first", "second", etc.
        const canvas = document.querySelector("#edugamer-canvas");
        
        if (canvas) {
            // Llamamos a nuestro gestor para que inicie el nivel correspondiente.
            // initializeGame devuelve la instancia de Kaplay para poder limpiarla después.
            currentLevelInstance = initializeGame(levelName, canvas);
        }
    }

    cleanup(() => {
        // Al salir del componente Livewire, destruimos la instancia del juego.
        if (currentLevelInstance) {
            cleanupGame(currentLevelInstance);
            currentLevelInstance = null;
        }
    });
});

Livewire.hook('request', ({ fail }) => {
    fail(({ status, preventDefault }) => {
        if (status === 419) {
            preventDefault();
            location.reload();
        }
    });
});