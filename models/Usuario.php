<?php
require_once __DIR__ . '/../config/database.php';

class Usuario {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    private function isHashed($hash) {
        return is_string($hash) && strlen($hash) === 60 && (strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0 || strpos($hash, '$2b$') === 0);
    }

    public function getByUsuario($usuario) {
        $stmt = $this->conn->prepare("SELECT * FROM login WHERE BINARY usuario = ? LIMIT 1");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM login WHERE BINARY email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function login($identificador, $password, $type = 'users') {
        if ($type === 'email') {
            $user = $this->getByEmail($identificador);
        } else {
            $user = $this->getByUsuario($identificador);
        }

        if (!$user) {
            return ['success' => false, 'reason' => 'not_found'];
        }

        $stored = $user['password'] ?? '';

        if ($this->isHashed($stored)) {
            if (password_verify($password, $stored)) {
                unset($user['password']);
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'reason' => 'bad_password'];
            }
        } else {
            if ($password === $stored) {
                unset($user['password']);
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'reason' => 'bad_password'];
            }
        }
    }
}
?>