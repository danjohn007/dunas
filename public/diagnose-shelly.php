<?php
/**
 * Diagnóstico detallado del Shelly Pro 4PM
 */

require_once __DIR__ . '/../config/config.php';

echo "<h1>🔧 Diagnóstico detallado del Shelly Pro 4PM</h1>";

// Función para hacer peticiones con más detalle
function detailedShellyRequest($url, $description) {
    echo "<h3>📡 $description</h3>";
    echo "<p><strong>URL:</strong> $url</p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    // Capturar información verbose
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    $start = microtime(true);
    $response = curl_exec($ch);
    $time = microtime(true) - $start;
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Obtener información verbose
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    fclose($verbose);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><td><strong>HTTP Code</strong></td><td>$httpCode</td></tr>";
    echo "<tr><td><strong>Tiempo</strong></td><td>" . round($time * 1000, 2) . "ms</td></tr>";
    
    if ($error) {
        echo "<tr><td><strong>Error</strong></td><td style='color: red;'>$error</td></tr>";
    }
    
    if ($response) {
        $decoded = json_decode($response, true);
        if ($decoded) {
            echo "<tr><td><strong>Respuesta JSON</strong></td><td><pre>" . json_encode($decoded, JSON_PRETTY_PRINT) . "</pre></td></tr>";
        } else {
            echo "<tr><td><strong>Respuesta RAW</strong></td><td><pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre></td></tr>";
        }
    }
    
    echo "</table>";
    
    return [
        'success' => !$error && $httpCode == 200,
        'response' => $response,
        'decoded' => $decoded ?? null,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

// 1. Información del dispositivo
$deviceInfo = detailedShellyRequest(SHELLY_API_URL . 'shelly', 'Información del dispositivo');

// 2. Estado general
$status = detailedShellyRequest(SHELLY_API_URL . 'status', 'Estado general del dispositivo');

// 3. Estado específico de todos los switches
for ($i = 0; $i <= 3; $i++) {
    $switchStatus = detailedShellyRequest(SHELLY_API_URL . "rpc/Switch.GetStatus?id=$i", "Estado del Switch $i");
}

// 4. Configuración de todos los switches
for ($i = 0; $i <= 3; $i++) {
    $switchConfig = detailedShellyRequest(SHELLY_API_URL . "rpc/Switch.GetConfig?id=$i", "Configuración del Switch $i");
}

// 5. Probar comando directo en Switch 0
echo "<h2>🧪 Pruebas de comandos directos</h2>";

echo "<h3>🔓 Comando ABRIR (Switch 0 = false)</h3>";
$openTest = detailedShellyRequest(SHELLY_OPEN_URL, 'Comando para abrir barrera');

echo "<h3>🔒 Comando CERRAR (Switch 0 = true)</h3>";
$closeTest = detailedShellyRequest(SHELLY_CLOSE_URL, 'Comando para cerrar barrera');

// 6. Verificar si hay otros switches que podrían estar interfiriendo
echo "<h2>🔍 Análisis de configuración</h2>";

if ($deviceInfo['decoded']) {
    $device = $deviceInfo['decoded'];
    echo "<h4>📋 Información del dispositivo:</h4>";
    echo "<ul>";
    echo "<li><strong>Tipo:</strong> " . ($device['type'] ?? 'N/A') . "</li>";
    echo "<li><strong>MAC:</strong> " . ($device['mac'] ?? 'N/A') . "</li>";
    echo "<li><strong>Firmware:</strong> " . ($device['fw'] ?? 'N/A') . "</li>";
    echo "<li><strong>Modelo:</strong> " . ($device['model'] ?? 'N/A') . "</li>";
    echo "</ul>";
}

// 7. Recomendaciones basadas en los resultados
echo "<h2>💡 Análisis y recomendaciones</h2>";

if ($openTest['success'] && $closeTest['success']) {
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
    echo "<h4>⚠️ Los comandos se ejecutan pero el switch no responde físicamente</h4>";
    echo "<p><strong>Posibles causas:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Switch incorrecto:</strong> Estás enviando comandos al Switch 0, pero tu relé físico podría estar en otro canal (1, 2, o 3)</li>";
    echo "<li><strong>Configuración de salida:</strong> El switch está configurado como entrada en lugar de salida</li>";
    echo "<li><strong>Modo de operación:</strong> El switch está en modo detached o no está configurado para control directo</li>";
    echo "<li><strong>Hardware:</strong> El relé físico está dañado o desconectado</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h4>🔧 Próximos pasos:</h4>";
    echo "<ol>";
    echo "<li><strong>Probar todos los switches:</strong> Ejecuta comandos en Switch 1, 2 y 3 para ver cuál controla tu barrera</li>";
    echo "<li><strong>Verificar configuración física:</strong> Asegúrate de que el cable esté conectado al relé correcto</li>";
    echo "<li><strong>Revisar configuración del switch:</strong> Verifica que esté configurado como 'relay' y no como 'switch'</li>";
    echo "</ol>";
    
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f1aeb5; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ Los comandos no se están ejecutando correctamente</h4>";
    echo "<p>Revisa los errores HTTP arriba para más detalles.</p>";
    echo "</div>";
}

// 8. Crear botones de prueba para todos los switches
echo "<h2>🎮 Panel de pruebas manual</h2>";
echo "<p>Haz clic en estos botones para probar cada switch manualmente:</p>";

echo "<div style='display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 20px 0;'>";

for ($i = 0; $i <= 3; $i++) {
    echo "<div style='text-align: center; border: 1px solid #ddd; padding: 10px; border-radius: 5px;'>";
    echo "<h4>Switch $i</h4>";
    echo "<a href='?test_switch=$i&action=on' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; display: inline-block; margin: 2px;'>ON</a><br>";
    echo "<a href='?test_switch=$i&action=off' style='background: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; display: inline-block; margin: 2px;'>OFF</a>";
    echo "</div>";
}

echo "</div>";

// Procesar pruebas manuales
if (isset($_GET['test_switch']) && isset($_GET['action'])) {
    $switch = (int)$_GET['test_switch'];
    $action = $_GET['action'] === 'on' ? 'true' : 'false';
    $testUrl = SHELLY_API_URL . "rpc/Switch.Set?id=$switch&on=$action";
    
    echo "<hr>";
    echo "<h3>🧪 Resultado de prueba manual</h3>";
    $manualTest = detailedShellyRequest($testUrl, "Prueba manual: Switch $switch = $action");
    
    if ($manualTest['success']) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>✅ Comando ejecutado correctamente en Switch $switch</strong>";
        echo "<p>Si este switch controló tu barrera, actualiza la configuración para usar Switch $switch en lugar de Switch 0.</p>";
        echo "</div>";
    }
}

echo "<h3>📝 Configuración actual del sistema:</h3>";
echo "<pre>";
echo "SHELLY_OPEN_URL: " . SHELLY_OPEN_URL . "\n";
echo "SHELLY_CLOSE_URL: " . SHELLY_CLOSE_URL . "\n";
echo "</pre>";
?>