<?php
/**
 * Script para verificar configuraciones de Shelly en base de datos
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/Settings.php';

echo "<h1>Verificación de configuraciones Shelly</h1>";

// Mostrar constantes definidas
echo "<h2>Constantes definidas en config.php:</h2>";
echo "<strong>SHELLY_API_URL:</strong> " . (defined('SHELLY_API_URL') ? SHELLY_API_URL : 'NO DEFINIDA') . "<br>";
echo "<strong>SHELLY_OPEN_URL:</strong> " . (defined('SHELLY_OPEN_URL') ? SHELLY_OPEN_URL : 'NO DEFINIDA') . "<br>";
echo "<strong>SHELLY_CLOSE_URL:</strong> " . (defined('SHELLY_CLOSE_URL') ? SHELLY_CLOSE_URL : 'NO DEFINIDA') . "<br>";

// Verificar configuraciones en base de datos
try {
    $settings = new Settings();
    $allSettings = $settings->getAll();
    
    echo "<h2>Configuraciones en base de datos:</h2>";
    
    $shellyKeys = [
        'shelly_api_url',
        'shelly_open_url', 
        'shelly_close_url',
        'shelly_relay_open',
        'shelly_relay_close'
    ];
    
    $found = false;
    foreach ($shellyKeys as $key) {
        if (isset($allSettings[$key])) {
            echo "<strong>$key:</strong> " . $allSettings[$key] . "<br>";
            $found = true;
        }
    }
    
    if (!$found) {
        echo "No se encontraron configuraciones de Shelly en la base de datos.<br>";
        echo "El sistema usará las constantes definidas en config.php.<br>";
    }
    
    echo "<h2>Todas las configuraciones en BD:</h2>";
    echo "<pre>";
    print_r($allSettings);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<strong style='color: red;'>Error al consultar base de datos:</strong> " . $e->getMessage();
}

// Simular lo que hace ShellyAPI::getSettings()
echo "<h2>Configuraciones que usará ShellyAPI:</h2>";
try {
    $settings = new Settings();
    $allSettings = $settings->getAll();
    
    $finalSettings = [
        'api_url' => $allSettings['shelly_api_url'] ?? (defined('SHELLY_API_URL') ? SHELLY_API_URL : 'NO DEFINIDA'),
        'open_url' => $allSettings['shelly_open_url'] ?? (defined('SHELLY_OPEN_URL') ? SHELLY_OPEN_URL : 'NO DEFINIDA'),
        'close_url' => $allSettings['shelly_close_url'] ?? (defined('SHELLY_CLOSE_URL') ? SHELLY_CLOSE_URL : 'NO DEFINIDA'),
    ];
    
    echo "<pre>";
    print_r($finalSettings);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<strong style='color: red;'>Error:</strong> " . $e->getMessage();
}

?>