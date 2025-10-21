<?php
require_once __DIR__ . '/../models/Expediente.php';
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Oficio.php';
require_once __DIR__ . '/../models/OficioUser.php';
require_once __DIR__ . '/../config/database.php';

class ResponderOficioController {
     
    public function responder() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Verificar si el usuario está logueado
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $database = new Database();
        $conn = $database->connect();
        $expedienteModel = new Expediente($conn);

        // Obtener el ID del oficio a responder
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: index.php?action=expedientes");
            exit();
        }

        $oficio_id = intval($_GET['id']);
        $oficio = $expedienteModel->obtenerPorId($oficio_id);

        if (!$oficio) {
            header("Location: index.php?action=expedientes");
            exit();
        }

        // Obtener historial de derivaciones
        $historial_derivaciones = $expedienteModel->obtenerHistorialDerivaciones($oficio_id);

        // Verificar permisos (solo el usuario asignado o admin puede responder)
        $puede_responder = $this->verificarPermisos($oficio, $_SESSION);
        
        if (!$puede_responder) {
            header("Location: index.php?action=expedientes");
            exit();
        }

        $mensaje_error = "";
        
        // Procesar respuesta del oficio
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['completar']) || isset($_POST['denegar'])) {
                $respuesta = trim($_POST['respuesta']);
                $nuevo_estado = isset($_POST['completar']) ? 'completado' : 'denegado';
                
                $resultado = $expedienteModel->actualizarRespuesta($oficio_id, $respuesta, $nuevo_estado);

                if ($resultado['success']) {
                    header("Location: index.php?action=expedientes&mensaje=" . urlencode($resultado['mensaje']));
                    exit();
                } else {
                    $mensaje_error = $resultado['mensaje'];
                }
            }
        }

        // Pasar variables a la vista
        $view_data = compact('oficio', 'historial_derivaciones', 'mensaje_error');
        extract($view_data);

        include __DIR__ . '/../views/responder.php';
    }

    private function verificarPermisos($oficio, $session) {
        if ($session['tipo_usuario'] === 'Administrador') {
            return true;
        }

        // Para usuarios regulares, verificar si es el usuario asignado
        $user_id = $session['id'] ?? $session['user_id'] ?? $session['usuario_id'] ?? null;
        
        if ($user_id && $oficio['usuario_derivado_id'] == $user_id) {
            return true;
        }

        return false;
    }
}
?>