<?php
class Oficio {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getEstadisticas() {
        $estadisticas = [
            'pendientes' => 0,
            'en_tramite' => 0,
            'atendidos' => 0,
            'denegados' => 0
        ];

        $sql = "SELECT estado, COUNT(*) as total 
                FROM oficios 
                WHERE activo = 1 
                GROUP BY estado";
        $result = mysqli_query($this->conn, $sql);

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

    public function getActividadReciente() {
        $actividad = [];
        $sql = "
            SELECT o.*, a.nombre as area_nombre, l.nombre as usuario_nombre 
            FROM oficios o 
            LEFT JOIN areas a ON o.area_id = a.id 
            LEFT JOIN login l ON o.usuario_id = l.id 
            WHERE o.activo = 1 
            ORDER BY o.fecha_registro DESC 
            LIMIT 10";
        $result = mysqli_query($this->conn, $sql);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $actividad[] = $row;
            }
        }
        return $actividad;
    }
}
