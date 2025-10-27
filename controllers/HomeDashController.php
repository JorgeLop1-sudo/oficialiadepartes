<?php

require_once __DIR__ . '/../models/Oficio.php';
require_once __DIR__ . '/../models/OficioUser.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/NotificationHelper.php';

class HomeDashController {
    
    public function dash() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['id'])) {
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

        
        $notifications = $this->setupNotifications($conn);
        
        if ($notifications) {
            $estadisticas = $notifications['estadisticas'];
            $actividad_reciente = $notifications['actividad_reciente'];
        } else {
            
            $estadisticas = ['pendientes' => 0, 'en_tramite' => 0, 'atendidos' => 0, 'denegados' => 0];
            $actividad_reciente = [];
        }

        include __DIR__ . '/../views/homedash.php';
    }
    
    protected function setupNotifications($conn) {
        if ($_SESSION['tipo_usuario'] === 'Administrador') {
            require_once __DIR__ . '/../models/Oficio.php';
            $oficioModel = new Oficio($conn);
            $estadisticas = $oficioModel->getEstadisticas();
            $actividad_reciente = $oficioModel->getActividadReciente();
            
            mysqli_close($conn);
            
            return [
                'estadisticas' => $estadisticas,
                'actividad_reciente' => $actividad_reciente
            ];
            
        } elseif ($_SESSION['tipo_usuario'] === 'Usuario') {
            $usuario_id = $this->getUsuarioId($conn, $_SESSION['usuario']);
            
            if (!$usuario_id) {
                header("Location: index.php?action=login");
                exit();
            }
            
            $_SESSION['id'] = $usuario_id;
            
            require_once __DIR__ . '/../models/OficioUser.php';
            $oficioUserModel = new OficioUser($conn);
            $estadisticas = $oficioUserModel->getEstadisticas($usuario_id);
            $actividad_reciente = $oficioUserModel->getActividadReciente($usuario_id);
            
            mysqli_close($conn);
            
            return [
                'estadisticas' => $estadisticas,
                'actividad_reciente' => $actividad_reciente
            ];
        }
        
        return null;
    }
    
    protected function getUsuarioId($conn, $usuario) {
        $sql = "SELECT id FROM login WHERE usuario = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $usuario);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['id'];
        }
        return false;
    }

    protected function formatFecha($fecha) {
        if (empty($fecha)) return 'Sin fecha';
        
        $fecha_obj = new DateTime($fecha);
        $hoy = new DateTime();
        $ayer = new DateTime('yesterday');
        
        if ($fecha_obj->format('Y-m-d') === $hoy->format('Y-m-d')) {
            return 'Hoy, ' . $fecha_obj->format('H:i');
        } elseif ($fecha_obj->format('Y-m-d') === $ayer->format('Y-m-d')) {
            return 'Ayer, ' . $fecha_obj->format('H:i');
        } else {
            return $fecha_obj->format('d/m/Y H:i');
        }
    }

    protected function getActivityIcon($estado) {
        switch ($estado) {
            case 'pendiente': return 'fas fa-clock text-warning';
            case 'tramite': return 'fas fa-tasks text-primary';
            case 'completado': return 'fas fa-check-circle text-success';
            case 'denegado': return 'fas fa-times-circle text-danger';
            default: return 'fas fa-file-alt text-info';
        }
    }
}
?>