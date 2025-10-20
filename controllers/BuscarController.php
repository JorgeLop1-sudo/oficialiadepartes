<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/BuscarModel.php';

class BuscarController {
    public function index() {
        include __DIR__ . '/../views/buscar.php';
    }

    public function buscar() {
        
        // Headers para evitar cache en páginas protegidas
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        header('Content-Type: application/json');
        
        $database = new Database();
        $conn = $database->connect();
        $buscarModel = new BuscarModel($conn);

        $numero_documento = isset($_POST['numero_documento']) ? $_POST['numero_documento'] : '';

        if (empty($numero_documento)) {
            echo json_encode(['success' => false, 'message' => 'Número de documento no proporcionado']);
            exit;
        }

        $oficio = $buscarModel->buscarPorNumeroDocumento($numero_documento);

        if ($oficio) {
            $response = [
                'success' => true,
                'data' => [
                    'remitente' => $oficio['remitente'],
                    'tipo_persona' => $oficio['tipo_persona'],
                    'dependencia' => $oficio['dependencia'] ?? '',
                    'numero_documento' => $oficio['numero_documento'] ?? '',
                    'correo' => $oficio['correo'] ?? '',
                    'telefono' => $oficio['telefono'] ?? '',
                    'asunto' => $oficio['asunto'] ?? '',
                    'archivo_nombre' => $oficio['archivo_nombre'] ?? '',
                    'archivo_ruta' => $oficio['archivo_ruta'] ?? '',
                    'respuesta' => $oficio['respuesta'] ?? '',
                    'area_derivada' => $oficio['area_derivada_nombre'] ?? '',
                    'usuario_derivado' => $oficio['usuario_derivado_nombre'] ?? '',
                    'area_registro' => $oficio['area_registro_nombre'] ?? '',
                    'usuario_registro' => $oficio['usuario_registro_nombre'] ?? '',
                    'fecha_derivacion' => $oficio['fecha_derivacion'] ?? '',
                    'fecha_respuesta' => $oficio['fecha_respuesta'] ?? '',
                    'fecha_registro' => $oficio['fecha_registro'] ?? '',
                    'estado' => $oficio['estado'] ?? '',
                    'observaciones' => $oficio['observaciones'] ?? '',
                    'prioridad' => $oficio['prioridad'] ?? ''
                ]
            ];
            echo json_encode($response);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró ningún oficio con ese número de documento']);
        }

        $conn->close();
        exit;
    }
}