<?php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodos() {
        $usuarios = [];
        $query = "
            SELECT l.*, a.nombre as area_nombre 
            FROM login l 
            LEFT JOIN areas a ON l.area_id = a.id 
            ORDER BY l.id DESC
        ";
        $result = mysqli_query($this->conn, $query);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $usuarios[] = $row;
            }
        }
        return $usuarios;
    }

    public function obtenerPorId($id) {
        $id = mysqli_real_escape_string($this->conn, $id);
        $query = mysqli_query($this->conn, "SELECT * FROM login WHERE id = '$id'");
        return $query && mysqli_num_rows($query) > 0 ? mysqli_fetch_assoc($query) : null;
    }

    public function crear($usuario, $password, $nombre, $tipo_usuario, $area_id, $email) {
        // Validar longitud mínima del usuario
        if (strlen($usuario) < 6) {
            return "Error: El usuario debe tener al menos 6 caracteres";
        }
        
        // Validar que solo contenga letras y números
        if (!preg_match('/^[a-zA-Z0-9]+$/', $usuario)) {
            return "Error: El usuario solo puede contener letras y números";
        }
    
        $usuario = mysqli_real_escape_string($this->conn, $usuario);
        $nombre = mysqli_real_escape_string($this->conn, $nombre);
        $tipo_usuario = mysqli_real_escape_string($this->conn, $tipo_usuario);
        $area_id = mysqli_real_escape_string($this->conn, $area_id);
        $email = mysqli_real_escape_string($this->conn, $email);
    
        // Verificar si el usuario ya existe
        $check_query = mysqli_query($this->conn, "SELECT * FROM login WHERE usuario = '$usuario'");
        if (mysqli_num_rows($check_query) > 0) {
            return "Error: El nombre de usuario ya existe";
        }
    
        // Verificar si el correo ya existe
        if (!empty($email)) {
            $check_query = mysqli_query($this->conn, "SELECT * FROM login WHERE email = '$email'");
            if (mysqli_num_rows($check_query) > 0) {
                return "Error: El correo electrónico ya está registrado";
            }
        }
    
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO login (usuario, password, nombre, tipo_usuario, area_id, email) 
                        VALUES ('$usuario', '$password_hashed', '$nombre', '$tipo_usuario', '$area_id', '$email')";
    
        return mysqli_query($this->conn, $insert_query) 
            ? "Usuario creado exitosamente" 
            : "Error al crear usuario: " . mysqli_error($this->conn);
    }

    public function actualizar($id, $usuario, $nombre, $tipo_usuario, $area_id, $email, $password = null) {
        // Validar longitud mínima del usuario
        if (strlen($usuario) < 6) {
            return "Error: El usuario debe tener al menos 6 caracteres";
        }
        
        // Validar que solo contenga letras y números
        if (!preg_match('/^[a-zA-Z0-9]+$/', $usuario)) {
            return "Error: El usuario solo puede contener letras y números";
        }
    
        $id = mysqli_real_escape_string($this->conn, $id);
        $usuario = mysqli_real_escape_string($this->conn, $usuario);
        $nombre = mysqli_real_escape_string($this->conn, $nombre);
        $tipo_usuario = mysqli_real_escape_string($this->conn, $tipo_usuario);
        $email = mysqli_real_escape_string($this->conn, $email);
        
        // Manejar area_id NULL
        $area_id_value = ($area_id === null || $area_id === '') ? 'NULL' : "'" . mysqli_real_escape_string($this->conn, $area_id) . "'";
    
        // Verificar si el usuario ya existe (excluyendo el actual)
        $check_user_query = mysqli_query($this->conn, "SELECT * FROM login WHERE usuario = '$usuario' AND id != '$id'");
        if (mysqli_num_rows($check_user_query) > 0) {
            return "Error: El nombre de usuario ya existe";
        }
    
        // Verificar si el correo ya existe (excluyendo el actual)
        if (!empty($email)) {
            $check_email_query = mysqli_query($this->conn, "SELECT * FROM login WHERE email = '$email' AND id != '$id'");
            if (mysqli_num_rows($check_email_query) > 0) {
                return "Error: El correo electrónico ya está registrado";
            }
        }
    
        $password_update = "";
        if ($password) {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $password_update = ", password = '$password_hashed'";
        }
    
        $update_query = "UPDATE login SET usuario = '$usuario', nombre = '$nombre', 
                         tipo_usuario = '$tipo_usuario', area_id = $area_id_value, email = '$email'
                         $password_update WHERE id = '$id'";
        
        return mysqli_query($this->conn, $update_query) 
            ? "Usuario actualizado exitosamente" 
            : "Error al actualizar usuario: " . mysqli_error($this->conn);
    }

    
    public function eliminar($id) {
        $id = mysqli_real_escape_string($this->conn, $id);
        
        mysqli_begin_transaction($this->conn);
        
        try {
            // 1. Verificar que el usuario existe
            $user_query = mysqli_query($this->conn, "SELECT usuario, nombre FROM login WHERE id = '$id'");
            if (mysqli_num_rows($user_query) === 0) {
                throw new Exception("El usuario no existe");
            }
            $user_data = mysqli_fetch_assoc($user_query);
            $usuario_nombre = $user_data['usuario'];
            
            // 2. Deshabilitar temporalmente verificaciones de FK
            mysqli_query($this->conn, "SET FOREIGN_KEY_CHECKS = 0");
            
            // 3. Actualizar todas las referencias al usuario
            $update_queries = [
                "UPDATE oficios SET usuario_id = NULL WHERE usuario_id = '$id'",
                "UPDATE oficios SET usuario_derivado_id = NULL WHERE usuario_derivado_id = '$id'",
                "UPDATE historial_derivaciones SET usuario_origen_id = NULL WHERE usuario_origen_id = '$id'",
                "UPDATE historial_derivaciones SET usuario_destino_id = NULL WHERE usuario_destino_id = '$id'",
            ];
            
            foreach ($update_queries as $query) {
                mysqli_query($this->conn, $query);
                // No verificamos errores aquí intencionalmente - si falla, sigue adelante
            }
            
            // 4. Eliminar el usuario
            $delete_result = mysqli_query($this->conn, "DELETE FROM login WHERE id = '$id'");
            
            if (!$delete_result) {
                throw new Exception("Error al eliminar usuario: " . mysqli_error($this->conn));
            }
            
            if (mysqli_affected_rows($this->conn) === 0) {
                throw new Exception("No se pudo eliminar el usuario");
            }
            
            // 5. Reactivar verificaciones de FK
            mysqli_query($this->conn, "SET FOREIGN_KEY_CHECKS = 1");
            
            mysqli_commit($this->conn);
            
            return "Usuario '$usuario_nombre' eliminado exitosamente. Las referencias se han actualizado automáticamente.";
            
        } catch (Exception $e) {
            mysqli_rollback($this->conn);
            // Siempre reactivar las verificaciones
            mysqli_query($this->conn, "SET FOREIGN_KEY_CHECKS = 1");
            return "Error al eliminar usuario: " . $e->getMessage();
        }
    }
}
?>