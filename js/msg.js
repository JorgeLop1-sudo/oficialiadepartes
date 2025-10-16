        // Limpiar parámetros de mensaje de la URL después de mostrar el mensaje
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('mensaje')) {
                // Remover el parámetro mensaje de la URL sin recargar
                urlParams.delete('mensaje');
                const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                window.history.replaceState({}, document.title, newUrl);
            }
        });