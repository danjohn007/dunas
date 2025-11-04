<?php
/**
 * Sistema de Control de Acceso con IoT
 * Punto de entrada principal
 */

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

// Iniciar sesión
Session::start();

// Configuración de errores para producción
// No mostrar errores en la interfaz de usuario
ini_set('display_errors', '0');
// Registrar errores excepto deprecados y notices
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

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
    'profile' => 'ProfileController',
    'settings' => 'SettingsController',
    'access-denied' => 'HomeController',
];

// Determinar controlador y acción
if ($controller === 'logout') {
    $controllerClass = 'AuthController';
    $action = 'logout';
} elseif ($controller === 'login') {
    $controllerClass = 'AuthController';
    // For login route: use 'login' action for POST, 'index' for GET
    $action = ($_SERVER['REQUEST_METHOD'] === 'POST') ? 'login' : 'index';
} elseif (isset($controllerMap[$controller])) {
    $controllerClass = $controllerMap[$controller];
} else {
    // Controller not found in map - show 404
    $controllerClass = 'HomeController';
    $action = 'notFound';
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

// If the requested action doesn't exist and we're not already showing 404, show 404
if (!method_exists($controllerInstance, $action)) {
    if ($action !== 'notFound') {
        $controllerClass = 'HomeController';
        $action = 'notFound';
        if (!($controllerInstance instanceof HomeController)) {
            require_once APP_PATH . '/controllers/HomeController.php';
            $controllerInstance = new HomeController();
        }
    } else {
        // Fallback to index if notFound doesn't exist (shouldn't happen)
        $action = 'index';
    }
}

call_user_func_array([$controllerInstance, $action], $params);
