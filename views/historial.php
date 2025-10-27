<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS-OP - Historial de Derivaciones</title>
    <link rel="icon" type="image/png" href="/oficialiadepartes/css/images/icono2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <link rel="stylesheet" href="/oficialiadepartes/css/globals/style-body.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/style-sidebar.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/style-badge.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/dashboard/stylehistorial.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/buttonaction.css">

</head>
<body>
<!-- Incluir el sidebar -->
<?php include 'partials/sidebar.php'; ?>
    
<!-- Main Content -->
<div class="main-content">
    <!-- Header -->
    <?php include 'partials/header.php'; ?>

    <!-- Page Title -->
    <!--div class="page-header">
        <h3 class="page-title">
            <?php if ($info_oficio): ?>
                Historial de Derivaciones - Oficio #<?php echo $info_oficio['id']; ?>
            <?php else: ?>
                Historial de Derivaciones
            <?php endif; ?>
        </h3>
            
        <?php if (!$info_oficio): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Selecciona un oficio desde la gestión de expedientes para ver su historial completo.
            </div>
        <?php endif; ?>
    </div-->

    <?php if ($info_oficio): ?>
        <!-- Información del Oficio -->
        <div class="card oficio-info-card">
            <div class="card-header">
                <h5>Información del Oficio</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>ID:</strong> <?php echo $info_oficio['id']; ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Remitente:</strong> <?php echo htmlspecialchars($info_oficio['remitente']); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>N° Documento:</strong> <?php echo htmlspecialchars($info_oficio['numero_documento'] ?? 'N/A'); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Estado:</strong> 
                        <span class="badge-estado <?php 
                            switch($info_oficio['estado']) {
                                case 'pendiente': echo 'badge-pendiente'; break;
                                case 'tramite': echo 'badge-proceso'; break;
                                case 'completado': echo 'badge-completado'; break;
                                case 'denegado': echo 'badge-denegado'; break;
                            }
                        ?>">
                            <?php 
                            switch($info_oficio['estado']) {
                                case 'pendiente': echo 'Pendiente'; break;
                                case 'tramite': echo 'En trámite'; break;
                                case 'completado': echo 'Completado'; break;
                                case 'denegado': echo 'Denegado'; break;
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

            <!-- Historial de Derivaciones -->
        <div class="historial-container">
            <div class="historial-header">
                <h5>Línea de Tiempo de Derivaciones</h5>
                <div class="historial-info">
                    Total de registros: <?php echo count($historial); ?>
                </div>
            </div>
                <?php if (empty($historial)): ?>
                    <div class="no-historial-message">
                        <i class="fas fa-history"></i>
                        <h4>No hay historial de derivaciones</h4>
                        <p>Este oficio no ha sido derivado aún.</p>
                    </div>
                <?php else: ?>
                <div class="timeline">
                    <?php 
                    $last_registro_id = null;
                    foreach ($historial as $index => $registro): 
                        // Saltar registros duplicados consecutivos
                        if ($last_registro_id === $registro['id']) {
                            continue;
                        }
                        $last_registro_id = $registro['id'];
                    ?>
                        <div class="timeline-item <?php echo $index % 2 == 0 ? 'left' : 'right'; ?>">
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <span class="timeline-date">
                                        <?php echo date('d/m/Y H:i', strtotime($registro['fecha_derivacion'])); ?>
                                    </span>
                                    <span class="timeline-badge <?php 
                                        echo $registro['tipo_registro'] == 'RESPUESTA' ? 'badge-final' : 
                                            ($registro['tipo_registro'] == 'DERIVACIÓN' ? 'badge-derivacion' : 'badge-registro');
                                        ?>">
                                        <?php echo $registro['tipo_registro']; ?>
                                    </span>
                                    <small class="registro-id">ID: <?php echo $registro['id']; ?></small>
                                </div>
                                    
                                <div class="timeline-body">
                                    <!-- Mostrar siempre quién realizó la acción -->
                                    <div class="accion-info">
                                        <strong>Acción realizada por:</strong>
                                        <div class="user-info">
                                            <i class="fas fa-user"></i>
                                            <?php echo htmlspecialchars($registro['usuario_origen_nombre']); ?>                                                (<?php echo htmlspecialchars($registro['usuario_origen_usuario']); ?>)
                                        </div>
                                        <div class="area-info">
                                            <i class="fas fa-building"></i>
                                            <?php echo htmlspecialchars($registro['area_origen_nombre']); ?>
                                        </div>
                                    </div>

                                    <!-- Solo mostrar destino si es una derivación -->
                                    <?php if ($registro['tipo_registro'] == 'DERIVACIÓN' && $registro['area_destino_nombre']): ?>
                                        <div class="destino-info">
                                            <strong>Derivado a:</strong>
                                            <div class="user-info">
                                                <i class="fas fa-user"></i>
                                                <?php echo htmlspecialchars($registro['usuario_destino_nombre']); ?>
                                                (<?php echo htmlspecialchars($registro['usuario_destino_usuario']); ?>)
                                            </div>
                                            <div class="area-info">
                                                <i class="fas fa-building"></i>
                                                <?php echo htmlspecialchars($registro['area_destino_nombre']); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($registro['respuesta'])): ?>
                                        <div class="comentario-info">
                                            <strong>Comentario:</strong>
                                            <p><?php echo nl2br(htmlspecialchars($registro['respuesta'])); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="estado-info">
                                        <strong>Estado:</strong>
                                        <span class="badge-estado <?php 
                                            switch($registro['estado']) {
                                                case 'pendiente': echo 'badge-pendiente'; break;
                                                case 'tramite': echo 'badge-proceso'; break;
                                                case 'completado': echo 'badge-completado'; break;
                                                case 'denegado': echo 'badge-denegado'; break;
                                            }
                                        ?>">
                                            <?php echo ucfirst($registro['estado']); ?>
                                        </span>
                                    </div>

                                    <?php if ($registro['tiempo_formateado'] && $registro['tipo_registro'] != 'RESPUESTA'): ?>
                                        <div class="tiempo-info">
                                            <strong>Tiempo en esta etapa:</strong>
                                            <span class="tiempo-duracion"><?php echo $registro['tiempo_formateado']; ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($_SESSION['tipo_usuario'] === 'Administrador' || $_SESSION['tipo_usuario'] === 'Usuario'): ?>
    <a href="index.php?action=expedientes" class="btn-action btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
<?php endif; ?>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../oficialiadepartes/js/navbar.js"></script>
</body>
</html>