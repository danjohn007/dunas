<?php
/**
 * Sistema de Control de Acceso con IoT
 * Punto de entrada principal
 */

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

// Iniciar sesión
Session::start();

// Obtener la URI solicitada
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$requestUri = str_replace($scriptName, '', $requestUri);
$requestUri = trim($requestUri, '/');
$requestUri = strtok($requestUri, '?');

// Parsear ruta
$segments = explode('/', $requestUri);
$controller = !empty($segments[0]) ? $segments[0] : 'home';
$action = isset($segments[1]) ? $segments[1] : 'index';
$params = array_slice($segments, 2);

// Mapeo de controladores
$controllerMap = [
    'home' => 'HomeController',
    'login' => 'AuthController',
    'logout' => 'AuthController',
    'dashboard' => 'DashboardController',
    'users' => 'UserController',
    'clients' => 'ClientController',
    'units' => 'UnitController',
    'drivers' => 'DriverController',
    'access' => 'AccessController',
    'transactions' => 'TransactionController',
    'reports' => 'ReportController',
    'access-denied' => 'HomeController',
];

// Determinar controlador y acción
if ($controller === 'logout') {
    $controllerClass = 'AuthController';
    $action = 'logout';
} else {
    $controllerClass = $controllerMap[$controller] ?? 'HomeController';
}

$controllerFile = APP_PATH . '/controllers/' . $controllerClass . '.php';

if (!file_exists($controllerFile)) {
    $controllerClass = 'HomeController';
    $action = 'notFound';
    $controllerFile = APP_PATH . '/controllers/HomeController.php';
}

require_once $controllerFile;

// Instanciar y ejecutar controlador
$controllerInstance = new $controllerClass();

if (!method_exists($controllerInstance, $action)) {
    $action = 'index';
}

call_user_func_array([$controllerInstance, $action], $params);
