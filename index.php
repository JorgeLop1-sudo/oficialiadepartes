<?php

require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/controllers/LoginController.php';
require_once __DIR__ . '/controllers/HomeDashController.php';

$action = $_GET['action'] ?? 'login';

switch($action){
    case 'login':
        (new LoginController())->login();
        break;
    
    case 'homedash':
        (new HomeDashController())->dash();
        break;
    
    default:
        (new LoginController())->login();
        break;
}

?>