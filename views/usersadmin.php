<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS-OP - Gestión de Usuarios</title>
    <link rel="icon" type="image/png" href="/mvc_oficialiapartes/css/image/icono3.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.2/css/buttons.bootstrap5.min.css">

    <link rel="stylesheet" href="/oficialiadepartes/css/globals/style-body.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/buttonnew.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/style-container.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/buttonaction.css">

    <style>
        
    </style>

</head>
<body>
    <!-- Incluir el sidebar -->
    <?php include 'partials/sidebar.php'; ?>
    
    <div class="main-content">
        
    <?php include 'partials/header.php'; ?>

        <!-- Mostrar mensajes de éxito -->
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['mensaje']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Mostrar mensajes de error -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        
        <div class="page-title">
            <h3>Gestión de Usuarios</h3>
            <button class="btn btn-new" data-bs-toggle="modal" data-bs-target="#nuevoUsuarioModal">
                <i class="fas fa-plus"></i> Nuevo Usuario
            </button>
        </div>

        
        <div class="users-container">
            <?php if (empty($usuarios)): ?>
                <div class="alert alert-warning">
                    No hay usuarios registrados en el sistema.
                </div>
            <?php else: ?>
                <?php foreach ($usuarios as $user): ?>
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-name"><?php echo htmlspecialchars($user['usuario']); ?></div>
                            <div class="user-id">ID: <?php echo $user['id']; ?></div>
                        </div>
                        <div class="user-details">
                            <div class="user-detail">
                                <div class="user-detail-icon">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div class="user-detail-text">
                                    <strong>Tipo:</strong> 
                                    <span class="badge bg-<?php echo $user['tipo_usuario'] == 'Administrador' ? 'warning' : ($user['tipo_usuario'] == 'Usuario' ? 'info' : 'success'); ?>">
                                        <?php echo htmlspecialchars($user['tipo_usuario']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="user-detail">
                                <div class="user-detail-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="user-detail-text">
                                    <strong>Nombre:</strong> <?php echo htmlspecialchars($user['nombre']); ?>
                                </div>
                            </div>
                            <div class="user-detail">
                                <div class="user-detail-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="user-detail-text">
                                    <strong>Área:</strong> 
                                    <?php 
                                    if (!empty($user['area_nombre'])) {
                                        echo htmlspecialchars($user['area_nombre']);
                                    } else {
                                        echo '<span class="text-muted">Sin área asignada</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php if (!empty($user['email'])): ?>
                            <div class="user-detail">
                                <div class="user-detail-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="user-detail-text">
                                    <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="sectionbutton">
                            <a href="index.php?action=usersadmin&editar=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary btn-action">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <button class="btn btn-sm btn-danger btn-action" 
                                    onclick="confirmarEliminacion(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['usuario']); ?>')">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para Nuevo Usuario -->
    <div class="modal fade" id="nuevoUsuarioModal" tabindex="-1" aria-labelledby="nuevoUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevoUsuarioModalLabel">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="" id="nuevoUsuarioForm">
                <div class="modal-body">
                    <!-- Mostrar mensajes de error solo para este modal -->
                    <?php if (!empty($error_modal_nuevo)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_modal_nuevo); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario *</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" 
                            value="<?php echo isset($form_data['usuario']) ? htmlspecialchars($form_data['usuario']) : ''; ?>" 
                            pattern="[a-zA-Z0-9]{6,}" 
                            title="El usuario debe tener al menos 6 caracteres y solo puede contener letras y números" 
                            required>
                        <div id="usernameError" class="username-error">
                            El usuario debe tener al menos 6 caracteres y solo puede contener letras y números
                        </div>
                    </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="input-group-text" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button class="input-group-text password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatchError" class="password-match-error">
                                Las contraseñas no coinciden
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?php echo isset($form_data['nombre']) ? htmlspecialchars($form_data['nombre']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_usuario" class="form-label">Tipo de Usuario *</label>
                            <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                                <option value="" selected disabled>Seleccionar tipo</option>
                                <option value="Administrador" <?php echo (isset($form_data['tipo_usuario']) && $form_data['tipo_usuario'] == 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                                <option value="Usuario" <?php echo (isset($form_data['tipo_usuario']) && $form_data['tipo_usuario'] == 'Usuario') ? 'selected' : ''; ?>>Usuario</option>
                                <option value="Guardia" <?php echo (isset($form_data['tipo_usuario']) && $form_data['tipo_usuario'] == 'Guardia') ? 'selected' : ''; ?>>Guardia</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="area" class="form-label">Área *</label>
                            <select class="form-select" id="area" name="area" required>
                                <option value="" selected disabled>Seleccionar área</option>
                                <?php if (isset($areas_disponibles) && !empty($areas_disponibles)): ?>
                                    <?php foreach ($areas_disponibles as $area): ?>
                                        <option value="<?php echo $area['id']; ?>" 
                                            <?php echo (isset($form_data['area']) && $form_data['area'] == $area['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($area['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay áreas disponibles</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="crear_usuario" class="btn btn-success" id="submitButton">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Modal para Editar Usuario -->
<?php if ($usuario_editar): ?>
    <div class="modal fade" id="editarUsuarioModal" tabindex="-1" aria-labelledby="editarUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarUsuarioModalLabel">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="" id="editarUsuarioForm">
                    <input type="hidden" name="id" value="<?php echo $usuario_editar['id']; ?>">
                    <div class="modal-body">
                        <!-- Mostrar mensajes de error solo para este modal -->
                        <?php if (!empty($error_modal_editar)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($error_modal_editar); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="usuario_edit" class="form-label">Usuario *</label>
                            <input type="text" class="form-control" id="usuario_edit" name="usuario" 
                                value="<?php echo htmlspecialchars($usuario_editar['usuario']); ?>" 
                                pattern="[a-zA-Z0-9]{6,}" 
                                title="El usuario debe tener al menos 6 caracteres y solo puede contener letras y números" 
                                required>
                            <div id="usernameErrorEdit" class="username-error" style="display: none;">
                                El usuario debe tener al menos 6 caracteres y solo puede contener letras y números
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password_edit" class="form-label">Nueva Contraseña (dejar vacío para no cambiar)</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password_edit" name="password">
                                <span class="input-group-text password-toggle" onclick="togglePassword('password_edit')">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password_edit" class="form-label">Confirmar Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password_edit" name="confirm_password">
                                <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password_edit')">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div id="passwordMatchErrorEdit" class="password-match-error" style="display: none;">
                                Las contraseñas no coinciden
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="nombre_edit" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre_edit" name="nombre" 
                                   value="<?php echo htmlspecialchars($usuario_editar['nombre']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_usuario_edit" class="form-label">Tipo de Usuario *</label>
                            <select class="form-select" id="tipo_usuario_edit" name="tipo_usuario" required>
                                <option value="Administrador" <?php echo ($usuario_editar['tipo_usuario'] ?? '') == 'Administrador' ? 'selected' : ''; ?>>Administrador</option>
                                <option value="Usuario" <?php echo ($usuario_editar['tipo_usuario'] ?? '') == 'Usuario' ? 'selected' : ''; ?>>Usuario</option>
                                <option value="Guardia" <?php echo ($usuario_editar['tipo_usuario'] ?? '') == 'Guardia' ? 'selected' : ''; ?>>Guardia</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="area_edit" class="form-label">Área *</label>
                            <select class="form-select" id="area_edit" name="area" required>
                                <option value="" disabled>Seleccionar área</option>
                                <?php if (isset($areas_disponibles) && !empty($areas_disponibles)): ?>
                                    <?php foreach ($areas_disponibles as $area): ?>
                                        <option value="<?php echo $area['id']; ?>" 
                                            <?php echo (isset($usuario_editar['area']) && $usuario_editar['area'] == $area['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($area['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay áreas disponibles</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="email_edit" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email_edit" name="email" 
                                   value="<?php echo htmlspecialchars($usuario_editar['email'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="editar_usuario" class="btn btn-success" id="submitButtonEdit">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Función para validar formato de usuario
    function validateUsername(inputId, formType = 'nuevo') {
        const usernameField = document.getElementById(inputId);
        const username = usernameField.value;
        const errorElement = formType === 'nuevo' ? 'usernameError' : 'usernameErrorEdit';
        const errorEl = document.getElementById(errorElement);
        
        if (username.length > 0 && (username.length < 6 || !/^[a-zA-Z0-9]+$/.test(username))) {
            errorEl.style.display = 'block';
            usernameField.classList.add('is-invalid');
            return false;
        } else {
            errorEl.style.display = 'none';
            usernameField.classList.remove('is-invalid');
            return true;
        }
    }

    function confirmarEliminacion(id, usuario) {
        if (confirm(`¿Estás seguro de eliminar al usuario "${usuario}"? Los oficios relacionados se mantendrán en el sistema.`)) {
            window.location.href = `index.php?action=usersadmin&eliminar=${id}`;
        }
    }

    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.parentElement.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Función para validar que las contraseñas coincidan
    function validatePasswords(formType = 'nuevo') {
        const passwordField = formType === 'nuevo' ? 'password' : 'password_edit';
        const confirmField = formType === 'nuevo' ? 'confirm_password' : 'confirm_password_edit';
        const errorElement = formType === 'nuevo' ? 'passwordMatchError' : 'passwordMatchErrorEdit';
        const submitButton = formType === 'nuevo' ? 'submitButton' : 'submitButtonEdit';
        
        const password = document.getElementById(passwordField).value;
        const confirmPassword = document.getElementById(confirmField).value;
        const errorEl = document.getElementById(errorElement);
        const submitBtn = document.getElementById(submitButton);
        
        if (password && confirmPassword && password !== confirmPassword) {
            errorEl.style.display = 'block';
            submitBtn.disabled = true;
            return false;
        } else {
            errorEl.style.display = 'none';
            submitBtn.disabled = false;
            return true;
        }
    }

    // Agregar event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Event listeners para validación de NUEVO usuario
        document.getElementById('usuario')?.addEventListener('input', () => validateUsername('usuario', 'nuevo'));
        document.getElementById('password')?.addEventListener('input', () => validatePasswords('nuevo'));
        document.getElementById('confirm_password')?.addEventListener('input', () => validatePasswords('nuevo'));
        
        // Event listeners para validación de EDICIÓN de usuario
        document.getElementById('usuario_edit')?.addEventListener('input', () => validateUsername('usuario_edit', 'editar'));
        document.getElementById('password_edit')?.addEventListener('input', () => validatePasswords('editar'));
        document.getElementById('confirm_password_edit')?.addEventListener('input', () => validatePasswords('editar'));
        
        // Validar formularios antes de enviar - NUEVO USUARIO
        document.getElementById('nuevoUsuarioForm')?.addEventListener('submit', function(e) {
            if (!validatePasswords('nuevo') || !validateUsername('usuario', 'nuevo')) {
                e.preventDefault();
                alert('Por favor, corrige los errores en el formulario.');
            }
        });
        
        // Validar formularios antes de enviar - EDITAR USUARIO
        document.getElementById('editarUsuarioForm')?.addEventListener('submit', function(e) {
            if (!validatePasswords('editar') || !validateUsername('usuario_edit', 'editar')) {
                e.preventDefault();
                alert('Por favor, corrige los errores en el formulario.');
            }
        });
        
        // Mostrar modales automáticamente si es necesario - NUEVO USUARIO
        <?php if ($mostrar_modal_nuevo): ?>
            var modalNuevo = new bootstrap.Modal(document.getElementById('nuevoUsuarioModal'));
            modalNuevo.show();
            
            // Prevenir que el modal se cierre al hacer clic fuera de él cuando hay error
            if ('<?php echo !empty($error_modal_nuevo) ? 'true' : 'false'; ?>' === 'true') {
                const modalElement = document.getElementById('nuevoUsuarioModal');
                if (modalElement) {
                    modalElement.setAttribute('data-bs-backdrop', 'static');
                    modalElement.setAttribute('data-bs-keyboard', 'false');
                }
            }
        <?php endif; ?>
        
        // Mostrar modales automáticamente si es necesario - EDITAR USUARIO
        <?php if ($mostrar_modal_editar): ?>
            var modalEditar = new bootstrap.Modal(document.getElementById('editarUsuarioModal'));
            modalEditar.show();
            
            // Prevenir que el modal se cierre al hacer clic fuera de él cuando hay error
            if ('<?php echo !empty($error_modal_editar) ? 'true' : 'false'; ?>' === 'true') {
                const modalElement = document.getElementById('editarUsuarioModal');
                if (modalElement) {
                    modalElement.setAttribute('data-bs-backdrop', 'static');
                    modalElement.setAttribute('data-bs-keyboard', 'false');
                }
            }
        <?php endif; ?>
        
        // Limpiar parámetros de URL cuando se cierren los modales
        const modals = ['nuevoUsuarioModal', 'editarUsuarioModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.addEventListener('hidden.bs.modal', function () {
                    // Remover parámetros de edición/error de la URL sin recargar
                    const url = new URL(window.location);
                    url.searchParams.delete('editar');
                    window.history.replaceState({}, '', url);
                    
                    // Restaurar comportamiento normal del modal (por si se había cambiado)
                    modal.removeAttribute('data-bs-backdrop');
                    modal.removeAttribute('data-bs-keyboard');
                });
            }
        });

        // Limpiar errores cuando se abran los modales
        document.getElementById('nuevoUsuarioModal')?.addEventListener('show.bs.modal', function () {
            // Restaurar comportamiento normal del modal
            this.removeAttribute('data-bs-backdrop');
            this.removeAttribute('data-bs-keyboard');
        });

        document.getElementById('editarUsuarioModal')?.addEventListener('show.bs.modal', function () {
            // Restaurar comportamiento normal del modal
            this.removeAttribute('data-bs-backdrop');
            this.removeAttribute('data-bs-keyboard');
        });
    });

    // Función para cerrar modal y limpiar URL
    function cerrarModal() {
        const url = new URL(window.location);
        url.searchParams.delete('editar');
        window.history.replaceState({}, '', url);
        
        // Ocultar modal manualmente
        const modal = bootstrap.Modal.getInstance(document.getElementById('editarUsuarioModal'));
        if (modal) {
            modal.hide();
        }
    }

    // Función para resetear formulario cuando se cierra el modal de nuevo usuario
    function resetearFormularioNuevo() {
        document.getElementById('nuevoUsuarioForm')?.reset();
        // Limpiar mensajes de error
        document.getElementById('usernameError').style.display = 'none';
        document.getElementById('passwordMatchError').style.display = 'none';
        document.getElementById('usuario')?.classList.remove('is-invalid');
        document.getElementById('submitButton').disabled = false;
    }

    // Función para resetear formulario cuando se cierra el modal de editar usuario
    function resetearFormularioEditar() {
        // Limpiar mensajes de error
        document.getElementById('usernameErrorEdit').style.display = 'none';
        document.getElementById('passwordMatchErrorEdit').style.display = 'none';
        document.getElementById('usuario_edit')?.classList.remove('is-invalid');
        document.getElementById('submitButtonEdit').disabled = false;
    }
</script>

<script src="../oficialiadepartes/js/msg.js"></script>
<script src="../oficialiadepartes/js/navbar.js"></script>
    
</body>
</html>