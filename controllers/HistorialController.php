<?php
require_once __DIR__ . '/../models/Expediente.php';
require_once __DIR__ . '/../config/database.php';

class HistorialController {
    public function historial() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $database = new Database();
        $conn = $database->connect();
        $expedienteModel = new Expediente($conn);

        $historial = [];
        $oficio_id = null;
        $info_oficio = null;

        // Verificar si se está solicitando el historial de un oficio específico
        if (isset($_GET['oficio_id'])) {
            $oficio_id = intval($_GET['oficio_id']);
            $info_oficio = $expedienteModel->obtenerPorId($oficio_id);
            $historial = $expedienteModel->obtenerHistorialDerivaciones($oficio_id);
        }

        // Pasar variables a la vista
        $view_data = compact('historial', 'oficio_id', 'info_oficio');
        extract($view_data);

        include __DIR__ . '/../views/historial.php';
    }
}
?>