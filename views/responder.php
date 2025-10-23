<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS-OP - Responder Oficio</title>
    <link rel="icon" type="image/png" href="/oficialiadepartes/css/images/icono2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="/oficialiadepartes/css/globals/style-body.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/style-sidebar.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/style-badge.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/dashboard/styleresponder.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/buttonaction.css">

    
</head>
<body>
    <!-- Incluir el sidebar -->
    <?php include 'partials/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <?php include 'partials/header.php'; ?>

        <!-- Mostrar mensajes de error -->
        <?php if (!empty($mensaje_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($mensaje_error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        
        <!-- Page Title -->
        <h3 class="page-title">Responder Oficio</h3>

        <!-- Document Details -->
        <div class="document-details">
            <h5 class="mb-4">Detalles del Oficio</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <!--div class="detail-row">
                        <span class="detail-label">ID:</span>
                        <span class="detail-value"><?php echo $oficio['id']; ?></span>
                    </div-->
                    <div class="detail-row">
                        <span class="detail-label">Fecha de Registro:</span>
                        <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($oficio['fecha_registro'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Remitente:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($oficio['remitente']); ?></span>
                    </div>
                    <!--div class="detail-row">
                        <span class="detail-label">Asunto:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($oficio['asunto']); ?></span>
                    </div-->
                    <div class="detail-row">
                        <span class="detail-label">Número de Documento:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($oficio['numero_documento'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Estado:</span>
                        <span class="detail-value">
                            <?php 
                            $badge_class = '';
                            switch ($oficio['estado']) {
                                case 'pendiente':
                                    $badge_class = 'badge-pendiente';
                                    $estado_texto = 'Pendiente';
                                    break;
                                case 'tramite':
                                    $badge_class = 'badge-proceso';
                                    $estado_texto = 'En tramite';
                                    break;
                                case 'completado':
                                    $badge_class = 'badge-completado';
                                    $estado_texto = 'Completado';
                                    break;
                                case 'denegado':
                                    $badge_class = 'badge-denegado';
                                    $estado_texto = 'Denegado';
                                    break;
                                default:
                                    $badge_class = 'badge-pendiente';
                                    $estado_texto = 'Pendiente';
                            }
                            ?>
                            <span class="badge-estado <?php echo $badge_class; ?>"><?php echo $estado_texto; ?></span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Área Origen:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($oficio['nombre'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Usuario Origen:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($oficio['usuario_nombre'] ?? 'N/A'); ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    
                </div>
            </div>
            
            <?php if (!empty($oficio['area_derivada_nombre'])): ?>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="detail-row">
                            <span class="detail-label">Derivado a:</span>
                            <span class="detail-value">
                                <?php echo htmlspecialchars($oficio['area_derivada_nombre']); ?>
                                <?php if (!empty($oficio['usuario_derivado_nombre'])): ?>
                                    (Usuario: <?php echo htmlspecialchars($oficio['usuario_derivado_nombre']); ?>)
                                <?php endif; ?>
                                <?php if (!empty($oficio['fecha_derivacion'])): ?>
                                    - <?php echo date('d/m/Y H:i', strtotime($oficio['fecha_derivacion'])); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($oficio['respuesta'])): ?>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="detail-row">
                            <span class="detail-label">Comentarios:</span>
                            <span class="detail-value"><?php echo nl2br(htmlspecialchars($oficio['respuesta'])); ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- File Preview -->
        <?php if (!empty($oficio['archivo_ruta'])): ?>
            <div class="file-preview">
                <h5 class="mb-3">Documento Adjunto</h5>
                <div class="d-flex align-items-center">
                    <i class="fas fa-file-pdf me-2 text-danger" style="font-size: 2rem;"></i>
                    <div>
                        <div class="fw-bold">Documento adjunto</div>
                        <div ><?php echo basename($oficio['archivo_ruta']); ?></div>
                    </div>
                    <div class="ms-auto">
                        <a href="<?php echo $oficio['archivo_ruta']; ?>" target="_blank" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye me-1"></i> Ver documento
                        </a>
                        <a href="<?php echo $oficio['archivo_ruta']; ?>" download class="btn btn-secondary btn-sm">
                            <i class="fas fa-download me-1"></i> Descargar
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Este oficio no tiene documentos adjuntos.
            </div>
        <?php endif; ?>

        <!-- Response Form -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Responder al Oficio</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="respuesta" class="form-label">Respuesta</label>
                        <textarea class="form-control" id="respuesta" name="respuesta" rows="6" placeholder="Escriba aquí su respuesta al oficio..." required><?php echo isset($_POST['respuesta']) ? htmlspecialchars($_POST['respuesta']) : ''; ?></textarea>
                    </div>
                    
                    <div class="action-buttons">
                        <div class="d-flex justify-content-between">

                            <?php if ($_SESSION['tipo_usuario'] === 'Administrador' || $_SESSION['tipo_usuario'] === 'Usuario'): ?>
                            <a href="index.php?action=expedientes" class="btn-action btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <?php endif; ?>

                            <div>
                                <button type="submit" name="denegar" class="btn-action btn-danger me-2">
                                    <i class="fas fa-times-circle me-1"></i> Denegar
                                </button>
                                <button type="submit" name="completar" class="btn-action btn-success">
                                    <i class="fas fa-check-circle me-1"></i> Completar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../oficialiadepartes/js/navbar.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>