<?php
/**
 * Test de integración completa Shelly Pro 4PM
 * Verificar que funcione desde el servidor web
 */

require_once __DIR__ . '/../config/config.php';

echo "<h1>🎯 Test de Integración Web - Shelly Pro 4PM</h1>";

echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>✅ Configuración actual:</h3>";
echo "<ul>";
echo "<li><strong>SHELLY_API_URL:</strong> " . SHELLY_API_URL . "</li>";
echo "<li><strong>SHELLY_USERNAME:</strong> " . SHELLY_USERNAME . "</li>";
echo "<li><strong>SHELLY_PASSWORD:</strong> " . str_repeat('*', strlen(SHELLY_PASSWORD)) . " (últimos 6: " . substr(SHELLY_PASSWORD, -6) . ")</li>";
echo "<li><strong>SHELLY_ENABLED:</strong> " . (SHELLY_ENABLED ? 'SÍ' : 'NO') . "</li>";
echo "<li><strong>SHELLY_OPEN_URL:</strong> " . SHELLY_OPEN_URL . "</li>";
echo "<li><strong>SHELLY_CLOSE_URL:</strong> " . SHELLY_CLOSE_URL . "</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🧪 Pruebas en tiempo real</h2>";

// Test 1: Conectividad básica
echo "<h3>1. 🔍 Test de conectividad</h3>";
echo "<div id='test1' style='padding: 15px; margin: 10px 0; border: 2px solid #ddd; border-radius: 8px; background: #f8f9fa;'>";
echo "<strong>Probando acceso básico al Shelly...</strong><br>";
flush();

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, SHELLY_API_URL . "/shelly");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, SHELLY_USERNAME . ':' . SHELLY_PASSWORD);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
curl_close($ch);

if (!$error && $httpCode == 200) {
    echo "✅ <strong style='color: green;'>Conectividad OK</strong> - HTTP 200<br>";
    echo "⏱️ Tiempo de respuesta: " . round($totalTime, 3) . "s<br>";
    echo "📄 Respuesta recibida: " . strlen($response) . " caracteres";
} else {
    echo "❌ <strong style='color: red;'>Error de conectividad</strong><br>";
    echo "🔢 Código HTTP: $httpCode<br>";
    if ($error) {
        echo "⚠️ Error cURL: $error";
    }
}
echo "</div>";

// Test 2: Test usando ShellyAPI class
echo "<h3>2. 🔧 Test usando clase ShellyAPI</h3>";

echo "<h4>🔓 Comando ABRIR barrera</h4>";
echo "<div id='test2a' style='padding: 15px; margin: 10px 0; border: 2px solid #ddd; border-radius: 8px; background: #fff3cd;'>";
echo "<strong>Ejecutando ShellyAPI::openBarrier()...</strong><br>";
flush();

$startTime = microtime(true);
$openResult = ShellyAPI::openBarrier();
$endTime = microtime(true);

if ($openResult['success']) {
    echo "✅ <strong style='color: green;'>¡Barrera ABIERTA exitosamente!</strong><br>";
    echo "⏱️ Tiempo total: " . round(($endTime - $startTime), 3) . "s<br>";
    if (isset($openResult['data'])) {
        echo "📊 Respuesta del Shelly: <code>" . json_encode($openResult['data']) . "</code><br>";
    }
    if (isset($openResult['response_time'])) {
        echo "🌐 Tiempo de red: " . round($openResult['response_time'], 3) . "s";
    }
} else {
    echo "❌ <strong style='color: red;'>Error al abrir barrera</strong><br>";
    echo "🔍 Error: " . ($openResult['error'] ?? 'Error desconocido') . "<br>";
    echo "🔢 Código HTTP: " . ($openResult['http_code'] ?? 'N/A') . "<br>";
    echo "🔗 URL utilizada: " . ($openResult['url'] ?? 'N/A');
}
echo "</div>";

// Pausa de 2 segundos entre comandos
sleep(2);

echo "<h4>🔒 Comando CERRAR barrera</h4>";
echo "<div id='test2b' style='padding: 15px; margin: 10px 0; border: 2px solid #ddd; border-radius: 8px; background: #f8d7da;'>";
echo "<strong>Ejecutando ShellyAPI::closeBarrier()...</strong><br>";
flush();

$startTime = microtime(true);
$closeResult = ShellyAPI::closeBarrier();
$endTime = microtime(true);

if ($closeResult['success']) {
    echo "✅ <strong style='color: green;'>¡Barrera CERRADA exitosamente!</strong><br>";
    echo "⏱️ Tiempo total: " . round(($endTime - $startTime), 3) . "s<br>";
    if (isset($closeResult['data'])) {
        echo "📊 Respuesta del Shelly: <code>" . json_encode($closeResult['data']) . "</code><br>";
    }
    if (isset($closeResult['response_time'])) {
        echo "🌐 Tiempo de red: " . round($closeResult['response_time'], 3) . "s";
    }
} else {
    echo "❌ <strong style='color: red;'>Error al cerrar barrera</strong><br>";
    echo "🔍 Error: " . ($closeResult['error'] ?? 'Error desconocido') . "<br>";
    echo "🔢 Código HTTP: " . ($closeResult['http_code'] ?? 'N/A') . "<br>";
    echo "🔗 URL utilizada: " . ($closeResult['url'] ?? 'N/A');
}
echo "</div>";

// Test 3: Comandos directos (como funcionaron en terminal)
echo "<h3>3. 🎯 Test con comandos directos</h3>";

echo "<h4>🔓 Comando directo ABRIR (como en terminal)</h4>";
echo "<div id='test3a' style='padding: 15px; margin: 10px 0; border: 2px solid #007bff; border-radius: 8px; background: #e7f3ff;'>";
$directOpenUrl = "http://192.168.1.95/rpc/Switch.Set?id=0&on=false";
echo "<strong>URL:</strong> <code>$directOpenUrl</code><br>";
echo "<strong>Credenciales:</strong> admin:67da6c<br>";
flush();

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $directOpenUrl);
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
    echo "✅ <strong style='color: green;'>Comando directo ABRIR funcionó</strong><br>";
    echo "⏱️ Tiempo: " . round($totalTime, 3) . "s<br>";
    echo "📄 Respuesta: <code>$response</code>";
} else {
    echo "❌ <strong style='color: red;'>Error en comando directo ABRIR</strong><br>";
    echo "🔢 HTTP: $httpCode<br>";
    if ($error) echo "⚠️ Error: $error";
}
echo "</div>";

sleep(2);

echo "<h4>🔒 Comando directo CERRAR (como en terminal)</h4>";
echo "<div id='test3b' style='padding: 15px; margin: 10px 0; border: 2px solid #dc3545; border-radius: 8px; background: #f8d7da;'>";
$directCloseUrl = "http://192.168.1.95/rpc/Switch.Set?id=0&on=true";
echo "<strong>URL:</strong> <code>$directCloseUrl</code><br>";
echo "<strong>Credenciales:</strong> admin:67da6c<br>";
flush();

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $directCloseUrl);
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
    echo "✅ <strong style='color: green;'>Comando directo CERRAR funcionó</strong><br>";
    echo "⏱️ Tiempo: " . round($totalTime, 3) . "s<br>";
    echo "📄 Respuesta: <code>$response</code>";
} else {
    echo "❌ <strong style='color: red;'>Error en comando directo CERRAR</strong><br>";
    echo "🔢 HTTP: $httpCode<br>";
    if ($error) echo "⚠️ Error: $error";
}
echo "</div>";

// Resumen final
echo "<h2>📋 Resumen de resultados</h2>";

$allWorking = ($openResult['success'] && $closeResult['success']);

if ($allWorking) {
    echo "<div style='background: #d4edda; border: 2px solid #c3e6cb; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724;'>🎉 ¡INTEGRACIÓN COMPLETAMENTE FUNCIONAL!</h3>";
    echo "<p><strong>✅ El sistema web ya puede controlar automáticamente la barrera Shelly</strong></p>";
    echo "<ul style='font-size: 16px;'>";
    echo "<li>✅ Conectividad desde servidor web: OK</li>";
    echo "<li>✅ Autenticación HTTP Basic: OK</li>";
    echo "<li>✅ Comando ABRIR barrera: OK</li>";
    echo "<li>✅ Comando CERRAR barrera: OK</li>";
    echo "<li>✅ Clase ShellyAPI: Funcional</li>";
    echo "</ul>";
    
    echo "<h4 style='color: #155724;'>🚀 Sistema listo en estas páginas:</h4>";
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>📥 Registrar Entrada (abre barrera):</strong><br>";
    echo "<a href='https://fix360.app/dunas/dunasshelly/public/access/create' target='_blank' style='color: #007bff; font-size: 14px;'>https://fix360.app/dunas/dunasshelly/public/access/create</a></p>";
    echo "<p><strong>📤 Registrar Salida (cierra barrera):</strong><br>";
    echo "<span style='color: #6c757d; font-size: 14px;'>https://fix360.app/dunas/dunasshelly/public/access/detail/[ID] → Botón 'Registrar Salida'</span></p>";
    echo "</div>";
    
    echo "<h4 style='color: #155724;'>🔧 Cómo funciona:</h4>";
    echo "<ol>";
    echo "<li>Al registrar una <strong>entrada</strong> → se ejecuta <code>ShellyAPI::openBarrier()</code></li>";
    echo "<li>Al registrar una <strong>salida</strong> → se ejecuta <code>ShellyAPI::closeBarrier()</code></li>";
    echo "<li>Los comandos se envían con autenticación <code>admin:67da6c</code></li>";
    echo "<li>El Shelly responde y controla físicamente la barrera</li>";
    echo "</ol>";
    
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 2px solid #f1aeb5; padding: 20px; border-radius: 10px;'>";
    echo "<h3 style='color: #721c24;'>⚠️ Problemas detectados en la integración</h3>";
    echo "<p>Revise los errores mostrados arriba para diagnosticar el problema.</p>";
    echo "<p><strong>Posibles causas:</strong></p>";
    echo "<ul>";
    echo "<li>El servidor web no puede acceder a la red local del Shelly</li>";
    echo "<li>Problema de configuración de red/firewall</li>";
    echo "<li>El Shelly no está respondiendo desde la IP del servidor</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<style>";
echo "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 20px; line-height: 1.6; }";
echo "h1, h2, h3, h4 { color: #333; margin-top: 30px; }";
echo "code { background: #f1f3f4; padding: 2px 6px; border-radius: 4px; font-family: 'Consolas', monospace; }";
echo "pre { background: #f8f9fa; padding: 15px; border-radius: 8px; overflow-x: auto; }";
echo "a { text-decoration: none; }";
echo "a:hover { text-decoration: underline; }";
echo "</style>";

echo "<script>";
echo "console.log('Test de integración Shelly completado');";
echo "setTimeout(function() { window.scrollTo(0, document.body.scrollHeight); }, 1000);";
echo "</script>";
?>