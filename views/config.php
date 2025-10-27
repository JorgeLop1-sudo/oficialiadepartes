<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS-OP - Configuración</title>
    <link rel="icon" type="image/png" href="/oficialiadepartes/css/images/icono2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="/oficialiadepartes/css/globals/style-body.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/dashboard/styleconfig.css">
    <link rel="stylesheet" href="/oficialiadepartes/css/globals/buttonaction.css">
    
    <style>
        
    </style>
</head>
<body>
    <!-- Incluir el sidebar -->
    <?php include 'partials/sidebar.php'; ?>
    
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <?php include 'partials/header.php'; ?>

        <!-- Page Title -->
        <h3 class="page-title">Configuración del Sistema</h3>

        <div class="config-sections-container">

            <!-- User Configuration Section -->
            <div class="config-section">
                <h4 class="mb-4"><i class="fas fa-user me-2"></i>Configuración de Usuario</h4>
                
                <div class="alert user-feedback" id="userFeedback"></div>
                
                <form id="userDataForm" method="POST">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="user_id" value="<?php echo $usuario_actual['id']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre" class="required-field">Nombre completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                    value="<?php echo htmlspecialchars($usuario_actual['nombre']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="usuario" class="required-field">Nombre de usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" 
                                    value="<?php echo htmlspecialchars($usuario_actual['usuario']); ?>" required>
                                <small class="form-text">Este nombre será usado para iniciar sesión</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="area">Área asignada</label>
                                    <input type="text" class="form-control" id="area" name="area" disabled
                                        value="<?php echo htmlspecialchars($usuario_actual['area_nombre'] ?? 'Sin área asignada'); ?>">
                                <small class="form-text">El área no puede ser modificada desde aquí</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="required-field">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                    value="<?php echo htmlspecialchars($usuario_actual['email']); ?>" required>
                            </div>
                            <div class="sectionbutton">
                                <button type="submit" class="btn-action">
                                    <i class="fas fa-save me-1"></i> Guardar cambios
                                </button>
                            </div>
                        </div>     
                    </div>
                    
                </form>
            </div>

            <!-- Password Change Section -->
            <div class="config-section">
                <h4 class="mb-4"><i class="fas fa-lock me-2"></i>Cambiar Contraseña</h4>
                
                <div class="alert password-feedback" id="passwordFeedback"></div>
                
                <form id="passwordForm" method="POST">
                    <input type="hidden" name="action" value="update_password">
                    <input type="hidden" name="user_id" value="<?php echo $usuario_actual['id']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="currentPassword" class="required-field">Contraseña actual</label>
                                <input type="password" class="form-control" id="currentPassword" data-toggle="currentPassword" name="currentPassword" required>
                                <button type="button" class="toggle-password" data-target="currentPassword" id="togglePassword1">
                                <i class="fas fa-eye"></i>
                            
                            </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="newPassword" class="required-field">Nueva contraseña</label>
                                <input type="password" class="form-control" id="newPassword" data-toggle="newPassword" name="newPassword" required>
                                <button type="button" class="toggle-password" data-target="newPassword" id="togglePassword2">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-text">Mínimo 8 caracteres, incluir mayúsculas, minúsculas y números</small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confirmPassword" class="required-field">Confirmar nueva contraseña</label>
                                <input type="password" class="form-control" id="confirmPassword" data-toggle="confirmPassword" name="confirmPassword" required>
                                <button type="button" class="toggle-password" data-target="confirmPassword" id="togglePassword3">
                                <i class="fas fa-eye"></i>
                            </button>
                            </div>
                            <div class="sectionbutton">
                                <button type="submit" class="btn-action">
                                    <i class="fas fa-key me-1"></i> Cambiar contraseña
                                </button>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>

        </div>

    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {

        const tipoUsuario = "<?php echo $_SESSION['tipo_usuario']; ?>";

            //if (tipoUsuario === 'admin') {
                // Validación del formulario de datos de usuario
                $('#userDataForm').on('submit', function(e) {
                    e.preventDefault();
                    
                    console.log('Enviando formulario de usuario...');
                    
                    const formData = {
                        nombre: $('#nombre').val(),
                        usuario: $('#usuario').val(),
                        email: $('#email').val()
                    };
                    
                    // Validaciones básicas de frontend
                    if (!formData.nombre || !formData.usuario || !formData.email) {
                        showUserFeedback('Por favor, complete todos los campos obligatorios.', 'danger');
                        return;
                    }
                    
                    if (!isValidEmail(formData.email)) {
                        showUserFeedback('Por favor, ingrese un correo electrónico válido.', 'danger');
                        return;
                    }

                    $.ajax({
                    url: 'index.php?action=config',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        console.log('Respuesta del servidor:', response);
                        
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                showUserFeedback(result.message, 'success');
                                // Actualizar información en la barra superior si el nombre cambió
                                if (formData.nombre !== '<?php echo $usuario_actual['nombre']; ?>') {
                                    setTimeout(() => {
                                        location.reload(); // Recargar para ver cambios
                                    }, 1500);
                                }
                            } else {
                                showUserFeedback(result.message, 'danger');
                            }
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                            console.error('Response received:', response);
                            showUserFeedback('Error al procesar la respuesta del servidor. Respuesta: ' + response.substring(0, 100), 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error AJAX:', error);
                        showUserFeedback('Error de conexión con el servidor: ' + error, 'danger');
                    }
                });

            });

            // Validación del formulario de contraseña
            $('#passwordForm').on('submit', function(e) {
                e.preventDefault();
                
                const currentPassword = $('#currentPassword').val();
                const newPassword = $('#newPassword').val();
                const confirmPassword = $('#confirmPassword').val();
                
                // Validaciones
                if (!currentPassword || !newPassword || !confirmPassword) {
                    showPasswordFeedback('Por favor, complete todos los campos.', 'danger');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    showPasswordFeedback('Las contraseñas nuevas no coinciden.', 'danger');
                    return;
                }
                
                if (!isPasswordStrong(newPassword)) {
                    showPasswordFeedback('La nueva contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números.', 'danger');
                    return;
                }
                
                // Enviar formulario mediante AJAX
                $.ajax({
                    url: 'index.php?action=config',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                showPasswordFeedback(result.message, 'success');
                                $('#passwordForm')[0].reset();
                            } else {
                                showPasswordFeedback(result.message, 'danger');
                            }
                        } catch (e) {
                            showPasswordFeedback('Error al procesar la respuesta del servidor.', 'danger');
                        }
                    },
                    error: function() {
                        showPasswordFeedback('Error de conexión con el servidor.', 'danger');
                    }
                });
            });

            function isValidEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }
            
            function isPasswordStrong(password) {
                // Mínimo 8 caracteres, al menos una mayúscula, una minúscula y un número
                const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
                return re.test(password);
            }
            
            function showUserFeedback(message, type) {
                const feedback = $('#userFeedback');
                feedback.removeClass('alert-success alert-danger alert-warning');
                feedback.addClass(`alert-${type}`);
                feedback.html(message);
                feedback.show();
                
                // Ocultar después de 5 segundos
                setTimeout(() => feedback.hide(), 5000);
            }
            
            function showPasswordFeedback(message, type) {
                const feedback = $('#passwordFeedback');
                feedback.removeClass('alert-success alert-danger alert-warning');
                feedback.addClass(`alert-${type}`);
                feedback.html(message);
                feedback.show();
                
                // Ocultar después de 5 segundos
                setTimeout(() => feedback.hide(), 5000);
            }
        });

    </script>
    <script src="../oficialiadepartes/js/navbar.js"></script>
    <script src="../oficialiadepartes/js/viewpass.js"></script>
</body>
</html>