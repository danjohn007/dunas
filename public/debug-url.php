<?php
/**
 * Script de debug para verificar BASE_URL
 */
require_once __DIR__ . '/../config/config.php';

echo "<h1>Debug BASE_URL</h1>";
echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";
echo "<p><strong>SERVER vars:</strong></p>";
echo "<pre>";
var_dump([
    'HTTP_HOST' => $_SERVER['HTTP_HOST'],
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'],
    'REQUEST_URI' => $_SERVER['REQUEST_URI'],
    'HTTPS' => $_SERVER['HTTPS'] ?? 'not set',
    'SERVER_PORT' => $_SERVER['SERVER_PORT']
]);
echo "</pre>";

echo "<p><strong>Expected compare URL:</strong> " . BASE_URL . "/api/compare_plate.php</p>";

// Verificar si el archivo exists
$compareFile = __DIR__ . '/api/compare_plate.php';
echo "<p><strong>Compare file exists:</strong> " . (file_exists($compareFile) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Compare file path:</strong> " . $compareFile . "</p>";
?>