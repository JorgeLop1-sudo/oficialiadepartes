<?php

require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/controllers/LoginController.php';
require_once __DIR__ . '/controllers/HomeDashController.php';
require_once __DIR__ . '/controllers/AreasAdminController.php';
require_once __DIR__ . '/controllers/UsersAdminController.php';
require_once __DIR__ . '/controllers/ConfigController.php';

$action = $_GET['action'] ?? 'login';

switch($action){
    case 'login':
        (new LoginController())->login();
        break;
    case 'logout':
        (new LoginController())->logout();
        break;
    case 'homedash':
        (new HomeDashController())->dash();
        break;
    case 'areasadmin':
        (new AreasAdminController())->areas();
        break;
    case 'usersadmin':
        (new UsersAdminController())->users();
        break;

    case 'config':
        (new ConfigController())->config();
        break;
    
    default:
        (new LoginController())->login();
        break;
}
?>