<?php
require_once __DIR__ . '/../models/Expediente.php';
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../models/User.php';

class RegistrarController {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function registro() {
        // Verificar si el usuario está logueado y tiene permisos
        session_start();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        
        // Verificar si el usuario tiene permiso para acceder (guardia, admin, o user según necesites)
        $tipo_usuario = $_SESSION['tipo_usuario'] ?? '';
        $usuarios_permitidos = ['Guardia', 'Administrador']; // Ajusta según tus necesidades
        
        if (!in_array($tipo_usuario, $usuarios_permitidos)) {
            header('Location: index.php?action=homedash');
            exit();
        }
        
        $mensaje = "";
        $tipoMensaje = "";
        
        // Obtener áreas para el select
        $areaModel = new Area($this->db);
        $areas = $areaModel->obtenerTodasActivas();
        
        // Procesar formulario de registro de oficio
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->procesarRegistro();
            
            // Guardar mensaje en sesión y redirigir
            $_SESSION['mensaje_registro'] = $resultado['mensaje'];
            $_SESSION['tipo_mensaje_registro'] = $resultado['tipo'];
            
            // REDIRECCIÓN después del POST (PRG Pattern)
            header('Location: index.php?action=registrar');
            exit();
        }

        
        // Recuperar mensajes de sesión si existen
        if (isset($_SESSION['mensaje_registro'])) {
            $mensaje = $_SESSION['mensaje_registro'];
            $tipoMensaje = $_SESSION['tipo_mensaje_registro'];
            
            // Limpiar mensajes de sesión después de usarlos
            unset($_SESSION['mensaje_registro']);
            unset($_SESSION['tipo_mensaje_registro']);
        }
        
        // Pasar áreas a la vista
        $view_data = compact('mensaje', 'tipoMensaje', 'areas');
        extract($view_data);
        
        // Cargar la vista
        require_once __DIR__ . '/../views/registrar.php';
    }
    
    private function procesarRegistro() {
        // Recoger y sanitizar datos del formulario
        $remitente = mysqli_real_escape_string($this->db, $_POST['remitente']);
        $dependencia = mysqli_real_escape_string($this->db, $_POST['dependencia']);
        $numero_documento = isset($_POST['numeroDocumento']) ? mysqli_real_escape_string($this->db, $_POST['numeroDocumento']) : null;
        
        // Obtener área y usuario destino del formulario
        $area_destino_id = isset($_POST['area_derivada']) ? intval($_POST['area_derivada']) : null;
        $usuario_destino_id = isset($_POST['usuario_derivado']) ? intval($_POST['usuario_derivado']) : null;
        
        // Validar que se hayan seleccionado área y usuario destino
        if (!$area_destino_id || !$usuario_destino_id) {
            return ['mensaje' => "Error: Debe seleccionar un área y usuario destino", 'tipo' => "error"];
        }
        
        // Obtener los nombres del área y usuario destino
        $area_destino_nombre = $this->obtenerNombreArea($area_destino_id);
        $usuario_destino_nombre = $this->obtenerNombreUsuario($usuario_destino_id);
        
        if (!$area_destino_nombre || !$usuario_destino_nombre) {
            return ['mensaje' => "Error: El área o usuario destino no existen", 'tipo' => "error"];
        }
        
        // Valores fijos para área y usuario de registro (como los tenías originalmente)
        $area_id = 3; // Valor fijo para area_id (Caseta)
        $usuario_id = $_SESSION['usuario_id']; // Usar el ID del usuario logueado
        
        // Valores fijos para derivación (como los tenías originalmente)
        $area_derivada_id = 2; // Recepción
        $usuario_derivado_id = 2; // Juanita
        
        // Verificar si el área destino existe
        $query_area_check = mysqli_query($this->db, "SELECT id FROM areas WHERE id = '$area_destino_id'");
        if (mysqli_num_rows($query_area_check) === 0) {
            return ['mensaje' => "Error: El área de destino seleccionada no es válida", 'tipo' => "error"];
        }
        
        // Verificar si el usuario destino existe
        $query_usuario_check = mysqli_query($this->db, "SELECT id FROM login WHERE id = '$usuario_destino_id' AND area_id = '$area_destino_id' AND activo=1");
        if (mysqli_num_rows($query_usuario_check) === 0) {
            return ['mensaje' => "Error: El usuario de destino no es válido o no pertenece al área seleccionada", 'tipo' => "error"];
        }
        
        // Procesar archivo subido
        $archivo_nombre = null;
        $archivo_ruta = null;
        $archivo_ruta2 = null;
        
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $resultado_archivo = $this->procesarArchivo();
            if ($resultado_archivo['error']) {
                return ['mensaje' => $resultado_archivo['mensaje'], 'tipo' => "error"];
            }
            $archivo_nombre = $resultado_archivo['nombre'];
            $archivo_ruta2 = $resultado_archivo['ruta'];
        }
        
        // Insertar en la base de datos con los nuevos campos
        $insert_query = "INSERT INTO oficios (
                        remitente, dependencia, numero_documento, archivo_nombre, archivo_ruta, 
                        area_derivada_id, usuario_derivado_id, area_id, usuario_id,
                        area_destino, usuario_destino
                    ) VALUES (
                        '$remitente', '$dependencia', '$numero_documento', '$archivo_nombre', '$archivo_ruta2', 
                        '$area_derivada_id', '$usuario_derivado_id', '$area_id', '$usuario_id',
                        '$area_destino_nombre', '$usuario_destino_nombre'
                    )";
        
        if (mysqli_query($this->db, $insert_query)) {
            return ['mensaje' => "Oficio registrado correctamente.", 'tipo' => "success"];
        } else {
            return ['mensaje' => "Error al registrar el oficio: " . mysqli_error($this->db), 'tipo' => "error"];
        }
    }
    
    private function obtenerNombreArea($area_id) {
        $query = mysqli_query($this->db, "SELECT nombre FROM areas WHERE id = '$area_id'");
        if ($query && mysqli_num_rows($query) > 0) {
            $row = mysqli_fetch_assoc($query);
            return $row['nombre'];
        }
        return null;
    }
    
    private function obtenerNombreUsuario($usuario_id) {
        $query = mysqli_query($this->db, "SELECT nombre FROM login WHERE id = '$usuario_id'");
        if ($query && mysqli_num_rows($query) > 0) {
            $row = mysqli_fetch_assoc($query);
            return $row['nombre'];
        }
        return null;
    }
    
    private function procesarArchivo() {
        $directorio = "uploads/";
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }
        
        $archivo_nombre = basename($_FILES['archivo']['name']);
        $archivo_temporal = $_FILES['archivo']['tmp_name'];
        $archivo_ruta = $directorio . time() . '_' . $archivo_nombre;
        $archivo_ruta2 = '/oficialiadepartes/' . $directorio . time() . '_' . $archivo_nombre;
        
        if (!move_uploaded_file($archivo_temporal, $archivo_ruta)) {
            return ['error' => true, 'mensaje' => "Error al subir el archivo."];
        }
        
        return ['error' => false, 'nombre' => $archivo_nombre, 'ruta' => $archivo_ruta2];
    }
}
?>