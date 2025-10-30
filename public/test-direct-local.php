<?php
/**
 * Test directo con IP local del Shelly
 * Sin port forwarding - conexión directa
 */

require_once __DIR__ . '/../config/config.php';

echo "<h1>🎯 Test Directo - IP Local Shelly</h1>";

echo "<div style='background: #e7f3ff; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📋 Configuración simplificada:</h3>";
echo "<ul>";
echo "<li><strong>SHELLY_API_URL:</strong> " . SHELLY_API_URL . "</li>";
echo "<li><strong>SHELLY_USERNAME:</strong> " . SHELLY_USERNAME . "</li>";
echo "<li><strong>SHELLY_PASSWORD:</strong> " . str_repeat('*', strlen(SHELLY_PASSWORD)) . "</li>";
echo "<li><strong>SHELLY_OPEN_URL:</strong> " . SHELLY_OPEN_URL . "</li>";
echo "<li><strong>SHELLY_CLOSE_URL:</strong> " . SHELLY_CLOSE_URL . "</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🧪 Pruebas directas con IP local</h2>";

// Test 1: Conectividad básica
echo "<h3>1. 🔍 Test de conectividad básica</h3>";
echo "<div style='padding: 15px; margin: 10px 0; border: 2px solid #007bff; border-radius: 8px; background: #f8f9fa;'>";
echo "<strong>Probando:</strong> <code>http://192.168.1.95/shelly</code><br>";
echo "<strong>Credenciales:</strong> admin:67da6c<br>";
flush();

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://192.168.1.95/shelly");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, "admin:67da6c");
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
curl_close($ch);

if (!$error && $httpCode == 200) {
    echo "<span style='color: green; font-weight: bold;'>✅ CONECTIVIDAD OK</span><br>";
    echo "<strong>Tiempo:</strong> " . round($totalTime, 3) . "s<br>";
    echo "<strong>Respuesta:</strong> " . strlen($response) . " caracteres recibidos<br>";
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ ERROR DE CONECTIVIDAD</span><br>";
    echo "<strong>Código HTTP:</strong> $httpCode<br>";
    if ($error) {
        echo "<strong>Error cURL:</strong> $error<br>";
    }
    echo "<strong>💡 Esto significa:</strong> El servidor web no puede acceder a tu red local<br>";
}
echo "</div>";

// Test 2: Usar ShellyAPI class
echo "<h3>2. 🔧 Test usando ShellyAPI (tu sistema)</h3>";

echo "<h4>🔓 Comando ABRIR barrera</h4>";
echo "<div style='padding: 15px; margin: 10px 0; border: 2px solid #28a745; border-radius: 8px; background: #d4edda;'>";
echo "<strong>Ejecutando:</strong> ShellyAPI::openBarrier()<br>";
flush();

$openResult = ShellyAPI::openBarrier();

if ($openResult['success']) {
    echo "<span style='color: green; font-weight: bold;'>✅ ¡BARRERA ABIERTA!</span><br>";
    if (isset($openResult['data'])) {
        echo "<strong>Respuesta:</strong> " . json_encode($openResult['data']) . "<br>";
    }
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ ERROR AL ABRIR</span><br>";
    echo "<strong>Error:</strong> " . ($openResult['error'] ?? 'Desconocido') . "<br>";
}
echo "</div>";

sleep(2);

echo "<h4>🔒 Comando CERRAR barrera</h4>";
echo "<div style='padding: 15px; margin: 10px 0; border: 2px solid #dc3545; border-radius: 8px; background: #f8d7da;'>";
echo "<strong>Ejecutando:</strong> ShellyAPI::closeBarrier()<br>";
flush();

$closeResult = ShellyAPI::closeBarrier();

if ($closeResult['success']) {
    echo "<span style='color: green; font-weight: bold;'>✅ ¡BARRERA CERRADA!</span><br>";
    if (isset($closeResult['data'])) {
        echo "<strong>Respuesta:</strong> " . json_encode($closeResult['data']) . "<br>";
    }
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ ERROR AL CERRAR</span><br>";
    echo "<strong>Error:</strong> " . ($closeResult['error'] ?? 'Desconocido') . "<br>";
}
echo "</div>";

// Diagnóstico final
echo "<h2>📋 Diagnóstico y soluciones</h2>";

$bothWorking = ($openResult['success'] && $closeResult['success']);

if ($bothWorking) {
    echo "<div style='background: #d4edda; border: 2px solid #c3e6cb; padding: 20px; border-radius: 10px;'>";
    echo "<h3 style='color: #155724;'>🎉 ¡FUNCIONA PERFECTAMENTE!</h3>";
    echo "<p><strong>✅ Tu servidor web SÍ puede acceder al Shelly directamente</strong></p>";
    echo "<p>Esto significa que:</p>";
    echo "<ul>";
    echo "<li>✅ Tu servidor web está en la misma red que el Shelly</li>";
    echo "<li>✅ No necesitas port forwarding</li>";
    echo "<li>✅ El sistema ya está listo para funcionar</li>";
    echo "</ul>";
    
    echo "<h4>🚀 Páginas listas para usar:</h4>";
    echo "<ul>";
    echo "<li><strong>Registrar Entrada:</strong> <a href='https://fix360.app/dunas/dunasshelly/public/access/create' target='_blank'>Crear Acceso</a></li>";
    echo "<li><strong>Ver Accesos:</strong> <a href='https://fix360.app/dunas/dunasshelly/public/access' target='_blank'>Lista de Accesos</a></li>";
    echo "</ul>";
    echo "</div>";
    
} else {
    echo "<div style='background: #f8d7da; border: 2px solid #f1aeb5; padding: 20px; border-radius: 10px;'>";
    echo "<h3 style='color: #721c24;'>❌ El servidor web no puede acceder al Shelly</h3>";
    echo "<p><strong>Problema:</strong> Tu servidor web está en internet pero el Shelly en tu red local.</p>";
    
    echo "<h4>🔧 Soluciones posibles:</h4>";
    echo "<ol>";
    echo "<li><strong>VPN:</strong> Configurar VPN entre el servidor y tu red</li>";
    echo "<li><strong>Proxy local:</strong> Instalar un proxy en tu red que redirija al Shelly</li>";
    echo "<li><strong>Webhook:</strong> El Shelly envía estados al servidor</li>";
    echo "<li><strong>Servidor local:</strong> Mover el sistema a un servidor en tu red local</li>";
    echo "</ol>";
    
    echo "<h4>⚡ Solución rápida - Webhook:</h4>";
    echo "<p>Podemos configurar que el sistema envíe comandos HTTP directos desde el navegador del usuario:</p>";
    echo "<ul>";
    echo "<li>El usuario hace clic en 'Registrar Entrada'</li>";
    echo "<li>JavaScript en el navegador envía comando al Shelly</li>";
    echo "<li>Solo funciona si el usuario está en la misma red WiFi</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<h3>🔍 Información técnica</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace;'>";
echo "<strong>Servidor web:</strong> fix360.app (internet)<br>";
echo "<strong>Shelly:</strong> 192.168.1.95 (red local)<br>";
echo "<strong>Problema:</strong> " . ($bothWorking ? "Ninguno - funciona perfectamente" : "Red diferente - no hay conectividad directa") . "<br>";
echo "<strong>Port forwarding:</strong> " . ($bothWorking ? "No necesario" : "No resuelve el problema") . "<br>";
echo "</div>";

echo "<style>";
echo "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 20px; line-height: 1.6; }";
echo "h1, h2, h3 { color: #333; }";
echo "code { background: #f1f3f4; padding: 2px 6px; border-radius: 4px; font-family: 'Consolas', monospace; }";
echo "</style>";
?>