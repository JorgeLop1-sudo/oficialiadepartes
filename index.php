<?php

require_once __DIR__ . '/controllers/LoginController.php';

$action = $_GET['action'] ?? 'login';

switch($action){
    case 'login':
        (new LoginController())->login();
        break;
}

?>