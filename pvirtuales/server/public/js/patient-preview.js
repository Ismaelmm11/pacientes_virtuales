/*
|--------------------------------------------------------------------------
| JS - Previsualización de Prompt
|--------------------------------------------------------------------------
|
| Gestiona la previsualización del prompt generado:
| - Renderizado de Markdown a HTML (usando marked.js)
| - Toggle entre vista formateada y código fuente
| - Copiar prompt al portapapeles
|
*/

// ==================== RENDERIZAR MARKDOWN ====================

/**
 * Al cargar el documento, toma el texto plano del prompt y lo convierte
 * en HTML visualmente atractivo (negritas, listas, etc.) usando la librería Marked.
 */
document.addEventListener('DOMContentLoaded', function () {
    const sourceElement = document.getElementById('promptSource');   // El contenedor con el texto crudo
    const renderedElement = document.getElementById('promptRendered'); // El contenedor para el HTML final

    /* Verificamos que ambos elementos existan en la página actual para evitar errores */
    if (sourceElement && renderedElement) {
        // marked.parse() transforma la sintaxis Markdown en etiquetas HTML
        renderedElement.innerHTML = marked.parse(sourceElement.textContent);
    }
});

// ==================== TOGGLE DE VISTA ====================

/**
 * Alterna la visibilidad entre la vista previa "bonita" y el código fuente.
 * @param {string} view - El tipo de vista seleccionado ('rendered' o 'source')
 */
function showView(view) {
    const rendered = document.getElementById('promptRendered');
    const source = document.getElementById('promptSource');
    const buttons = document.querySelectorAll('.view-btn');

    /* Limpia la clase 'active' de todos los botones para resetear el estado visual */
    buttons.forEach(btn => btn.classList.remove('active'));

    if (view === 'rendered') {
        rendered.style.display = 'block'; // Muestra HTML
        source.style.display = 'none';    // Oculta texto plano
        buttons[0].classList.add('active'); // Activa el primer botón (Renderizado)
    } else {
        rendered.style.display = 'none';   // Oculta HTML
        source.style.display = 'block';    // Muestra texto plano
        buttons[1].classList.add('active'); // Activa el segundo botón (Código)
    }
}

// ==================== COPIAR PROMPT ====================

/**
 * Utiliza la API moderna del portapapeles para copiar el contenido del prompt.
 * Incluye una respuesta visual temporal en el botón para confirmar la acción.
 */
function copyPrompt() {
    const promptText = document.getElementById('promptSource').textContent;
    const btn = document.getElementById('copyBtn');

    /* Navigator Clipboard API: método asíncrono para copiar texto */
    navigator.clipboard.writeText(promptText).then(function () {
        // --- Feedback Visual Exitoso ---
        btn.classList.add('copied'); // Clase CSS opcional para cambiar color
        btn.querySelector('span').textContent = '¡Copiado!'; // Cambia el texto del botón

        /* Tras 2 segundos, devuelve el botón a su estado original */
        setTimeout(function () {
            btn.classList.remove('copied');
            btn.querySelector('span').textContent = 'Copiar Prompt';
        }, 2000);
    }).catch(function (err) {
        /* Manejo de errores (por ejemplo, si el navegador bloquea el acceso al portapapeles) */
        console.error('Error al copiar:', err);
        alert('Error al copiar. Intenta seleccionar el texto manualmente.');
    });
}

