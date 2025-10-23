<?php

require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/controllers/LoginController.php';
require_once __DIR__ . '/controllers/HomeDashController.php';
require_once __DIR__ . '/controllers/AreasAdminController.php';
require_once __DIR__ . '/controllers/UsersAdminController.php';
require_once __DIR__ . '/controllers/ExpedientesController.php';
require_once __DIR__ . '/controllers/ConfigController.php';
require_once __DIR__ . '/controllers/RegistrarController.php';
require_once __DIR__ . '/controllers/BuscarController.php';
require_once __DIR__ . '/controllers/ResponderOficioController.php';
require_once __DIR__ . '/controllers/HistorialController.php';

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
    case 'expedientes':
        (new ExpedientesController())->expedientes();
        break;
    case 'registrar':
        (new RegistrarController())->registro();
        break;
    case 'buscar':
        (new BuscarController())->index();
        break;
    case 'buscarOficio':
        (new BuscarController())->buscar();
        break;
    case 'responder':
        (new ResponderOficioController())->responder();
        break;
    case 'config':
        (new ConfigController())->config();
        break;
    case 'historial':
        (new HistorialController())->historial();
        break;
    
    default:
        (new LoginController())->login();
        break;
}
?>