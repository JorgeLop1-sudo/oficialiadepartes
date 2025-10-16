<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../models/Oficio.php';
require_once __DIR__ . '/../models/OficioUser.php';
require_once __DIR__ . '/../config/database.php';


class ConfigController {
    
    public function config() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['usuario'])) {
            // Headers para evitar cache en páginas protegidas
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");
            header("Location: index.php?action=login");
            exit();
        }

        // Headers para evitar cache en páginas protegidas
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        // PROCESAR FORMULARIOS PRIMERO (para AJAX)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $this->procesarFormulario();
            exit(); // IMPORTANTE: Salir después de responder a AJAX
        }

        // SI ES UNA PETICIÓN GET, MOSTRAR LA VISTA NORMAL
        $database = new Database();
        $conn = $database->connect();
        $userModel = new User($conn);

        // Obtener datos del usuario actual
        $usuario_id = $_SESSION['id'] ?? 0;
        $usuario_actual = $userModel->obtenerPorId($usuario_id);

        if (!$usuario_actual) {
            header("Location: index.php?action=login");
            exit();
        }

        // Obtener información del área del usuario
        $area_nombre = '';
        if (!empty($usuario_actual['area_id'])) {
            $areaModel = new Area($conn);
            $area = $areaModel->obtenerPorId($usuario_actual['area_id']);
            $area_nombre = $area['nombre'] ?? '';
        }

        $usuario_actual['area_nombre'] = $area_nombre;
        

        // Pasar variables a la vista
        $view_data = compact('usuario_actual');
        extract($view_data);

        include __DIR__ . '/../views/config.php';
    }

    private function procesarFormulario() {
        $database = new Database();
        $conn = $database->connect();
        $userModel = new User($conn);
        
        $usuario_id = $_SESSION['id'] ?? 0;

        if ($_POST['action'] === 'update_user') {
            $this->actualizarDatosUsuario($userModel, $usuario_id);
        } elseif ($_POST['action'] === 'update_password') {
            $this->actualizarPassword($userModel, $usuario_id);
        }
        
        mysqli_close($conn);
    }

    private function actualizarDatosUsuario($userModel, $usuario_id) {
        $nombre = $_POST['nombre'] ?? '';
        $usuario = $_POST['usuario'] ?? '';
        $email = $_POST['email'] ?? '';
    
        // Validaciones básicas
        if (empty($nombre) || empty($usuario) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Por favor, complete todos los campos obligatorios.']);
            return;
        }
    
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Por favor, ingrese un correo electrónico válido.']);
            return;
        }
    
        // Obtener datos actuales del usuario para preservar el area_id correcto
        $usuario_actual = $userModel->obtenerPorId($usuario_id);
        if (!$usuario_actual) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
            return;
        }
    
        // Usar el area_id actual del usuario en lugar del de la sesión
        $area_id = $usuario_actual['area_id'];
    
        // Actualizar datos del usuario
        $resultado = $userModel->actualizar(
            $usuario_id,
            $usuario,
            $nombre,
            $_SESSION['tipo_usuario'],
            $area_id, // Usar el area_id actual del usuario
            $email
        );
    
        if (strpos($resultado, 'Error') === false) {
            // Actualizar datos en la sesión
            $_SESSION['nombre'] = $nombre;
            $_SESSION['usuario'] = $usuario;
            
            echo json_encode(['success' => true, 'message' => 'Datos actualizados correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => $resultado]);
        }
    }

    private function actualizarPassword($userModel, $usuario_id) {
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        // Validaciones
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'Por favor, complete todos los campos.']);
            return;
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas nuevas no coinciden.']);
            return;
        }

        if (!$this->validarFortalezaPassword($newPassword)) {
            echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números.']);
            return;
        }

        // Verificar contraseña actual
        $usuario = $userModel->obtenerPorId($usuario_id);
        if (!$usuario || !password_verify($currentPassword, $usuario['password'])) {
            echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta.']);
            return;
        }

        // Actualizar contraseña
        $resultado = $userModel->actualizar(
            $usuario_id,
            $usuario['usuario'],
            $usuario['nombre'],
            $usuario['tipo_usuario'],
            $usuario['area_id'],
            $usuario['email'],
            $newPassword
        );

        if (strpos($resultado, 'Error') === false) {
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => $resultado]);
        }
    }

    private function validarFortalezaPassword($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
    }
}
?>