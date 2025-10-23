<?php
class Area {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodas() {
        $areas = [];
        $sql = "
            SELECT a.*, 
                   COUNT(l.id) as total_usuarios,
                   GROUP_CONCAT(CONCAT(l.nombre, ' (', l.usuario, ')') SEPARATOR '| ') as usuarios_lista
            FROM areas a
            LEFT JOIN login l ON a.id = l.area_id AND l.activo = 1
            WHERE a.activo = 1
            GROUP BY a.id
            ORDER BY a.nombre ASC";
        $result = mysqli_query($this->conn, $sql);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $areas[] = $row;
            }
        }
        return $areas;
    }

    public function obtenerTodasActivas() {
        $areas = [];
        $sql = "SELECT id, nombre FROM areas WHERE activo = 1 ORDER BY nombre ASC";
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $areas[] = $row;
            }
        } else {
            error_log("Error en consulta de áreas: " . mysqli_error($this->conn));
        }
        
        return $areas;
    }

    public function obtenerPorId($id) {
        $id = mysqli_real_escape_string($this->conn, $id);
        $query = mysqli_query($this->conn, "SELECT * FROM areas WHERE id = '$id' AND activo = 1");
        return $query && mysqli_num_rows($query) > 0 ? mysqli_fetch_assoc($query) : null;
    }

    public function crear($nombre, $descripcion) {
        $nombre = mysqli_real_escape_string($this->conn, $nombre);
        $descripcion = mysqli_real_escape_string($this->conn, $descripcion);

        $check = mysqli_query($this->conn, "SELECT * FROM areas WHERE nombre = '$nombre' AND activo = 1");
        if (mysqli_num_rows($check) > 0) {
            return "Error: El área ya existe";
        }

        $sql = "INSERT INTO areas (nombre, descripcion) VALUES ('$nombre', '$descripcion')";
        return mysqli_query($this->conn, $sql) ? "Área creada exitosamente" : "Error al crear área: " . mysqli_error($this->conn);
    }

    public function actualizar($id, $nombre, $descripcion, $nombre_anterior) {
        $id = mysqli_real_escape_string($this->conn, $id);
        $nombre = mysqli_real_escape_string($this->conn, $nombre);
        $descripcion = mysqli_real_escape_string($this->conn, $descripcion);
        $nombre_anterior = mysqli_real_escape_string($this->conn, $nombre_anterior);

        $sql = "UPDATE areas SET nombre = '$nombre', descripcion = '$descripcion' WHERE id = '$id' AND activo = 1";
        if (mysqli_query($this->conn, $sql)) {
            mysqli_query($this->conn, "UPDATE login SET area_id = '$id' 
                                       WHERE area_id IN (SELECT id FROM areas WHERE nombre = '$nombre_anterior' AND activo = 1)");
            return "Área actualizada exitosamente y usuarios migrados";
        } else {
            return "Error al actualizar área: " . mysqli_error($this->conn);
        }
    }

    public function eliminar($id) {
        $id = mysqli_real_escape_string($this->conn, $id);
        
        // Verificar si hay usuarios activos asignados a esta área
        $check = mysqli_query($this->conn, "SELECT * FROM login WHERE area_id = '$id' AND activo = 1");
        if (mysqli_num_rows($check) > 0) {
            return "No se puede eliminar el área porque está asignada a usuarios activos";
        }
        
        // Realizar eliminación lógica (soft delete)
        $sql = "UPDATE areas SET activo = 0 WHERE id = '$id'";
        return mysqli_query($this->conn, $sql) 
            ? "Área desactivada exitosamente" 
            : "Error al desactivar área: " . mysqli_error($this->conn);
    }
}
?>