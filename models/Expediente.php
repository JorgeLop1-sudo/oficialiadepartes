<?php
class Expediente {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodos($filtros = [], $usuario_id = null, $tipo_usuario = null) {
        $expedientes = [];
        
        $query = "
            SELECT o.*, a.nombre as area_nombre, l.nombre as usuario_nombre,
                   ad.nombre as area_derivada_nombre, ud.nombre as usuario_derivado_nombre
            FROM oficios o 
            LEFT JOIN areas a ON o.area_id = a.id 
            LEFT JOIN login l ON o.usuario_id = l.id
            LEFT JOIN areas ad ON o.area_derivada_id = ad.id
            LEFT JOIN login ud ON o.usuario_derivado_id = ud.id
            WHERE 1=1
        ";

        // Filtrar por usuario si no es admin
        if ($tipo_usuario !== 'Administrador' && $usuario_id) {
            $usuario_id = mysqli_real_escape_string($this->conn, $usuario_id);
            $query .= " AND (o.usuario_derivado_id = '$usuario_id' OR o.usuario_id = '$usuario_id')";
        }

        // Aplicar filtros existentes
        if (!empty($filtros['numero'])) {
            $numero = mysqli_real_escape_string($this->conn, $filtros['numero']);
            $query .= " AND o.numero_documento LIKE '%$numero%'";
        }

        if (!empty($filtros['estado'])) {
            $estado = mysqli_real_escape_string($this->conn, $filtros['estado']);
            $query .= " AND o.estado = '$estado'";
        }

        $query .= " ORDER BY o.fecha_registro DESC";

        $result = mysqli_query($this->conn, $query);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $expedientes[] = $row;
            }
        }
        
        return $expedientes;
    }

    // Método para obtener el historial completo
    /*public function obtenerHistorialDerivaciones($oficio_id) {
        $historial = [];
        $oficio_id = mysqli_real_escape_string($this->conn, $oficio_id);
        
        $query = "
            SELECT 
                h.*,
                ao.nombre as area_origen_nombre,
                uo.nombre as usuario_origen_nombre,
                uo.usuario as usuario_origen_usuario,
                ad.nombre as area_destino_nombre,
                ud.nombre as usuario_destino_nombre,
                ud.usuario as usuario_destino_usuario,
                o.remitente,
                o.asunto,
                o.numero_documento
            FROM historial_derivaciones h
            LEFT JOIN areas ao ON h.area_origen_id = ao.id
            LEFT JOIN login uo ON h.usuario_origen_id = uo.id
            LEFT JOIN areas ad ON h.area_destino_id = ad.id
            LEFT JOIN login ud ON h.usuario_destino_id = ud.id
            LEFT JOIN oficios o ON h.oficio_id = o.id
            WHERE h.oficio_id = '$oficio_id'
            ORDER BY h.fecha_derivacion ASC
        ";
        
        $result = mysqli_query($this->conn, $query);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $historial[] = $row;
            }
        }
        
        return $historial;
    }*/



    public function obtenerPorId($id) {
        $id = mysqli_real_escape_string($this->conn, $id);
        $query = "SELECT * FROM oficios WHERE id = '$id'";
        $result = mysqli_query($this->conn, $query);
        return $result && mysqli_num_rows($result) > 0 ? mysqli_fetch_assoc($result) : null;
    }

    public function eliminar($id) {
        $id = mysqli_real_escape_string($this->conn, $id);
        
        // Obtener información del archivo
        $archivo_info = $this->obtenerPorId($id);
        $archivo_ruta = $archivo_info['archivo_ruta'] ?? '';
        
        // Eliminar archivo físico si existe
        if (!empty($archivo_ruta) && file_exists($archivo_ruta)) {
            unlink($archivo_ruta);
        }
        
        // Eliminar registro de la base de datos
        $delete_query = "DELETE FROM oficios WHERE id = '$id'";
        return mysqli_query($this->conn, $delete_query) 
            ? "Oficio eliminado correctamente" 
            : "Error al eliminar el oficio: " . mysqli_error($this->conn);
    }

    public function derivar($id, $area_derivada, $usuario_derivado, $respuesta) {
        $id = mysqli_real_escape_string($this->conn, $id);
        $area_derivada = mysqli_real_escape_string($this->conn, $area_derivada);
        $usuario_derivado = mysqli_real_escape_string($this->conn, $usuario_derivado);
        $respuesta = mysqli_real_escape_string($this->conn, $respuesta);

        // Primero, obtener información actual del oficio para el historial
        $oficio_actual = $this->obtenerPorId($id);
        if (!$oficio_actual) {
            return "Error: Oficio no encontrado";
        }

        // Registrar en el historial ANTES de actualizar
        //$this->registrarEnHistorial($oficio_actual, $area_derivada, $usuario_derivado, $respuesta);

        // Actualizar el oficio
        $update_query = "UPDATE oficios SET 
                        area_derivada_id = '$area_derivada',
                        usuario_derivado_id = '$usuario_derivado',
                        respuesta = '$respuesta',
                        estado = 'tramite',
                        fecha_derivacion = NOW()
                        WHERE id = '$id'";
        
        return mysqli_query($this->conn, $update_query) 
            ? "Oficio derivado correctamente" 
            : "Error al derivar el oficio: " . mysqli_error($this->conn);
    }

     // Método para registrar en el historial
    /* private function registrarEnHistorial($oficio, $area_destino_id, $usuario_destino_id, $respuesta) {
        $oficio_id = mysqli_real_escape_string($this->conn, $oficio['id']);
        $area_origen_id = mysqli_real_escape_string($this->conn, $oficio['area_id']);
        $usuario_origen_id = mysqli_real_escape_string($this->conn, $oficio['usuario_id']);
        $area_destino_id = mysqli_real_escape_string($this->conn, $area_destino_id);
        $usuario_destino_id = mysqli_real_escape_string($this->conn, $usuario_destino_id);
        $respuesta = mysqli_real_escape_string($this->conn, $respuesta);
        $estado_actual = mysqli_real_escape_string($this->conn, $oficio['estado']);

        $query = "INSERT INTO historial_derivaciones (
                    oficio_id, area_origen_id, usuario_origen_id, 
                    area_destino_id, usuario_destino_id, respuesta, estado
                ) VALUES (
                    '$oficio_id', '$area_origen_id', '$usuario_origen_id',
                    '$area_destino_id', '$usuario_destino_id', '$respuesta', '$estado_actual'
                )";

        return mysqli_query($this->conn, $query);
    }*/

    public function obtenerUsuariosPorArea($area_id) {
        $area_id = mysqli_real_escape_string($this->conn, $area_id);
        
        $usuarios = [];
        $sql = "SELECT id, usuario, nombre 
                FROM login 
                WHERE area_id = '$area_id' 
                AND activo = 1 
                AND tipo_usuario != 'Guardia'  -- Opcional: excluir guardias si no deben aparecer
                ORDER BY nombre ASC";
        
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $usuarios[] = $row;
            }
        }
        
        return $usuarios;
    }

    // También registrar en historial cuando se responde un oficio
    public function actualizarRespuesta($id, $respuesta, $estado) {
        $id = mysqli_real_escape_string($this->conn, $id);
        $respuesta = mysqli_real_escape_string($this->conn, $respuesta);
        $estado = mysqli_real_escape_string($this->conn, $estado);
        
        // Obtener información actual del oficio
        $oficio_actual = $this->obtenerPorId($id);
        if (!$oficio_actual) {
            return [
                'success' => false,
                'mensaje' => "Error: Oficio no encontrado"
            ];
        }

        // Registrar respuesta en el historial
        //$this->registrarRespuestaEnHistorial($oficio_actual, $respuesta, $estado);
        
        $update_query = "UPDATE oficios SET 
                        respuesta = '$respuesta',
                        estado = '$estado',
                        fecha_respuesta = NOW()
                        WHERE id = '$id'";
        
        if (mysqli_query($this->conn, $update_query)) {
            return [
                'success' => true,
                'mensaje' => "Oficio " . ($estado == 'completado' ? 'completado' : 'denegado') . " correctamente"
            ];
        } else {
            return [
                'success' => false,
                'mensaje' => "Error al actualizar el oficio: " . mysqli_error($this->conn)
            ];
        }
    }

    /*private function registrarRespuestaEnHistorial($oficio, $respuesta, $nuevo_estado) {
        $oficio_id = mysqli_real_escape_string($this->conn, $oficio['id']);
        $area_origen_id = mysqli_real_escape_string($this->conn, $oficio['area_id']);
        $usuario_origen_id = mysqli_real_escape_string($this->conn, $oficio['usuario_id']);
        $respuesta = mysqli_real_escape_string($this->conn, $respuesta);

        $query = "INSERT INTO historial_derivaciones (
                    oficio_id, area_origen_id, usuario_origen_id, 
                    respuesta, estado, observaciones
                ) VALUES (
                    '$oficio_id', '$area_origen_id', '$usuario_origen_id',
                    '$respuesta', '$nuevo_estado', 'RESPUESTA FINAL'
                )";

        return mysqli_query($this->conn, $query);
    }*/
    
}
?>