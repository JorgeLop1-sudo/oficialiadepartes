<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS-MPV - Dashboard</title>
    <link rel="icon" type="image/png" href="/oficialiadepartes/css/images/icono2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/style-body.css" >
    <link rel="stylesheet" href="/oficialiadepartes/css/dashboard/stylehome.css">

</head>
<body>
    <!-- Incluir el sidebar -->
    <?php include 'partials/sidebar.php'; ?>

    
    <div class="main-content">
        
    <?php include 'partials/header.php'; ?>

       
        <h3 class="page-title">Resumen de Oficios</h3>
        <div class="stats-container">

            <?php if ($_SESSION['tipo_usuario'] === 'Administrador'): ?>
            <div class="stat-card pending" onclick="filterOficios('pendiente')">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?php echo $estadisticas['pendientes']; ?></div>
                <div class="stat-title">Pendientes</div>
            </div>
            <?php endif; ?>
            
            <div class="stat-card in-process" onclick="filterOficios('tramite')">
                <div class="stat-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-number"><?php echo $estadisticas['en_tramite']; ?></div>
                <div class="stat-title">En Trámite</div>
            </div>
            
            <div class="stat-card completed" onclick="filterOficios('completado')">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?php echo $estadisticas['atendidos']; ?></div>
                <div class="stat-title">Atendidos</div>
            </div>
            
            <div class="stat-card denied" onclick="filterOficios('denegado')">
                <div class="stat-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-number"><?php echo $estadisticas['denegados']; ?></div>
                <div class="stat-title">Denegados</div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <h4 class="activity-title">Actividad Reciente</h4>
            
            <?php if (empty($actividad_reciente)): ?>
                <div class="no-activity">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>No hay actividad reciente</p>
                </div>
            <?php else: ?>
                <?php foreach ($actividad_reciente as $oficio): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="<?php echo getActivityIcon($oficio['estado']); ?>"></i>
                        </div>
                        <div class="activity-content">
                            <h5>
                                <?php 
                                switch ($oficio['estado']) {
                                    case 'pendiente':
                                        echo "Nuevo Oficio Registrado";
                                        break;
                                    case 'tramite':
                                        echo "Oficio en Trámite";
                                        break;
                                    case 'completado':
                                        echo "Oficio Completado";
                                        break;
                                    case 'denegado':
                                        echo "Oficio Denegado";
                                        break;
                                    default:
                                        echo "Actualización de Oficio";
                                }
                                ?>
                            </h5>
                            <p><?php echo $oficio['asunto']; ?></p>
                            <div class="activity-time">
                                <?php echo formatFecha($oficio['fecha_registro']); ?> | 
                                Área: <?php echo $oficio['area_nombre']; ?> | 
                                Por: <?php echo $oficio['usuario_nombre']; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterOficios(estado) {
            const tipoUsuario = "<?php echo $_SESSION['tipo_usuario']; ?>";

            if (tipoUsuario === 'Administrador' || tipoUsuario === 'Usuario') {
                window.location.href = 'index.php?action=expedientes&estado=' + estado;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard administrativo cargado');
        });
    </script>

    <script src="../oficialiadepartes/js/navbar.js"></script>

</body>
</html>