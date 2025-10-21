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

        <!-- Historial de Derivaciones -->
        <?php if (!empty($historial_derivaciones)): ?>
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Derivaciones</h5>
            </div>
            <div class="card-body">
                <div class="derivation-timeline">
                    <?php foreach ($historial_derivaciones as $index => $derivacion): ?>
                        <div class="timeline-item <?php echo ($index === count($historial_derivaciones) - 1) ? 'current' : ''; ?>">
                            <div class="timeline-marker">
                                <i class="fas fa-<?php echo ($index === count($historial_derivaciones) - 1) ? 'play-circle' : 'circle'; ?>"></i>
                            </div>
                            
                            <div class="timeline-content card">
                                <div class="card-header timeline-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="timeline-step">Paso <?php echo $index + 1; ?></strong>
                                        <span class="badge bg-<?php 
                                            switch($derivacion['estado']) {
                                                case 'pendiente': echo 'warning'; break;
                                                case 'tramite': echo 'info'; break;
                                                case 'completado': echo 'success'; break;
                                                case 'denegado': echo 'danger'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?php echo ucfirst($derivacion['estado']); ?>
                                        </span>
                                    </div>
                                    <small class="text-fecha">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($derivacion['fecha_derivacion'])); ?>
                                    </small>
                                </div>
                                
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Origen -->
                                        <div class="col-md-6">
                                            <div class="origin-section">
                                                <h6 class="section-title">
                                                    <i class="fas fa-arrow-right-from-bracket me-1"></i>
                                                    Origen
                                                </h6>
                                                <div class="info-box">
                                                    <div class="info-item">
                                                        <span class="info-label">Área:</span>
                                                        <span class="info-value"><?php echo htmlspecialchars($derivacion['area_origen_nombre'] ?? 'N/A'); ?></span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Usuario:</span>
                                                        <span class="info-value"><?php echo htmlspecialchars($derivacion['usuario_origen_nombre'] ?? 'N/A'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Destino -->
                                        <div class="col-md-6">
                                            <?php if (!empty($derivacion['area_destino_nombre'])): ?>
                                                <div class="destination-section">
                                                    <h6 class="section-title">
                                                        <i class="fas fa-arrow-right-to-bracket me-1"></i>
                                                        Destino
                                                    </h6>
                                                    <div class="info-box">
                                                        <div class="info-item">
                                                            <span class="info-label">Área:</span>
                                                            <span class="info-value"><?php echo htmlspecialchars($derivacion['area_destino_nombre']); ?></span>
                                                        </div>
                                                        <?php if (!empty($derivacion['usuario_destino_nombre'])): ?>
                                                            <div class="info-item">
                                                                <span class="info-label">Usuario:</span>
                                                                <span class="info-value"><?php echo htmlspecialchars($derivacion['usuario_destino_nombre']); ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="final-action-section">
                                                    <h6 class="section-title text-success">
                                                        <i class="fas fa-flag-checkered me-1"></i>
                                                        Acción Final
                                                    </h6>
                                                    <div class="final-status">
                                                        <span class="badge bg-<?php 
                                                            switch($derivacion['estado']) {
                                                                case 'completado': echo 'success'; break;
                                                                case 'denegado': echo 'danger'; break;
                                                                default: echo 'secondary';
                                                            }
                                                        ?>">
                                                            <?php echo strtoupper($derivacion['estado']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Respuesta/Observaciones -->
                                    <?php if (!empty($derivacion['respuesta'])): ?>
                                        <div class="response-section mt-3">
                                            <h6 class="section-title">
                                                <i class="fas fa-comment-dots me-1"></i>
                                                Respuesta/Observaciones
                                            </h6>
                                            <div class="response-content">
                                                <?php echo nl2br(htmlspecialchars($derivacion['respuesta'])); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($derivacion['observaciones'])): ?>
                                        <div class="observations-section mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                <?php echo htmlspecialchars($derivacion['observaciones']); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Page Title -->
        <h3 class="page-title">Responder Oficio</h3>

        <!-- Document Details -->
        <div class="document-details">
            <h5 class="mb-4">Detalles del Oficio</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="detail-row">
                        <span class="detail-label">ID:</span>
                        <span class="detail-value"><?php echo $oficio['id']; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Fecha de Registro:</span>
                        <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($oficio['fecha_registro'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Remitente:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($oficio['remitente']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Asunto:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($oficio['asunto']); ?></span>
                    </div>
                </div>
                <div class="col-md-6">
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
                        <span class="detail-value"><?php echo htmlspecialchars($oficio['area_nombre'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Usuario Origen:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($oficio['usuario_nombre'] ?? 'N/A'); ?></span>
                    </div>
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
                            <span class="detail-label">Respuesta Anterior:</span>
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