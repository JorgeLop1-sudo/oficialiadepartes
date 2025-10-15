<?php
class OficioUser {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getEstadisticas($usuario_id) {
        $estadisticas = [
            'pendientes' => 0,
            'en_tramite' => 0,
            'atendidos' => 0,
            'denegados' => 0
        ];

        $sql = "SELECT estado, COUNT(*) as total 
                FROM oficios 
                WHERE activo = 1 AND usuario_derivado_id = ? 
                GROUP BY estado";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $usuario_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                switch ($row['estado']) {
                    case 'pendiente': $estadisticas['pendientes'] = $row['total']; break;
                    case 'tramite': $estadisticas['en_tramite'] = $row['total']; break;
                    case 'completado': $estadisticas['atendidos'] = $row['total']; break;
                    case 'denegado': $estadisticas['denegados'] = $row['total']; break;
                }
            }
        }
        return $estadisticas;
    }

    public function getActividadReciente($usuario_id) {
        $actividad = [];
        $sql = "
            SELECT o.*, a.nombre as area_nombre, l.nombre as usuario_nombre, 
                   ad.nombre as area_derivada_nombre, ld.nombre as usuario_derivado_nombre
            FROM oficios o 
            LEFT JOIN areas a ON o.area_id = a.id 
            LEFT JOIN login l ON o.usuario_id = l.id 
            LEFT JOIN areas ad ON o.area_derivada_id = ad.id 
            LEFT JOIN login ld ON o.usuario_derivado_id = ld.id 
            WHERE o.activo = 1 AND o.usuario_derivado_id = ?
            ORDER BY o.fecha_derivacion DESC 
            LIMIT 10
        ";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $usuario_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $actividad[] = $row;
            }
        }
        return $actividad;
    }
}
?>