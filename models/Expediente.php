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

    // Método para obtener el historial completo de un oficio
    public function obtenerHistorialDerivaciones($oficio_id) {
        $historial = [];
        $oficio_id = mysqli_real_escape_string($this->conn, $oficio_id);
        
        $query = "
            SELECT DISTINCT
                h.id,
                h.oficio_id,
                h.area_origen_id,
                h.usuario_origen_id,
                h.area_destino_id,
                h.usuario_destino_id,
                h.respuesta,
                h.estado,
                h.observaciones,
                h.fecha_derivacion,
                h.fecha_fin,
                h.tiempo_duracion,
                h.es_respuesta_final,
                ao.nombre as area_origen_nombre,
                uo.nombre as usuario_origen_nombre,
                uo.usuario as usuario_origen_usuario,
                ad.nombre as area_destino_nombre,
                ud.nombre as usuario_destino_nombre,
                ud.usuario as usuario_destino_usuario,
                o.remitente,
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
                // Calcular tiempo de duración formateado
                if ($row['tiempo_duracion']) {
                    $row['tiempo_formateado'] = $this->formatearTiempo($row['tiempo_duracion']);
                } else {
                    $row['tiempo_formateado'] = $row['es_respuesta_final'] ? 'Finalizado' : 'En proceso';
                }
                
                // Determinar el tipo de registro
                if ($row['es_respuesta_final']) {
                    $row['tipo_registro'] = 'RESPUESTA';
                } else if ($row['area_destino_id']) {
                    $row['tipo_registro'] = 'DERIVACIÓN';
                } else {
                    $row['tipo_registro'] = 'REGISTRO INICIAL';
                }
                
                $historial[] = $row;
            }
        }
        
        return $historial;
    }

    // Método para formatear el tiempo en formato legible
    private function formatearTiempo($segundos) {
        $dias = floor($segundos / 86400);
        $horas = floor(($segundos % 86400) / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segundos = $segundos % 60;

        $tiempo = '';
        if ($dias > 0) $tiempo .= $dias . 'd ';
        if ($horas > 0) $tiempo .= $horas . 'h ';
        if ($minutos > 0) $tiempo .= $minutos . 'm ';
        if ($segundos > 0) $tiempo .= $segundos . 's';

        return trim($tiempo);
    }

    public function obtenerPorId($id) {
        $id = mysqli_real_escape_string($this->conn, $id);
        $query = "
            SELECT o.*, 
                   a.nombre as area_nombre,
                   u.nombre as usuario_nombre,
                   ad.nombre as area_derivada_nombre,
                   ud.nombre as usuario_derivado_nombre
            FROM oficios o 
            LEFT JOIN areas a ON o.area_id = a.id 
            LEFT JOIN login u ON o.usuario_id = u.id
            LEFT JOIN areas ad ON o.area_derivada_id = ad.id
            LEFT JOIN login ud ON o.usuario_derivado_id = ud.id
            WHERE o.id = '$id'
        ";
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

    public function derivar($id, $area_destino_id, $usuario_destino_id, $respuesta) {
        $id = mysqli_real_escape_string($this->conn, $id);
        $area_destino_id = mysqli_real_escape_string($this->conn, $area_destino_id);
        $usuario_destino_id = mysqli_real_escape_string($this->conn, $usuario_destino_id);
        $respuesta = mysqli_real_escape_string($this->conn, $respuesta);

        // Obtener información actual del oficio
        $oficio_actual = $this->obtenerPorId($id);
        if (!$oficio_actual) {
            return "Error: Oficio no encontrado";
        }

        // Obtener información del usuario actual (quien está derivando)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $usuario_origen_id = $_SESSION['id'];
        $usuario_origen_info = $this->obtenerUsuarioPorId($usuario_origen_id);
        
        if (!$usuario_origen_info) {
            return "Error: Usuario no encontrado";
        }
        
        // Determinar el área de origen (del usuario que deriva)
        $area_origen_id = $usuario_origen_info['area_id'];

        // Cerrar el registro anterior del usuario actual si existe
        $this->cerrarRegistroHistorialAnterior($id, $usuario_origen_id);

        // Registrar la nueva derivación en el historial
        $this->registrarDerivacionEnHistorial(
            $id, 
            $area_origen_id, 
            $usuario_origen_id,
            $area_destino_id, 
            $usuario_destino_id, 
            $respuesta,
            'tramite'
        );

        // Actualizar el oficio
        $update_query = "UPDATE oficios SET 
                        area_derivada_id = '$area_destino_id',
                        usuario_derivado_id = '$usuario_destino_id',
                        respuesta = '$respuesta',
                        estado = 'tramite',
                        fecha_derivacion = NOW()
                        WHERE id = '$id'";
        
        return mysqli_query($this->conn, $update_query) 
            ? "Oficio derivado correctamente" 
            : "Error al derivar el oficio: " . mysqli_error($this->conn);
    }

    // Método para obtener información del usuario
    private function obtenerUsuarioPorId($usuario_id) {
        $usuario_id = mysqli_real_escape_string($this->conn, $usuario_id);
        $query = "SELECT * FROM login WHERE id = '$usuario_id'";
        $result = mysqli_query($this->conn, $query);
        return $result && mysqli_num_rows($result) > 0 ? mysqli_fetch_assoc($result) : null;
    }

    // Método para registrar derivación en el historial
    private function registrarDerivacionEnHistorial($oficio_id, $area_origen_id, $usuario_origen_id, 
                                                  $area_destino_id, $usuario_destino_id, $respuesta, $estado) {
        $oficio_id = mysqli_real_escape_string($this->conn, $oficio_id);
        $area_origen_id = mysqli_real_escape_string($this->conn, $area_origen_id);
        $usuario_origen_id = mysqli_real_escape_string($this->conn, $usuario_origen_id);
        $area_destino_id = mysqli_real_escape_string($this->conn, $area_destino_id);
        $usuario_destino_id = mysqli_real_escape_string($this->conn, $usuario_destino_id);
        $respuesta = mysqli_real_escape_string($this->conn, $respuesta);
        $estado = mysqli_real_escape_string($this->conn, $estado);

        $query = "INSERT INTO historial_derivaciones (
                    oficio_id, area_origen_id, usuario_origen_id, 
                    area_destino_id, usuario_destino_id, respuesta, estado
                ) VALUES (
                    '$oficio_id', '$area_origen_id', '$usuario_origen_id',
                    '$area_destino_id', '$usuario_destino_id', '$respuesta', '$estado'
                )";

        return mysqli_query($this->conn, $query);
    }

    // Método para cerrar el registro anterior del usuario específico
    private function cerrarRegistroHistorialAnterior($oficio_id, $usuario_id) {
        $oficio_id = mysqli_real_escape_string($this->conn, $oficio_id);
        $usuario_id = mysqli_real_escape_string($this->conn, $usuario_id);
        
        // Buscar el último registro donde este usuario fue el destino y no tiene fecha_fin
        $query = "SELECT id, fecha_derivacion FROM historial_derivaciones 
                 WHERE oficio_id = '$oficio_id' 
                 AND usuario_destino_id = '$usuario_id' 
                 AND fecha_fin IS NULL 
                 AND es_respuesta_final = 0
                 ORDER BY fecha_derivacion DESC LIMIT 1";
        
        $result = mysqli_query($this->conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $registro_anterior = mysqli_fetch_assoc($result);
            $registro_id = $registro_anterior['id'];
            
            // Calcular duración en segundos
            $duracion = time() - strtotime($registro_anterior['fecha_derivacion']);
            
            // Actualizar con fecha_fin y duración
            $update_query = "UPDATE historial_derivaciones 
                           SET fecha_fin = NOW(), tiempo_duracion = '$duracion'
                           WHERE id = '$registro_id'";
            
            mysqli_query($this->conn, $update_query);
        }
    }

    public function obtenerUsuariosPorArea($area_id) {
        $area_id = mysqli_real_escape_string($this->conn, $area_id);
        
        $usuarios = [];
        $sql = "SELECT id, usuario, nombre 
                FROM login 
                WHERE area_id = '$area_id' 
                AND activo = 1 
                AND tipo_usuario != 'Guardia'
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

        // Obtener información del usuario actual (quien está respondiendo)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $usuario_origen_id = $_SESSION['id'];
        $usuario_origen_info = $this->obtenerUsuarioPorId($usuario_origen_id);
        
        if (!$usuario_origen_info) {
            return [
                'success' => false,
                'mensaje' => "Error: Usuario no encontrado"
            ];
        }
        
        $area_origen_id = $usuario_origen_info['area_id'];

        // Cerrar el registro anterior del usuario actual
        $this->cerrarRegistroHistorialAnterior($id, $usuario_origen_id);

        // Registrar respuesta final en el historial
        $this->registrarRespuestaFinalEnHistorial(
            $id, 
            $area_origen_id, 
            $usuario_origen_id,
            $respuesta, 
            $estado
        );
        
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

    // Método para registrar respuesta final en el historial
    private function registrarRespuestaFinalEnHistorial($oficio_id, $area_origen_id, $usuario_origen_id, $respuesta, $estado) {
        $oficio_id = mysqli_real_escape_string($this->conn, $oficio_id);
        $area_origen_id = mysqli_real_escape_string($this->conn, $area_origen_id);
        $usuario_origen_id = mysqli_real_escape_string($this->conn, $usuario_origen_id);
        $respuesta = mysqli_real_escape_string($this->conn, $respuesta);
        $estado = mysqli_real_escape_string($this->conn, $estado);

        $query = "INSERT INTO historial_derivaciones (
                    oficio_id, area_origen_id, usuario_origen_id, 
                    respuesta, estado, es_respuesta_final
                ) VALUES (
                    '$oficio_id', '$area_origen_id', '$usuario_origen_id',
                    '$respuesta', '$estado', 1
                )";

        return mysqli_query($this->conn, $query);
    }

    // Método para registrar el oficio inicial en el historial cuando se crea
    public function registrarOficioInicial($oficio_id, $area_id, $usuario_id, $remitente, $numero_documento) {
        $oficio_id = mysqli_real_escape_string($this->conn, $oficio_id);
        $area_id = mysqli_real_escape_string($this->conn, $area_id);
        $usuario_id = mysqli_real_escape_string($this->conn, $usuario_id);
        $remitente = mysqli_real_escape_string($this->conn, $remitente);
        $numero_documento = mysqli_real_escape_string($this->conn, $numero_documento);

        $observaciones = "Oficio creado - Remitente: " . $remitente . " - N° Doc: " . $numero_documento;

        $query = "INSERT INTO historial_derivaciones (
                    oficio_id, area_origen_id, usuario_origen_id, 
                    estado, observaciones
                ) VALUES (
                    '$oficio_id', '$area_id', '$usuario_id',
                    'pendiente', '$observaciones'
                )";

        return mysqli_query($this->conn, $query);
    }
    
}
?>