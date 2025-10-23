// pvirtuales/server/public/js/main.js

// Espera a que toda la página HTML se haya cargado
document.addEventListener("DOMContentLoaded", function() {

    // 1. Encuentra el botón y el área de mensajes en el HTML
    const helloButton = document.getElementById("helloButton");
    const messageArea = document.getElementById("messageArea");

    // 2. Añade un "escuchador" de clics al botón
    helloButton.addEventListener("click", function() {
        
        // Muestra un mensaje de carga
        messageArea.textContent = "Conectando con Laravel...";
        messageArea.style.color = "#ccc";

        // 3. Llama a la ruta /api/hello que creamos en Laravel
        fetch("/api/hello") // La ruta es relativa al dominio
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error: ' + response.statusText);
                }
                return response.json(); // Convierte la respuesta a JSON
            })
            .then(data => {
                // 4. Si todo va bien, muestra el mensaje "Hola Mundo"
                messageArea.textContent = data.message; // data.message es "Hola Mundo"
                messageArea.style.color = "#2ecc71"; // Verde
            })
            .catch(error => {
                // 5. Si algo falla (la API no responde, etc.)
                console.error('Error:', error);
                messageArea.textContent = "Error al conectar con la API.";
                messageArea.style.color = "#e74c3c"; // Rojo
            });
    });
});