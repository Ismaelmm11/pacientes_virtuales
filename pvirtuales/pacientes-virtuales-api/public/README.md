# Directorio `public`

Este es el **único directorio visible desde el exterior** y la raíz del servidor web (`DocumentRoot`).

Contiene el punto de entrada de todas las peticiones, `index.php` (patrón Front Controller), y los assets públicos si los hubiera (imágenes, CSS, etc.).

La principal ventaja es la **seguridad**, ya que todo el código sensible de la aplicación (`app`, `config`, etc.) queda fuera del alcance directo del navegador.