<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS-OP - Gestión de Expedientes</title>
    <link rel="icon" type="image/png" href="/oficialiadepartes/css/images/icono2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="/oficialiadepartes/css/globals/style-body.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/style-sidebar.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/style-badge.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/dashboard/styleexpedientes.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/buttonaction.css">
</head>
<body>
    <!-- Incluir el sidebar -->
    <?php include 'partials/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <?php include 'partials/header.php'; ?>

        <!-- Mostrar mensajes -->
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['mensaje']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Page Title -->
        <h3 class="page-title">Gestión de Expedientes</h3>

        <!-- Search Section -->
        <div class="search-section">
            <h5 class="search-title">Búsqueda de Expedientes</h5>
            <form method="GET" action="">
                <input type="hidden" name="action" value="expedientes">
                <div class="search-grid">
                    <div class="search-field">
                        <label for="numero">Número de Documento</label>
                        <input class="form-control" type="text" id="numero" name="numero" placeholder="Ingrese número de documento" value="<?php echo htmlspecialchars($filtros['numero'] ?? ''); ?>">
                    </div>
                    <div class="search-field">
                        <label for="estado">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="">Todos los estados</option>
                            <?php if ($_SESSION['tipo_usuario'] === 'Administrador' || $_SESSION['id'] == '2'): ?>
                                <option value="pendiente" <?php echo ($filtros['estado'] ?? '') == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <?php endif; ?>
                            <?php if ($_SESSION['tipo_usuario'] === 'Administrador' || $_SESSION['tipo_usuario'] === 'Usuario'): ?>
                                <option value="tramite" <?php echo ($filtros['estado'] ?? '') == 'tramite' ? 'selected' : ''; ?>>En tramite</option>
                                <option value="completado" <?php echo ($filtros['estado'] ?? '') == 'completado' ? 'selected' : ''; ?>>Completado</option>
                                <option value="denegado" <?php echo ($filtros['estado'] ?? '') == 'denegado' ? 'selected' : ''; ?>>Denegado</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="sectionbutton">
                    <a href="index.php?action=expedientes" class="btn-action">Limpiar</a>
                    <button type="submit" class="btn-action">Buscar</button>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="expedientes-container">
            <div class="expedientes-header">
                <h5>Listado de Expedientes</h5>
            </div>
            
            <?php if (empty($expedientes)): ?>
                <div class="no-expedientes-message">
                    <i class="fas fa-folder-open"></i>
                    <h4>No se encontraron expedientes en ese estado</h4>
                    <p>No hay expedientes que coincidan con los criterios de búsqueda.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="expedientes-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha/Hora</th>
                                <th>Remitente</th>
                                <!--th>Asunto</th-->
                                <th>Nro. Documento</th>
                                <th>Estado de oficio</th>
                                <th>Derivado a</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expedientes as $expediente): ?>
                                <tr>
                                    <td><?php echo $expediente['id']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($expediente['fecha_registro'])); ?></td>
                                    <td><?php echo htmlspecialchars($expediente['remitente']); ?></td>
                                    <!--td><?php echo htmlspecialchars($expediente['asunto']); ?></td-->
                                    <td><?php echo htmlspecialchars($expediente['numero_documento'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php 
                                        $badge_class = '';
                                        switch ($expediente['estado']) {
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
                                    </td>
                                    <td>
                                        <?php if (!empty($expediente['area_derivada_nombre'])): ?>
                                            <div><strong>Área:</strong> <?php echo htmlspecialchars($expediente['area_derivada_nombre']); ?></div>
                                            <?php if (!empty($expediente['usuario_derivado_nombre'])): ?>
                                                <div><strong>Usuario:</strong> <?php echo htmlspecialchars($expediente['usuario_derivado_nombre']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($expediente['fecha_derivacion'])): ?>
                                                <div class="info-derivacion">
                                                    <?php echo date('d/m/Y H:i', strtotime($expediente['fecha_derivacion'])); ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">No derivado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-buttons">
                                    <?php if (!empty($expediente['archivo_ruta'])): ?>
                                        <a href="<?php echo $expediente['archivo_ruta']; ?>" target="_blank" class="btn btn-sm btn-primary" title="Ver documento">
                                            <i class="nav icon material-symbols-rounded">docs</i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" title="Sin documento" disabled>
                                            <i class="nav icon material-symbols-rounded">unknown_document</i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Solo mostrar derivar si es admin o si el oficio está asignado al usuario -->
                                    <?php if ($tipo_usuario === 'Administrador' || $expediente['usuario_derivado_id'] == $_SESSION['id']): ?>
                                        <button class="btn btn-sm btn-warning" title="Derivar documento" onclick="abrirModalDerivacion(<?php echo $expediente['id']; ?>, '<?php echo htmlspecialchars($expediente['respuesta'] ?? ''); ?>')">
                                            <i class="nav icon material-symbols-rounded">reply_all</i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Solo mostrar responder si el oficio está asignado al usuario actual -->
                                    <?php if ($expediente['usuario_derivado_id'] == $_SESSION['id'] || $tipo_usuario === 'Administrador'): ?>
                                        <a href="index.php?action=responderoficio&id=<?php echo $expediente['id']; ?>" class="btn btn-sm btn-success" title="Responder documento">
                                            <i class="nav icon material-symbols-rounded">reply</i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($tipo_usuario === 'Administrador'): ?>
                                        <button class="btn btn-sm btn-danger" title="Eliminar documento" onclick="confirmarEliminacion(<?php echo $expediente['id']; ?>)">
                                            <i class="nav icon material-symbols-rounded">delete</i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <div class="pagination-container">
                    <div class="pagination-info">
                        Mostrando <?php echo count($expedientes); ?> de <?php echo count($expedientes); ?> registros
                    </div>
                    <div class="pagination-controls">
                        <button class="pagination-btn" disabled>Anterior</button>
                        <button class="pagination-btn active">1</button>
                        <button class="pagination-btn" disabled>Siguiente</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Derivación -->
    <div class="modal fade" id="modalDerivacion" tabindex="-1" aria-labelledby="modalDerivacionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDerivacionLabel">Derivar Oficio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="" id="formDerivacion">
                    <input type="hidden" name="action" value="expedientes">
                    <div class="modal-body">
                        <input type="hidden" name="oficio_id" id="oficio_id">
                        <input type="hidden" name="derivar_oficio" value="1">
                        
                        <div class="mb-3">
                                <label for="area_derivada" class="form-label required">Área de Destino</label>                            <select class="form-select" id="area_derivada" name="area_derivada" required onchange="cargarUsuariosPorArea(this.value)">
                                <option value="">Seleccionar área</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?php echo $area['id']; ?>"><?php echo htmlspecialchars($area['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                        <label for="usuario_derivado" class="form-label required">Usuario de Destino</label>                            <select class="form-select" id="usuario_derivado" name="usuario_derivado" required disabled>
                                <option value="">Primero seleccione un área</option>
                            </select>
                            <div class="form-text">Debe seleccionar un usuario para derivar el oficio</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="respuesta" class="form-label">Respuesta/Comentario</label>
                            <textarea class="form-control" id="respuesta" name="respuesta" rows="4" placeholder="Ingrese una respuesta o comentario sobre este oficio"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Derivar Oficio</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Validación del formulario de derivación
        document.getElementById('formDerivacion').addEventListener('submit', function(e) {
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

        function abrirModalDerivacion(id, respuesta) {
            $('#oficio_id').val(id);
            $('#respuesta').val(respuesta);
            $('#area_derivada').val('');
            $('#usuario_derivado').html('<option value="">Seleccionar usuario</option>');
            $('#usuario_derivado').prop('disabled', true);
            $('#usuario_derivado').prop('required', true);
            $('#modalDerivacion').modal('show');
        }

        function confirmarEliminacion(id) {
            if (confirm('¿Está seguro de eliminar este oficio? Esta acción no se puede deshacer.')) {
                window.location.href = 'index.php?action=expedientes&eliminar=' + id;
            }
        }

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
    </script>

<script src="../oficialiadepartes/js/navbar.js"></script>
</body>
</html>