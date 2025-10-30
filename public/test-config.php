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
    require_once __DIR__ . '/../app/helpers/ShellyAPI.php';
    
    // Usar reflection para acceder al método privado getSettings
    $reflection = new ReflectionClass('ShellyAPI');
    $method = $reflection->getMethod('getSettings');
    $method->setAccessible(true);
    $finalSettings = $method->invoke(null);
    
    echo "<pre>";
    print_r($finalSettings);
    echo "</pre>";
    
    // Verificar que las URLs contengan la IP correcta
    echo "<h2>Verificación de URLs:</h2>";
    if (strpos($finalSettings['open_url'], '192.168.1.95') !== false) {
        echo "<div style='color: green; font-weight: bold;'>✓ open_url contiene IP local 192.168.1.95</div>";
    } else {
        echo "<div style='color: orange; font-weight: bold;'>⚠ open_url NO contiene IP local 192.168.1.95</div>";
    }
    
    if (strpos($finalSettings['close_url'], '192.168.1.95') !== false) {
        echo "<div style='color: green; font-weight: bold;'>✓ close_url contiene IP local 192.168.1.95</div>";
    } else {
        echo "<div style='color: orange; font-weight: bold;'>⚠ close_url NO contiene IP local 192.168.1.95</div>";
    }
    
} catch (Exception $e) {
    echo "<strong style='color: red;'>Error:</strong> " . $e->getMessage();
}

?>