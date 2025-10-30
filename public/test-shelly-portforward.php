<?php
/**
 * Script de prueba para Shelly con Port Forwarding
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/ShellyAPI.php';

echo "<h1>🔧 Prueba de Shelly con Port Forwarding</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Mostrar configuración actual
echo "<h2>📋 Configuración actual:</h2>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Configuración</th><th>Valor</th></tr>";
echo "<tr><td>SHELLY_ENABLED</td><td>" . (SHELLY_ENABLED ? '✅ Habilitado' : '❌ Deshabilitado') . "</td></tr>";
echo "<tr><td>SHELLY_API_URL</td><td>" . SHELLY_API_URL . "</td></tr>";
echo "<tr><td>SHELLY_OPEN_URL</td><td>" . SHELLY_OPEN_URL . "</td></tr>";
echo "<tr><td>SHELLY_CLOSE_URL</td><td>" . SHELLY_CLOSE_URL . "</td></tr>";
echo "</table>";

if (!SHELLY_ENABLED) {
    echo "<p style='color: red;'><strong>⚠️ Shelly está deshabilitado. Habilítalo en config.php para probar.</strong></p>";
    exit;
}

// Prueba 1: Conectividad básica
echo "<h2>🌐 Prueba 1: Conectividad básica</h2>";

$basicTests = [
    'Página principal' => SHELLY_API_URL,
    'Status endpoint' => rtrim(SHELLY_API_URL, '/') . '/status',
    'Shelly info' => rtrim(SHELLY_API_URL, '/') . '/shelly',
    'Switch status' => rtrim(SHELLY_API_URL, '/') . '/rpc/Switch.GetStatus?id=0'
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Prueba</th><th>Resultado</th><th>Tiempo</th><th>Detalles</th></tr>";

foreach ($basicTests as $testName => $testUrl) {
    $start = microtime(true);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyAPI/1.0 Test');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    curl_close($ch);
    
    $time = round($totalTime * 1000, 2);
    
    if ($error) {
        echo "<tr style='background-color: #ffebee;'>";
        echo "<td>$testName</td>";
        echo "<td style='color: red;'>❌ Error</td>";
        echo "<td>{$time}ms</td>";
        echo "<td style='font-size: 12px;'>$error</td>";
        echo "</tr>";
    } elseif ($httpCode == 200) {
        echo "<tr style='background-color: #e8f5e8;'>";
        echo "<td>$testName</td>";
        echo "<td style='color: green;'>✅ HTTP $httpCode</td>";
        echo "<td>{$time}ms</td>";
        echo "<td style='font-size: 12px;'>Conectado correctamente</td>";
        echo "</tr>";
    } else {
        echo "<tr style='background-color: #fff3e0;'>";
        echo "<td>$testName</td>";
        echo "<td style='color: orange;'>⚠️ HTTP $httpCode</td>";
        echo "<td>{$time}ms</td>";
        echo "<td style='font-size: 12px;'>Código inesperado</td>";
        echo "</tr>";
    }
}

echo "</table>";

// Prueba 2: Funciones de ShellyAPI
echo "<h2>🔧 Prueba 2: Funciones de ShellyAPI</h2>";

try {
    // Test de apertura
    echo "<h3>🔓 Probando apertura de barrera...</h3>";
    $openResult = ShellyAPI::openBarrier();
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    print_r($openResult);
    echo "</pre>";
    
    if ($openResult['success']) {
        echo "<p style='color: green;'><strong>✅ Apertura exitosa!</strong></p>";
        
        // Esperar 3 segundos antes de cerrar
        echo "<p>Esperando 3 segundos antes de cerrar...</p>";
        sleep(3);
        
        // Test de cierre
        echo "<h3>🔒 Probando cierre de barrera...</h3>";
        $closeResult = ShellyAPI::closeBarrier();
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        print_r($closeResult);
        echo "</pre>";
        
        if ($closeResult['success']) {
            echo "<p style='color: green;'><strong>✅ Cierre exitoso!</strong></p>";
            echo "<div style='background: #4CAF50; color: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h3>🎉 ¡PRUEBA COMPLETADA EXITOSAMENTE!</h3>";
            echo "<p>Tu Shelly está funcionando correctamente con Port Forwarding.</p>";
            echo "<p><strong>Ahora puedes usar el sistema normalmente:</strong></p>";
            echo "<ul>";
            echo "<li>✅ Las entradas abrirán la barrera automáticamente</li>";
            echo "<li>✅ Las salidas cerrarán la barrera automáticamente</li>";
            echo "<li>✅ Los tiempos de respuesta son: " . 
                 (isset($openResult['response_time']) ? round($openResult['response_time'] * 1000) . "ms" : "N/A") . 
                 " apertura, " . 
                 (isset($closeResult['response_time']) ? round($closeResult['response_time'] * 1000) . "ms" : "N/A") . 
                 " cierre</li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'><strong>❌ Error en el cierre</strong></p>";
        }
    } else {
        echo "<p style='color: red;'><strong>❌ Error en la apertura</strong></p>";
        echo "<p><strong>Posibles causas:</strong></p>";
        echo "<ul>";
        echo "<li>El port forwarding no está configurado correctamente</li>";
        echo "<li>El dispositivo Shelly está offline</li>";
        echo "<li>Tu IP pública cambió</li>";
        echo "<li>Hay un firewall bloqueando la conexión</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Error general: " . $e->getMessage() . "</strong></p>";
}

// Prueba 3: Status del dispositivo
echo "<h2>📊 Prueba 3: Status del dispositivo</h2>";

try {
    $statusResult = ShellyAPI::getStatus();
    if ($statusResult['success']) {
        echo "<h4>✅ Status obtenido correctamente:</h4>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        print_r($statusResult['data']);
        echo "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠️ No se pudo obtener el status del dispositivo</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error al obtener status: " . $e->getMessage() . "</p>";
}

// Información adicional
echo "<h2>📝 Información adicional</h2>";
echo "<p><strong>IP del servidor:</strong> " . ($_SERVER['SERVER_ADDR'] ?? 'No disponible') . "</p>";
echo "<p><strong>User Agent:</strong> " . ($_SERVER['HTTP_USER_AGENT'] ?? 'No disponible') . "</p>";
echo "<p><strong>Método de detección de IP:</strong> Automático via servicios públicos</p>";

echo "<h3>🔧 Solución de problemas:</h3>";
echo "<ul>";
echo "<li><strong>Si ves errores de timeout:</strong> Verifica que el port forwarding esté activo</li>";
echo "<li><strong>Si ves HTTP 404:</strong> El Shelly no responde en esa IP</li>";
echo "<li><strong>Si ves HTTP 401:</strong> Necesitas configurar autenticación</li>";
echo "<li><strong>Si todo falla:</strong> Ejecuta <a href='get-public-ip.php'>get-public-ip.php</a> para diagnóstico completo</li>";
echo "</ul>";

echo "<p style='margin-top: 30px;'><em>Prueba completada a las " . date('H:i:s') . "</em></p>";
?>