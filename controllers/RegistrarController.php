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
        $usuarios_permitidos = ['Guardia']; // Ajusta según tus necesidades
        
        if (!in_array($tipo_usuario, $usuarios_permitidos)) {
            header('Location: index.php?action=homedash');
            exit();
        }
        
        $mensaje = "";
        $tipoMensaje = "";
        
        // Procesar formulario de registro de oficio
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->procesarRegistro();
            $mensaje = $resultado['mensaje'];
            $tipoMensaje = $resultado['tipo'];
        }
        
        // Cargar la vista
        require_once __DIR__ . '/../views/registrar.php';
    }
    
    private function procesarRegistro() {
        // Recoger y sanitizar datos del formulario
        $remitente = mysqli_real_escape_string($this->db, $_POST['remitente']);
        $dependencia = mysqli_real_escape_string($this->db, $_POST['dependencia']);
        $numero_documento = isset($_POST['numeroDocumento']) ? mysqli_real_escape_string($this->db, $_POST['numeroDocumento']) : null;
        
        $area_derivada_id=2;
        $usuario_derivado_id=2;
        // Valores fijos
        $area_id = 3; // Valor fijo para area_id
        $usuario_id = $_SESSION['usuario_id']; // Usar el ID del usuario logueado
        
        // Verificar si el área existe
        $query_area_check = mysqli_query($this->db, "SELECT id FROM areas WHERE id = '$area_id'");
        if (mysqli_num_rows($query_area_check) === 0) {
            return ['mensaje' => "Error: El área seleccionada no es válida", 'tipo' => "error"];
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
        
        // Insertar en la base de datos
        $insert_query = "INSERT INTO oficios (remitente, dependencia, numero_documento, archivo_nombre, archivo_ruta, area_derivada_id, usuario_derivado_id, area_id, usuario_id) 
                        VALUES ('$remitente', '$dependencia', '$numero_documento', '$archivo_nombre', '$archivo_ruta2', '$area_derivada_id', '$usuario_derivado_id', '$area_id', '$usuario_id')";
        
        if (mysqli_query($this->db, $insert_query)) {
            return ['mensaje' => "Oficio registrado correctamente.", 'tipo' => "success"];
        } else {
            return ['mensaje' => "Error al registrar el oficio: " . mysqli_error($this->db), 'tipo' => "error"];
        }
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