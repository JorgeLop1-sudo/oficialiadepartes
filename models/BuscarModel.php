<?php
class BuscarModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function buscarPorNumeroDocumento($numero_documento) {
        $sql = "SELECT o.*, a_derivada.nombre as area_derivada_nombre, u_derivado.nombre as usuario_derivado_nombre
                FROM oficios o
                LEFT JOIN areas a_derivada ON o.area_derivada_id = a_derivada.id
                LEFT JOIN login u_derivado ON o.usuario_derivado_id = u_derivado.id
                WHERE o.numero_documento = ? AND o.activo = 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $numero_documento);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
}