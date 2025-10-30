<?php
/**
 * Test de Shelly usando puerto 9000 configurado en router
 * Formato exacto que funciona localmente pero con puerto externo
 */

// Incluir configuración
require_once '../config/config.php';

echo "<h1>Prueba Shelly Puerto 9000</h1>";
echo "<h2>Configuración Actual:</h2>";

// Mostrar configuración detectada
echo "<p><strong>SHELLY_API_URL:</strong> " . SHELLY_API_URL . "</p>";
echo "<p><strong>SHELLY_OPEN_URL:</strong> " . SHELLY_OPEN_URL . "</p>";
echo "<p><strong>SHELLY_CLOSE_URL:</strong> " . SHELLY_CLOSE_URL . "</p>";
echo "<p><strong>Usuario:</strong> " . SHELLY_USERNAME . "</p>";
echo "<p><strong>Contraseña:</strong> " . SHELLY_PASSWORD . "</p>";

echo "<hr>";

// Función para hacer prueba con autenticación
function testShellyConnection($url, $username, $password, $action) {
    echo "<h3>Probando: $action</h3>";
    echo "<p><strong>URL:</strong> $url</p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyAPI/1.0');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $start_time = microtime(true);
    $response = curl_exec($ch);
    $end_time = microtime(true);
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $curl_info = curl_getinfo($ch);
    
    curl_close($ch);
    
    $duration = round(($end_time - $start_time) * 1000, 2);
    
    echo "<div style='background: " . ($http_code == 200 ? '#d4edda' : '#f8d7da') . "; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<p><strong>Código HTTP:</strong> $http_code</p>";
    echo "<p><strong>Tiempo:</strong> {$duration}ms</p>";
    
    if ($error) {
        echo "<p><strong>Error cURL:</strong> $error</p>";
    }
    
    if ($response) {
        echo "<p><strong>Respuesta:</strong></p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        // Verificar si es JSON válido
        $json_data = json_decode($response, true);
        if ($json_data) {
            echo "<p><strong>JSON decodificado:</strong></p>";
            echo "<pre>" . print_r($json_data, true) . "</pre>";
        }
    }
    
    echo "<p><strong>Info detallada de conexión:</strong></p>";
    echo "<pre>" . print_r($curl_info, true) . "</pre>";
    echo "</div>";
    
    return $http_code == 200;
}

// Probar conexión básica primero
echo "<h2>1. Prueba de Conexión Básica</h2>";
$basic_url = SHELLY_API_URL . "/rpc/Sys.GetStatus";
testShellyConnection($basic_url, SHELLY_USERNAME, SHELLY_PASSWORD, "Estado del Sistema");

echo "<h2>2. Prueba de Apertura de Barrera</h2>";
testShellyConnection(SHELLY_OPEN_URL, SHELLY_USERNAME, SHELLY_PASSWORD, "Abrir Barrera (on=false)");

echo "<h2>3. Esperando 3 segundos...</h2>";
echo "<script>setTimeout(function() { window.location.reload(); }, 3000);</script>";
flush();
sleep(3);

echo "<h2>4. Prueba de Cierre de Barrera</h2>";
testShellyConnection(SHELLY_CLOSE_URL, SHELLY_USERNAME, SHELLY_PASSWORD, "Cerrar Barrera (on=true)");

echo "<hr>";
echo "<h2>Comandos equivalentes en terminal que funcionan:</h2>";
echo "<pre>";
echo 'curl -u admin:67da6c "http://192.168.1.95/rpc/Switch.Set?id=0&on=false"  # Abrir' . "\n";
echo 'curl -u admin:67da6c "http://192.168.1.95/rpc/Switch.Set?id=0&on=true"   # Cerrar' . "\n";
echo "</pre>";

echo "<h2>URLs que estamos probando con puerto 9000:</h2>";
echo "<pre>";
echo "Abrir: " . SHELLY_OPEN_URL . "\n";
echo "Cerrar: " . SHELLY_CLOSE_URL . "\n";
echo "</pre>";

// Incluir ShellyAPI helper para prueba adicional
require_once '../app/helpers/ShellyAPI.php';

echo "<h2>5. Prueba usando ShellyAPI Helper</h2>";
try {
    $shellyAPI = new ShellyAPI();
    
    echo "<h3>Intentando abrir barrera...</h3>";
    $result_open = $shellyAPI->openBarrier();
    echo "<p>Resultado apertura: " . ($result_open ? "✅ ÉXITO" : "❌ FALLO") . "</p>";
    
    sleep(2);
    
    echo "<h3>Intentando cerrar barrera...</h3>";
    $result_close = $shellyAPI->closeBarrier();
    echo "<p>Resultado cierre: " . ($result_close ? "✅ ÉXITO" : "❌ FALLO") . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error en ShellyAPI: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Prueba completada. Si no funciona, verifica que el puerto 9000 esté configurado correctamente en tu router para reenviar a 192.168.1.95:80</em></p>";
?>