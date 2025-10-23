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
    
        // Verificar solo en áreas activas
        $check = mysqli_query($this->conn, "SELECT * FROM areas WHERE nombre = '$nombre' AND activo = 1");
        if (mysqli_num_rows($check) > 0) {
            return "Error: El área ya existe";
        }
    
        // Si existe una área inactiva con el mismo nombre, reactivarla
        $check_inactiva = mysqli_query($this->conn, "SELECT * FROM areas WHERE nombre = '$nombre' AND activo = 0");
        if (mysqli_num_rows($check_inactiva) > 0) {
            $area_inactiva = mysqli_fetch_assoc($check_inactiva);
            $sql = "UPDATE areas SET activo = 1, descripcion = '$descripcion', fecha_creacion = CURRENT_TIMESTAMP WHERE id = " . $area_inactiva['id'];
            return mysqli_query($this->conn, $sql) 
                ? "Área reactivada exitosamente" 
                : "Error al reactivar área: " . mysqli_error($this->conn);
        }
    
        // Crear nueva área
        $sql = "INSERT INTO areas (nombre, descripcion) VALUES ('$nombre', '$descripcion')";
        return mysqli_query($this->conn, $sql) 
            ? "Área creada exitosamente" 
            : "Error al crear área: " . mysqli_error($this->conn);
    }

    public function actualizar($id, $nombre, $descripcion, $nombre_anterior) {
        $id = mysqli_real_escape_string($this->conn, $id);
        $nombre = mysqli_real_escape_string($this->conn, trim($nombre));
        $descripcion = mysqli_real_escape_string($this->conn, trim($descripcion));
        $nombre_anterior = mysqli_real_escape_string($this->conn, trim($nombre_anterior));
    
        // Si el nombre cambió, verificar que no exista ninguna área (activa o inactiva) con el nuevo nombre
        if ($nombre !== $nombre_anterior) {
            $check = mysqli_query($this->conn, "SELECT * FROM areas WHERE nombre = '$nombre' AND id != '$id'");
            if (mysqli_num_rows($check) > 0) {
                $area_existente = mysqli_fetch_assoc($check);
                if ($area_existente['activo'] == 1) {
                    return "Error: Ya existe otra área activa con el nombre '$nombre'";
                } else {
                    return "Error: El nombre '$nombre' no es posible, intenta creando una con el mismo nombre para reactivación.";
                }
            }
        }
    
        $sql = "UPDATE areas SET nombre = '$nombre', descripcion = '$descripcion' WHERE id = '$id' AND activo = 1";
        
        if (mysqli_query($this->conn, $sql)) {
            // Actualizar la referencia en los usuarios
            mysqli_query($this->conn, "UPDATE login SET area_id = '$id' WHERE area_id IN (SELECT id FROM areas WHERE nombre = '$nombre_anterior' AND activo = 1)");
            return "Área actualizada exitosamente";
        } else {
            // Manejar error de duplicado de MySQL
            if (mysqli_errno($this->conn) == 1062) {
                return "Error: El nombre '$nombre' no es posible, intenta creando una con el mismo nombre para reactivación.";
            }
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