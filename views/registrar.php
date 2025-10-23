<?php
// Headers para prevenir caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oficialia de Partes - Registrar Oficio</title>
    <link rel="icon" type="image/png" href="/oficialiadepartes/css/images/icono2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/style-body.css" >
    <link rel="stylesheet" href="/oficialiadepartes/css/inicio/styleregistro.css">
</head>

<body>
    <!-- Incluir el sidebar -->
    <?php include 'partials/sidebar.php'; ?>

    <!-- Contenido principal -->
    <div class="main-content" id="mainContent">
        
    <?php include 'partials/header.php'; ?>

        <!-- Mostrar mensajes de éxito/error -->
    <?php if (isset($mensaje) && !empty($mensaje)): ?>
        <div class="alert-container">
            <div class="alert alert-<?php echo $tipoMensaje === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

        <div class="form-container">
            <form id="registerForm" method="POST" enctype="multipart/form-data">
                <!-- Sección de Remitente -->
                <div class="form-section">
                    <h3 class="form-section-title">Datos del Remitente</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label for="remitente" class="form-label">Remitente</label>
                            <input type="text" class="form-control" id="remitente" name="remitente" placeholder="Nombre completo o razón social" required>
                        </div>
                        <div class="col-md-6">
                            <label for="dependencia" class="form-label">Dependencia</label>
                            <input type="text" class="form-control" id="dependencia" name="dependencia" placeholder="Dependencia" required>
                        </div>
                        <div class="col-md-6">
                            <label for="numeroDocumento" class="form-label">Folio de oficio</label>
                            <input type="text" class="form-control" id="numeroDocumento" name="numeroDocumento" placeholder="Número de oficio">
                        </div>
                    </div>
                </div>

                <!-- Sección de Destinatario -->
                <div class="form-section">
                    <h3 class="form-section-title">Destinatario Final</h3>

                    <div class="row">
                        <div class="col-md-6">
                            <label for="area_derivada" class="form-label required">Área de Destino</label>                            
                            <select class="form-select" id="area_derivada" name="area_derivada" required onchange="cargarUsuariosPorArea(this.value)">
                                <option value="">Seleccionar área</option>
                                <?php foreach ($areas as $area): ?>
                                <option value="<?php echo $area['id']; ?>"><?php echo htmlspecialchars($area['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="usuario_derivado" class="form-label required">Usuario de Destino</label>                            
                            <select class="form-select" id="usuario_derivado" name="usuario_derivado" required disabled>
                                <option value="">Primero seleccione un área</option>
                            </select>
                            <div class="form-text">Seleccione el área y usuario final al que va dirigido el oficio</div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección de Contenido -->
                <div class="form-section">
                    <h3 class="form-section-title">Contenido del Trámite</h3>
                    <div class="mb-3">
                        <label class="form-label">Archivo</label>
                        <div class="file-upload" id="fileUploadArea">
                            <input type="file" id="archivo" name="archivo" style="display: none;">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Seleccionar archivo</p>
                            <p class="file-name" id="fileName">Ningún archivo seleccionado</p>
                        </div>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn-action btn-register">
                        <i class="fas fa-check-circle me-2"></i> Registrar
                    </button>
                    <a href="index.php?action=registrar" class="btn-action btn-cancel" id="cancelButton">
                        <i class="fas fa-times-circle me-2"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Manejar la subida de archivos
        const fileInput = document.getElementById('archivo');
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileName = document.getElementById('fileName');
        
        fileUploadArea.addEventListener('click', function() {
            fileInput.click();
        });
        
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileName.textContent = this.files[0].name;
            } else {
                fileName.textContent = 'Ningún archivo seleccionado';
            }
        });
        
        // Manejar el botón cancelar
        document.getElementById('cancelButton').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('¿Está seguro que desea cancelar? Se perderán todos los datos ingresados.')) {
                window.location.href = 'index.php';
            }
        });

        // Función para cargar usuarios por área
        function cargarUsuariosPorArea(areaId) {
            var usuarioSelect = $('#usuario_derivado');
            
            if (!areaId) {
                usuarioSelect.html('<option value="">Primero seleccione un área</option>');
                usuarioSelect.prop('disabled', true);
                usuarioSelect.prop('required', true);
                return;
            }
            
            $.ajax({
                url: 'index.php?action=expedientes&ajax=usuarios_por_area&area_id=' + areaId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">Seleccionar usuario</option>';
                        if (response.usuarios.length > 0) {
                            $.each(response.usuarios, function(index, usuario) {
                                options += '<option value="' + usuario.id + '">' + 
                                        usuario.nombre + ' (' + usuario.usuario + ')' + 
                                        '</option>';
                            });
                            usuarioSelect.html(options);
                            usuarioSelect.prop('disabled', false);
                        } else {
                            options = '<option value="">No hay usuarios en esta área</option>';
                            usuarioSelect.html(options);
                            usuarioSelect.prop('disabled', true);
                        }
                    } else {
                        usuarioSelect.html('<option value="">Error al cargar usuarios</option>');
                        usuarioSelect.prop('disabled', true);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', error);
                    usuarioSelect.html('<option value="">Error al cargar usuarios</option>');
                    usuarioSelect.prop('disabled', true);
                }
            });
        }

        // Validación del formulario antes de enviar
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            var areaSelect = document.getElementById('area_derivada');
            var usuarioSelect = document.getElementById('usuario_derivado');
            
            if (!areaSelect.value) {
                e.preventDefault();
                alert('Debe seleccionar un área de destino');
                areaSelect.focus();
                return false;
            }
            
            if (!usuarioSelect.value || usuarioSelect.disabled) {
                e.preventDefault();
                alert('Debe seleccionar un usuario de destino');
                usuarioSelect.focus();
                return false;
            }
            
            return true;
        });
    </script>
    
    <script src="../oficialiadepartes/js/navbar.js"></script>
</body>
</html>