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
                            <label for="tipoPersona" class="form-label">Tipo de Persona</label>
                            <select class="form-select" id="tipoPersona" name="tipoPersona" required>
                                <option value="" selected disabled>Seleccionar tipo</option>
                                <option value="natural">Persona Natural</option>
                                <option value="juridica">Persona Jurídica</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label for="dependencia" class="form-label">Dependencia</label>
                            <input type="text" class="form-control" id="dependencia" name="dependencia" placeholder="Dependencia" required>
                        </div>
                        <div class="col-md-6">

                            <label class="form-label">Folio de oficio</label>
                            
                            <input type="text" class="form-control" id="numeroDocumento" name="numeroDocumento" placeholder="Número de oficio">
                        </div>
                        
                    </div>
                </div>
                
                <!-- Sección de Contacto -->
                <!--div class="form-section">
                    <h3 class="form-section-title">Datos de Contacto</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo" placeholder="ejemplo@correo.com" required>
                        </div>
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Número de teléfono" required>
                        </div>
                    </div>
                </div-->
                
                <!-- Sección de Contenido -->
                <div class="form-section">
                    <h3 class="form-section-title">Contenido del Trámite</h3>
                    
                    <!--div class="mb-3">
                        <label for="asunto" class="form-label">Asunto</label>
                        <input type="text" class="form-control" id="asunto" name="asunto" placeholder="Asunto del trámite" required>
                    </div-->
                    
                    <div class="mb-3">
                        <label class="form-label">Archivo</label>
                        <div class="file-upload" id="fileUploadArea">
                            <input type="file" id="archivo" name="archivo" style="display: none;">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Seleccionar archivo</p>
                            <p class="file-name" id="fileName">Ningún archivo seleccionado</p>
                        </div>
                    </div>
                    
                    <!-- Información fija sobre el área y usuario asignado -->
                    <div class="alert alert-info">
                        <strong>Información del registro:</strong><br>
                        - Este documento será dirigido hacia recepción
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
        document.getElementById('cancelButton').addEventListener('click', function() {
            if (confirm('¿Está seguro que desea cancelar? Se perderán todos los datos ingresados.')) {
                window.location.href = 'index.php';
            }
        });
        
    </script>
    <script src="../oficialiadepartes/js/navbar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
</body>
</html>