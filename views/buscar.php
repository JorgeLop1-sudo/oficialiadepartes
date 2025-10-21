<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oficialia de partes - Buscar Trámite</title>
    <link rel="icon" type="image/png" href="/oficialiadepartes/css/images/icono2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/inicio/stylesinicio.css" id="theme-style">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/buttonaction.css" id="theme-style">


    <!-- Meta tags para evitar cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
</head>
<body>

    <div class="all-login">
            <div class="imagen d-none d-lg-flex">
                <img src="/oficialiadepartes/css/images/fondo-login.png" alt="logo">
            </div>

            <div class="main-container">

                <?php include 'partials/headerinicio.php'; ?>

                <div class="content">
                    <div class="form">
                        <h3 class="text-center mb-4">Buscar Trámite</h3>
                        
                        <form id="searchForm" method="POST">
                            <div class="mb-3">
                                <label for="expediente" class="form-label">Ingresar N° de Expediente</label>
                                <input type="text" class="form-control" id="expediente" name="expediente" placeholder="Ejemplo: 7878787" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="codigo" class="form-label">Ingresar Código de Seguridad</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" placeholder="Ingrese el código mostrado" required>
                            </div>
                            
                            <div class="security-code">
                                <span id="codigoSeguridad">A7B9</span>
                                <div>
                                    <p class="mb-1">Código de seguridad (distingue entre mayúsculas y minúsculas)</p>
                                    <a href="#" onclick="generarCodigo(); return false;"><i class="fas fa-sync-alt"></i> Generar nuevo código</a>
                                </div>
                            </div>
                            <div class="divbtn-action">
                                <button type="submit" class="btn-action">
                                    Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    </div>


    <!-- Modal para mostrar resultados -->
    <div class="modal fade" id="resultadoModal" tabindex="-1" aria-labelledby="resultadoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultadoModalLabel">Información del Oficio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Aquí se cargará la información del oficio -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-action" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para generar un código de seguridad aleatorio
        function generarCodigo() {
            const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let codigo = '';
            for (let i = 0; i < 4; i++) {
                codigo += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
            }
            document.getElementById('codigoSeguridad').textContent = codigo;
        }
        
        // Generar código inicial al cargar la página
        window.onload = function() {
            generarCodigo();
        };
        
        // Validación del formulario y envío mediante AJAX
        document.getElementById('searchForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            const expediente = document.getElementById('expediente').value;
            const codigo = document.getElementById('codigo').value;
            const codigoSeguridad = document.getElementById('codigoSeguridad').textContent;
            
            if (!expediente) {
                alert('Por favor, ingrese el número de expediente');
                return;
            }
            
            if (codigo !== codigoSeguridad) {
                alert('El código de seguridad no coincide. Por favor, inténtelo de nuevo.');
                generarCodigo();
                return;
            }
            
            // Realizar la búsqueda mediante AJAX (ahora usando el controlador MVC)
            buscarOficio(expediente);
        });
        
        function buscarOficio(numeroDocumento) {
            // Crear una solicitud AJAX al controlador MVC
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'index.php?action=buscarOficio', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        
                        if (response.success) {
                            mostrarResultadoEnModal(response.data);
                        } else {
                            alert(response.message || 'No se encontró el oficio solicitado.');
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        console.error('Response received:', this.responseText);
                        alert('Error al procesar la respuesta del servidor. Respuesta: ' + this.responseText);
                    }
                } else {
                    alert('Error en la conexión con el servidor. Status: ' + this.status);
                }
            };
            
            xhr.onerror = function() {
                alert('Error en la conexión con el servidor.');
            };
            
            // Enviar la solicitud
            xhr.send('numero_documento=' + encodeURIComponent(numeroDocumento));
        }
        
        function mostrarResultadoEnModal(oficio) {
            // Formatear la fecha
            const fechaRegistro = new Date(oficio.fecha_registro).toLocaleString('es-ES');
            const fechaDerivacion = oficio.fecha_derivacion ? new Date(oficio.fecha_derivacion).toLocaleString('es-ES') : 'No derivado';
            const fechaRespuesta = oficio.fecha_respuesta ? new Date(oficio.fecha_respuesta).toLocaleString('es-ES') : 'Sin respuesta';
            
            // Determinar la clase CSS según el estado
            let estadoClass = '';
            switch(oficio.estado) {
                case 'pendiente':
                    estadoClass = 'status-pendiente';
                    break;
                case 'tramite':
                    estadoClass = 'status-tramite';
                    break;
                case 'completado':
                    estadoClass = 'status-completado';
                    break;
                case 'denegado':
                    estadoClass = 'status-denegado';
                    break;
            }
            
            // Construir el HTML para el modal
            const modalContent = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Información del Remitente</h6>
                        <p><strong>Remitente:</strong> ${oficio.remitente}</p>
                        <p><strong>Dependencia:</strong> ${oficio.dependencia}</p>
                        <p><strong>N° Documento:</strong> ${oficio.numero_documento}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Detalles del Oficio</h6>
                        <p><strong>Estado:</strong> <span class="status-badge ${estadoClass}">${oficio.estado}</span></p>
                        <p><strong>Fecha de Registro:</strong> ${fechaRegistro}</p>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Seguimiento</h6>
                        <p><strong>Área Derivada:</strong> ${oficio.area_derivada || 'No derivado'}</p>
                        <p><strong>Usuario Derivado:</strong> ${oficio.usuario_derivado || 'No derivado'}</p>
                        <p><strong>Fecha de Derivación:</strong> ${fechaDerivacion}</p>
                        <p><strong>Respuesta:</strong> ${oficio.respuesta || 'Sin respuesta'}</p>
                        <p><strong>Fecha de Respuesta:</strong> ${fechaRespuesta}</p>
                    </div>
                </div>
                
                ${oficio.archivo_nombre ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Archivo Adjunto</h6>
                        <p><strong>Nombre:</strong> ${oficio.archivo_nombre}</p>
                        <div class="btndescargar">
                            <a href="${oficio.archivo_ruta}" class="btn-action" target="_blank">Descargar</a>
                        </div>
                    </div>
                </div>
                ` : ''}
            `;
            
            // Insertar el contenido en el modal
            document.getElementById('modalBody').innerHTML = modalContent;
            
            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('resultadoModal'));
            modal.show();
        }
    </script>

</body>
</html>