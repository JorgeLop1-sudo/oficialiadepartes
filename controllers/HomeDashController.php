<?php

require_once __DIR__ . '/../models/Oficio.php';
require_once __DIR__ . '/../models/OficioUser.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/NotificationHelper.php';

class HomeDashController {
    use NotificationHelper;
    
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
    
}
?>