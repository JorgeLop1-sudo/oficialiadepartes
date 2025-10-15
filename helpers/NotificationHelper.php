<?php

trait NotificationHelper {
    
    protected function setupNotifications($conn) {
        if ($_SESSION['tipo_usuario'] === 'Administrador') {
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
}
?>