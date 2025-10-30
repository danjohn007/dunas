<?php
/**
 * Test simple de configuración Shelly
 * Verificar que las constantes estén definidas correctamente
 */

require_once __DIR__ . '/../config/config.php';

echo "<h1>🔧 Test de Configuración Shelly</h1>";

// Verificar que todas las constantes estén definidas
$constants = [
    'SHELLY_API_URL',
    'SHELLY_USERNAME', 
    'SHELLY_PASSWORD',
    'SHELLY_API_TIMEOUT',
    'SHELLY_SWITCH_ID',
    'SHELLY_ENABLED',
    'SHELLY_OPEN_URL',
    'SHELLY_CLOSE_URL'
];

echo "<h2>📋 Verificación de constantes</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Constante</th><th>Definida</th><th>Valor</th></tr>";

foreach ($constants as $constant) {
    $defined = defined($constant);
    $value = $defined ? constant($constant) : 'NO DEFINIDA';
    
    // Ocultar contraseña parcialmente
    if ($constant === 'SHELLY_PASSWORD' && $defined) {
        $value = str_repeat('*', strlen($value) - 2) . substr($value, -2);
    }
    
    $status = $defined ? '✅' : '❌';
    $color = $defined ? '#d4edda' : '#f8d7da';
    
    echo "<tr style='background: $color;'>";
    echo "<td><strong>$constant</strong></td>";
    echo "<td>$status</td>";
    echo "<td><code>$value</code></td>";
    echo "</tr>";
}

echo "</table>";

// Solo continuar si todas las constantes están definidas
$allDefined = array_reduce($constants, function($carry, $const) {
    return $carry && defined($const);
}, true);

if (!$allDefined) {
    echo "<div style='background: #f8d7da; border: 1px solid #f1aeb5; padding: 20px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>❌ Error de configuración</h3>";
    echo "<p>Algunas constantes no están definidas. Revise el archivo <code>config/config.php</code></p>";
    echo "</div>";
    exit;
}

echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
echo "<h3>✅ Todas las constantes están definidas correctamente</h3>";
echo "</div>";

// Test básico de conectividad
echo "<h2>🌐 Test de conectividad básica</h2>";

$testUrl = SHELLY_API_URL . "/shelly";
echo "<p><strong>Probando URL:</strong> <code>$testUrl</code></p>";
echo "<p><strong>Con credenciales:</strong> " . SHELLY_USERNAME . ":" . str_repeat('*', strlen(SHELLY_PASSWORD)) . "</p>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, SHELLY_USERNAME . ':' . SHELLY_PASSWORD);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
curl_close($ch);

if (!$error && $httpCode == 200) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h3>✅ Conectividad OK</h3>";
    echo "<p><strong>Código HTTP:</strong> $httpCode</p>";
    echo "<p><strong>Tiempo de respuesta:</strong> " . round($totalTime, 3) . "s</p>";
    echo "<p><strong>Respuesta recibida:</strong> " . strlen($response) . " caracteres</p>";
    echo "</div>";
    
    // Test de los comandos específicos
    echo "<h2>🧪 Test de comandos Shelly</h2>";
    
    // Test comando ABRIR
    echo "<h3>🔓 Test comando ABRIR</h3>";
    echo "<p><strong>URL:</strong> <code>" . SHELLY_OPEN_URL . "</code></p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, SHELLY_OPEN_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, SHELLY_USERNAME . ':' . SHELLY_PASSWORD);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    curl_close($ch);
    
    if (!$error && $httpCode == 200) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4>✅ Comando ABRIR exitoso</h4>";
        echo "<p><strong>Código HTTP:</strong> $httpCode</p>";
        echo "<p><strong>Tiempo:</strong> " . round($totalTime, 3) . "s</p>";
        echo "<p><strong>Respuesta:</strong> <code>$response</code></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f1aeb5; padding: 15px; border-radius: 5px;'>";
        echo "<h4>❌ Error en comando ABRIR</h4>";
        echo "<p><strong>Código HTTP:</strong> $httpCode</p>";
        echo "<p><strong>Error:</strong> " . ($error ?: 'Sin error cURL') . "</p>";
        echo "<p><strong>Respuesta:</strong> <code>$response</code></p>";
        echo "</div>";
    }
    
    sleep(2); // Pausa entre comandos
    
    // Test comando CERRAR
    echo "<h3>🔒 Test comando CERRAR</h3>";
    echo "<p><strong>URL:</strong> <code>" . SHELLY_CLOSE_URL . "</code></p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, SHELLY_CLOSE_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, SHELLY_USERNAME . ':' . SHELLY_PASSWORD);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    curl_close($ch);
    
    if (!$error && $httpCode == 200) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4>✅ Comando CERRAR exitoso</h4>";
        echo "<p><strong>Código HTTP:</strong> $httpCode</p>";
        echo "<p><strong>Tiempo:</strong> " . round($totalTime, 3) . "s</p>";
        echo "<p><strong>Respuesta:</strong> <code>$response</code></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f1aeb5; padding: 15px; border-radius: 5px;'>";
        echo "<h4>❌ Error en comando CERRAR</h4>";
        echo "<p><strong>Código HTTP:</strong> $httpCode</p>";
        echo "<p><strong>Error:</strong> " . ($error ?: 'Sin error cURL') . "</p>";
        echo "<p><strong>Respuesta:</strong> <code>$response</code></p>";
        echo "</div>";
    }
    
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f1aeb5; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Error de conectividad</h3>";
    echo "<p><strong>Código HTTP:</strong> $httpCode</p>";
    echo "<p><strong>Error cURL:</strong> " . ($error ?: 'Sin error') . "</p>";
    echo "<p><strong>Posibles causas:</strong></p>";
    echo "<ul>";
    echo "<li>Port forwarding no configurado correctamente</li>";
    echo "<li>Shelly no accesible desde internet</li>";
    echo "<li>Firewall bloqueando la conexión</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<h2>📝 Siguiente paso</h2>";
echo "<p>Si todos los tests son exitosos, el sistema ya debería funcionar en:</p>";
echo "<ul>";
echo "<li><strong>Registrar Entrada:</strong> <a href='https://fix360.app/dunas/dunasshelly/public/access/create' target='_blank'>https://fix360.app/dunas/dunasshelly/public/access/create</a></li>";
echo "<li><strong>Registrar Salida:</strong> En cualquier registro de acceso activo</li>";
echo "</ul>";

echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }";
echo "table { border-collapse: collapse; width: 100%; margin: 20px 0; }";
echo "th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }";
echo "th { background-color: #f2f2f2; }";
echo "code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; font-family: monospace; }";
echo "h1, h2, h3 { color: #333; }";
echo "</style>";
?>