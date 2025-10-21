<?php
require_once __DIR__ . '/../models/Expediente.php';
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Oficio.php';
require_once __DIR__ . '/../models/OficioUser.php';
require_once __DIR__ . '/../config/database.php';

class ExpedientesController {
    public function expedientes() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['id'])) {
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

        $database = new Database();
        $conn = $database->connect();
        $expedienteModel = new Expediente($conn);
        $areaModel = new Area($conn);
        $userModel = new User($conn);

        $mensaje = "";
        $error = "";
        $filtros = [];

        // Obtener información del usuario actual
        $usuario_id = $_SESSION['id'] ?? null;
        $tipo_usuario = $_SESSION['tipo_usuario'] ?? 'Usuario';

        // Manejar solicitud AJAX para usuarios por área
        if (isset($_GET['ajax']) && $_GET['ajax'] == 'usuarios_por_area' && isset($_GET['area_id'])) {
            $area_id = intval($_GET['area_id']);
            $usuarios_filtrados = $expedienteModel->obtenerUsuariosPorArea($area_id);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'usuarios' => $usuarios_filtrados]);
            exit();
        }

        // Obtener áreas y usuarios para los modales
        $areas = $areaModel->obtenerTodasActivas();
        $usuarios = $userModel->obtenerTodos();

        // Procesar filtros de búsqueda
        if (isset($_GET['numero'])) {
            $filtros['numero'] = $_GET['numero'];
        }
        if (isset($_GET['estado'])) {
            $filtros['estado'] = $_GET['estado'];
        }

        // Obtener expedientes con filtros y según el tipo de usuario
        $expedientes = $expedienteModel->obtenerTodos($filtros, $usuario_id, $tipo_usuario);

        // Procesar eliminación de oficio (solo admin)
        if (isset($_GET['eliminar']) && $tipo_usuario === 'Administrador') {
            $mensaje = $expedienteModel->eliminar($_GET['eliminar']);
            header("Location: index.php?action=expedientes&mensaje=" . urlencode($mensaje));
            exit();
        }

        // Procesar derivación de oficio
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['derivar_oficio'])) {
            $mensaje = $expedienteModel->derivar(
                $_POST['oficio_id'],
                $_POST['area_derivada'],
                $_POST['usuario_derivado'],
                $_POST['respuesta']
            );
            
            if (strpos($mensaje, 'Error') === false) {
                header("Location: index.php?action=expedientes&mensaje=" . urlencode($mensaje));
                exit();
            } else {
                $error = $mensaje;
            }
        }
        
        // Pasar variables a la vista
        $view_data = compact('expedientes', 'areas', 'usuarios', 'mensaje', 'error', 'filtros', 'tipo_usuario');
        extract($view_data);

        include __DIR__ . '/../views/expedientes.php';
    }
}
?>