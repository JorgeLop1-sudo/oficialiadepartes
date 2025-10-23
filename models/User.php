<?php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Asegurarte de que el método obtenerTodos solo traiga usuarios activos
    public function obtenerTodos() {
        $usuarios = [];
        $query = "
            SELECT l.*, a.nombre as area_nombre 
            FROM login l 
            LEFT JOIN areas a ON l.area_id = a.id AND a.activo = 1
            WHERE l.activo = 1
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
        $query = mysqli_query($this->conn, "SELECT * FROM login WHERE id = '$id' AND activo = 1");
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
    
        // Verificar si el usuario ya existe (incluyendo usuarios desactivados)
        $check_query = mysqli_query($this->conn, "SELECT * FROM login WHERE usuario = '$usuario'");
        if (mysqli_num_rows($check_query) > 0) {
            // Verificar si el usuario está activo
            $existing_user = mysqli_fetch_assoc($check_query);
            if ($existing_user['activo'] == 1) {
                return "Error: El nombre de usuario ya existe";
            } else {
                // Si el usuario existe pero está desactivado, reactivarlo
                return $this->reactivarUsuario($existing_user['id'], $password, $nombre, $tipo_usuario, $area_id, $email);
            }
        }
    
        // VERIFICACIÓN MODIFICADA: Verificar si el correo ya existe (incluyendo inactivos)
        if (!empty($email)) {
            $check_query = mysqli_query($this->conn, "SELECT * FROM login WHERE email = '$email'");
            if (mysqli_num_rows($check_query) > 0) {
                $existing_email = mysqli_fetch_assoc($check_query);
                if ($existing_email['activo'] == 1) {
                    return "Error: El correo electrónico ya está registrado";
                } else {
                    return "Error: El correo electrónico '$email' no está disponible (usuario inactivo)";
                }
            }
        }
    
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO login (usuario, password, nombre, tipo_usuario, area_id, email) 
                        VALUES ('$usuario', '$password_hashed', '$nombre', '$tipo_usuario', '$area_id', '$email')";
    
        return mysqli_query($this->conn, $insert_query) 
            ? "Usuario creado exitosamente" 
            : "Error al crear usuario: " . mysqli_error($this->conn);
    }

    private function reactivarUsuario($id, $password, $nombre, $tipo_usuario, $area_id, $email) {
        $id = mysqli_real_escape_string($this->conn, $id);
        $nombre = mysqli_real_escape_string($this->conn, $nombre);
        $tipo_usuario = mysqli_real_escape_string($this->conn, $tipo_usuario);
        $area_id = mysqli_real_escape_string($this->conn, $area_id);
        $email = mysqli_real_escape_string($this->conn, $email);
        
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        
        $update_query = "UPDATE login SET 
                        password = '$password_hashed', 
                        nombre = '$nombre', 
                        tipo_usuario = '$tipo_usuario', 
                        area_id = '$area_id', 
                        email = '$email',
                        activo = 1 
                        WHERE id = '$id'";
        
        return mysqli_query($this->conn, $update_query) 
            ? "Usuario reactivado exitosamente" 
            : "Error al reactivar usuario: " . mysqli_error($this->conn);
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
    
        // VERIFICACIÓN MODIFICADA: Verificar si el usuario ya existe (incluyendo inactivos, excluyendo el actual)
        $check_user_query = mysqli_query($this->conn, "SELECT * FROM login WHERE usuario = '$usuario' AND id != '$id'");
        if (mysqli_num_rows($check_user_query) > 0) {
            $existing_user = mysqli_fetch_assoc($check_user_query);
            if ($existing_user['activo'] == 1) {
                return "Error: El nombre de usuario ya existe";
            } else {
                return "Error: El nombre de usuario '$usuario' no está disponible (usuario inactivo)";
            }
        }
    
        // VERIFICACIÓN MODIFICADA: Verificar si el correo ya existe (incluyendo inactivos, excluyendo el actual)
        if (!empty($email)) {
            $check_email_query = mysqli_query($this->conn, "SELECT * FROM login WHERE email = '$email' AND id != '$id'");
            if (mysqli_num_rows($check_email_query) > 0) {
                $existing_email = mysqli_fetch_assoc($check_email_query);
                if ($existing_email['activo'] == 1) {
                    return "Error: El correo electrónico ya está registrado";
                } else {
                    return "Error: El correo electrónico '$email' no está disponible (usuario inactivo)";
                }
            }
        }
    
        $password_update = "";
        if ($password) {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $password_update = ", password = '$password_hashed'";
        }
    
        $update_query = "UPDATE login SET usuario = '$usuario', nombre = '$nombre', 
                         tipo_usuario = '$tipo_usuario', area_id = $area_id_value, email = '$email'
                         $password_update WHERE id = '$id' AND activo = 1";
        
        return mysqli_query($this->conn, $update_query) 
            ? "Usuario actualizado exitosamente" 
            : "Error al actualizar usuario: " . mysqli_error($this->conn);
    }

    public function eliminar($id) {
        $id = mysqli_real_escape_string($this->conn, $id);
        
        // Obtener información del usuario antes de desactivarlo
        $user_query = mysqli_query($this->conn, "SELECT usuario, nombre FROM login WHERE id = '$id' AND activo = 1");
        if (mysqli_num_rows($user_query) === 0) {
            return "El usuario no existe o ya está desactivado";
        }
        $user_data = mysqli_fetch_assoc($user_query);
        $usuario_nombre = $user_data['usuario'];
        
        // Realizar eliminación lógica (soft delete)
        $sql = "UPDATE login SET activo = 0 WHERE id = '$id'";
        
        if (mysqli_query($this->conn, $sql)) {
            return "Usuario '$usuario_nombre' desactivado exitosamente";
        } else {
            return "Error al desactivar usuario: " . mysqli_error($this->conn);
        }
    }

    public function obtenerUsuariosActivosPorArea($area_id) {
        $area_id = mysqli_real_escape_string($this->conn, $area_id);
        
        $usuarios = [];
        $sql = "SELECT id, usuario, nombre, tipo_usuario
                FROM login 
                WHERE area_id = '$area_id' 
                AND activo = 1 
                ORDER BY nombre ASC";
        
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $usuarios[] = $row;
            }
        }
        
        return $usuarios;
    }
}
?>