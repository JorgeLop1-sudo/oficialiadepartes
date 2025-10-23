<?php

require_once __DIR__ . '/../models/Usuario.php';

class LoginController {
    private $usuarioModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->usuarioModel = new Usuario();
    }

    public function login() {
        
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        $identificador = "";
        $pass = "";
        $error = "";
        $login_type = "users";
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identificador = $_POST['identificador'] ?? '';
            $password = $_POST['password'] ?? '';
            $login_type = $_POST['login_type'] ?? 'users';

            $res = $this->usuarioModel->login($identificador, $password, $login_type);

            if ($res['success']) {
                $user = $res['user'];
                $_SESSION['id'] = $user['id'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
                $_SESSION['usuario_id'] = $user['id']; 

                
                $this->redirectByUserType($user['tipo_usuario']);
            } else {
                // Mensaje genérico por seguridad
                $error = "Credenciales incorrectas o usuario inactivo";
                $error = ($res['reason'] === 'not_found') ? 
                    (($login_type === 'email') ? "Correo no encontrado" : "Usuario no encontrado") : 
                    "Contraseña incorrecta";
            }
        }
        
        include __DIR__ . '/../views/login.php';
    }

    private function redirectByUserType($tipo_usuario) {
        switch ($tipo_usuario) {
            case 'Administrador':
                header("Location: index.php?action=homedash");
                break;
            case 'Guardia':
                header("Location: index.php?action=registrar");
                break;
            case 'Usuario':
            default:
                header("Location: index.php?action=homedash");
                break;
        }
        exit;
    }

    public function dashboard() {
        if (!isset($_SESSION['id'])) {
            header("Location: index.php?action=login");
            exit;
        }
        
        if (isset($_SESSION['tipo_usuario'])) {
            $this->redirectByUserType($_SESSION['tipo_usuario']);
        } else {
            header("Location: index.php?action=homedash");
        }
        exit;
    }

    public function logout() {
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        
        header("Location: index.php?action=login");
        exit();
    }
}

?>