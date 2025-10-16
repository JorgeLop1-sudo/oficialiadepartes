<?php
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../models/Oficio.php';
require_once __DIR__ . '/../models/OficioUser.php';
require_once __DIR__ . '/../config/database.php';


class AreasAdminController {

    public function areas() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] !== 'Administrador') {
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
        $areaModel = new Area($conn);
        $mensaje = "";

        // Procesar formularios
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['crear_area'])) {
                $mensaje = $areaModel->crear($_POST['nombre'], $_POST['descripcion']);
                header("Location: index.php?action=areasadmin&mensaje=" . urlencode($mensaje));
                exit();
            }

            if (isset($_POST['editar_area'])) {
                $mensaje = $areaModel->actualizar($_POST['id'], $_POST['nombre'], $_POST['descripcion'], $_POST['nombre_anterior']);
                header("Location: index.php?action=areasadmin&mensaje=" . urlencode($mensaje));
                exit();
            }
        }

        // Eliminar área
        if (isset($_GET['eliminar'])) {
            $mensaje = $areaModel->eliminar($_GET['eliminar']);
            header("Location: index.php?action=areasadmin&mensaje=" . urlencode($mensaje));
            exit();
        }

        // Área a editar
        $area_editar = null;
        if (isset($_GET['editar'])) {
            $area_editar = $areaModel->obtenerPorId($_GET['editar']);
        }

        $areas = $areaModel->obtenerTodas();

        include __DIR__ . '/../views/areasadmin.php';
    }

}
?>