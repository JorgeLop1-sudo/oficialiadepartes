<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../models/Oficio.php';
require_once __DIR__ . '/../models/OficioUser.php';
require_once __DIR__ . '/../config/database.php';

class UsersAdminController {

    public function users() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] !== 'Administrador') {
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");
            header("Location: index.php?action=login");
            exit();
        }

        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        $database = new Database();
        $conn = $database->connect();
        $userModel = new User($conn);
        $areaModel = new Area($conn);

        $mensaje = "";
        $error = "";
        $form_data = [];
        $mostrar_modal_nuevo = false;
        $mostrar_modal_editar = false;
        $error_modal_nuevo = "";
        $error_modal_editar = "";
        $usuario_editar = null; // Asegurar que esté inicializada

        // Procesar formularios
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['crear_usuario'])) {
                if ($_POST['password'] !== $_POST['confirm_password']) {
                    $error_modal_nuevo = "Error: Las contraseñas no coinciden";
                    $form_data = $_POST;
                    $mostrar_modal_nuevo = true;
                } else {
                    $resultado = $userModel->crear(
                        $_POST['usuario'],
                        $_POST['password'],
                        $_POST['nombre'],
                        $_POST['tipo_usuario'],
                        $_POST['area'],
                        $_POST['email']
                    );
                    
                    if (strpos($resultado, 'Error:') === 0) {
                        $error_modal_nuevo = $resultado;
                        $form_data = $_POST;
                        $mostrar_modal_nuevo = true;
                    } else {
                        header("Location: index.php?action=usersadmin&mensaje=" . urlencode($resultado));
                        exit();
                    }
                }
            }

            if (isset($_POST['editar_usuario'])) {
                $password = !empty($_POST['password']) ? $_POST['password'] : null;
                if ($password && $_POST['password'] !== $_POST['confirm_password']) {
                    $error_modal_editar = "Error: Las contraseñas no coinciden";
                    $mostrar_modal_editar = true;
                    // Guardar los datos del formulario para mostrarlos en el modal
                    $form_data_editar = $_POST;
                } else {
                    $resultado = $userModel->actualizar(
                        $_POST['id'],
                        $_POST['usuario'],
                        $_POST['nombre'],
                        $_POST['tipo_usuario'],
                        $_POST['area'],
                        $_POST['email'],
                        $password
                    );
                    
                    if (strpos($resultado, 'Error:') === 0) {
                        $error_modal_editar = $resultado;
                        $mostrar_modal_editar = true;
                        // Guardar los datos del formulario para mostrarlos en el modal
                        $form_data_editar = $_POST;
                    } else {
                        header("Location: index.php?action=usersadmin&mensaje=" . urlencode($resultado));
                        exit();
                    }
                }
            }
        }

        // Procesar eliminación
        if (isset($_GET['eliminar'])) {
            // Obtener el ID del usuario actual de diferentes formas posibles
            $usuario_actual_id = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? $_SESSION['user_id'] ?? null;
            
            if ($_GET['eliminar'] != $usuario_actual_id) {
                $resultado = $userModel->eliminar($_GET['eliminar']);
                if (strpos($resultado, 'Error:') === 0) {
                    $error = $resultado;
                } else {
                    header("Location: index.php?action=usersadmin&mensaje=" . urlencode($resultado));
                    exit();
                }
            } else {
                $error = "No puedes eliminarte a ti mismo";
            }
        }

        // Obtener usuario para editar (solo si viene por GET y no hay error de POST)
        if (isset($_GET['editar']) && !$mostrar_modal_editar) {
            $usuario_editar = $userModel->obtenerPorId($_GET['editar']);
            $mostrar_modal_editar = true;
        }

        // Si hay un error en edición por POST, cargar los datos del formulario
        if ($mostrar_modal_editar && isset($form_data_editar)) {
            $usuario_editar = $form_data_editar;
            // Asegurarse de que el ID esté presente
            if (!isset($usuario_editar['id']) && isset($_POST['id'])) {
                $usuario_editar['id'] = $_POST['id'];
            }
        }

        // Obtener datos
        $usuarios = $userModel->obtenerTodos();
        $areas_disponibles = $areaModel->obtenerTodasActivas();

        // Pasar variables a la vista
        $view_data = compact('usuarios', 'areas_disponibles', 'mensaje', 'error', 
                           'form_data', 'usuario_editar', 'mostrar_modal_nuevo', 
                           'mostrar_modal_editar', 'error_modal_nuevo', 'error_modal_editar');
        
        extract($view_data);

        include __DIR__ . '/../views/usersadmin.php';
    }

}
?>