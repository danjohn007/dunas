<?php
/**
 * Diagnóstico Avanzado Shelly - Detección de Problemas de Red y Configuración
 */
require_once '../config/config.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Diagnóstico Avanzado Shelly</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        .info { color: #3498db; font-weight: bold; }
        .section { margin: 20px 0; padding: 15px; border-left: 4px solid #3498db; background: #f8f9fa; }
        .test-result { padding: 10px; margin: 5px 0; border-radius: 4px; }
        .test-success { background: #d4edda; border: 1px solid #c3e6cb; }
        .test-error { background: #f8d7da; border: 1px solid #f5c6cb; }
        .test-warning { background: #fff3cd; border: 1px solid #ffeaa7; }
        .code { background: #2c3e50; color: #ecf0f1; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background: #f2f2f2; }
        .recommendation { background: #e8f5e8; border: 1px solid #c8e6c9; padding: 15px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>";

echo "<div class='container'>";
echo "<h1>🔧 Diagnóstico Avanzado Shelly Pro 4PM</h1>";

// Función para hacer peticiones HTTP con información detallada
function testHttpRequest($url, $username = null, $password = null, $timeout = 10) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyDiagnostic/1.0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    if ($username && $password) {
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
    }
    
    $start_time = microtime(true);
    $response = curl_exec($ch);
    $end_time = microtime(true);
    
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'success' => !$error && $info['http_code'] === 200,
        'http_code' => $info['http_code'],
        'error' => $error,
        'response_time' => round(($end_time - $start_time) * 1000, 2),
        'url' => $url,
        'response' => $response,
        'info' => $info
    ];
}

echo "<div class='section'>";
echo "<h2>📋 1. Verificación de Configuración Actual</h2>";
echo "<table class='table'>";
echo "<tr><th>Parámetro</th><th>Valor</th><th>Estado</th></tr>";

$config_checks = [
    'SHELLY_API_URL' => SHELLY_API_URL,
    'SHELLY_USERNAME' => SHELLY_USERNAME,
    'SHELLY_PASSWORD' => substr(SHELLY_PASSWORD, -6),
    'SHELLY_ENABLED' => SHELLY_ENABLED ? 'SÍ' : 'NO',
    'SHELLY_API_TIMEOUT' => SHELLY_API_TIMEOUT,
    'SHELLY_SWITCH_ID' => SHELLY_SWITCH_ID,
    'SHELLY_OPEN_URL' => SHELLY_OPEN_URL,
    'SHELLY_CLOSE_URL' => SHELLY_CLOSE_URL
];

foreach ($config_checks as $key => $value) {
    $status = !empty($value) ? "✅ OK" : "❌ Vacío";
    echo "<tr><td><strong>$key</strong></td><td>$value</td><td>$status</td></tr>";
}
echo "</table>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>🌐 2. Diagnóstico de Conectividad de Red</h2>";

// Test 1: Verificar si la IP pública responde
echo "<h3>🔍 Test 1: Conectividad a IP Pública</h3>";
$public_result = testHttpRequest('http://162.215.121.70/', SHELLY_USERNAME, SHELLY_PASSWORD);

echo "<div class='test-result " . ($public_result['success'] ? 'test-success' : 'test-error') . "'>";
echo "<strong>URL:</strong> http://162.215.121.70/<br>";
echo "<strong>Código HTTP:</strong> " . $public_result['http_code'] . "<br>";
echo "<strong>Tiempo de respuesta:</strong> " . $public_result['response_time'] . " ms<br>";
if ($public_result['error']) {
    echo "<strong>Error cURL:</strong> " . $public_result['error'] . "<br>";
}
echo "<strong>Estado:</strong> " . ($public_result['success'] ? "✅ Conectado" : "❌ Sin conexión") . "<br>";
echo "</div>";

// Test 2: Verificar endpoint específico de Shelly
echo "<h3>🎯 Test 2: Endpoint Shelly (/shelly)</h3>";
$shelly_result = testHttpRequest('http://162.215.121.70/shelly', SHELLY_USERNAME, SHELLY_PASSWORD);

echo "<div class='test-result " . ($shelly_result['success'] ? 'test-success' : 'test-error') . "'>";
echo "<strong>URL:</strong> http://162.215.121.70/shelly<br>";
echo "<strong>Código HTTP:</strong> " . $shelly_result['http_code'] . "<br>";
echo "<strong>Tiempo de respuesta:</strong> " . $shelly_result['response_time'] . " ms<br>";
if ($shelly_result['error']) {
    echo "<strong>Error cURL:</strong> " . $shelly_result['error'] . "<br>";
}
echo "<strong>Estado:</strong> " . ($shelly_result['success'] ? "✅ Accesible" : "❌ No accesible") . "<br>";
echo "</div>";

// Test 3: Verificar endpoint de información del dispositivo
echo "<h3>📱 Test 3: Información del Dispositivo (/rpc/Shelly.GetInfo)</h3>";
$info_result = testHttpRequest('http://162.215.121.70/rpc/Shelly.GetInfo', SHELLY_USERNAME, SHELLY_PASSWORD);

echo "<div class='test-result " . ($info_result['success'] ? 'test-success' : 'test-error') . "'>";
echo "<strong>URL:</strong> http://162.215.121.70/rpc/Shelly.GetInfo<br>";
echo "<strong>Código HTTP:</strong> " . $info_result['http_code'] . "<br>";
echo "<strong>Tiempo de respuesta:</strong> " . $info_result['response_time'] . " ms<br>";
if ($info_result['error']) {
    echo "<strong>Error cURL:</strong> " . $info_result['error'] . "<br>";
}
echo "<strong>Estado:</strong> " . ($info_result['success'] ? "✅ Información obtenida" : "❌ Sin información") . "<br>";

if ($info_result['success'] && $info_result['response']) {
    // Extraer solo el cuerpo de la respuesta (después de los headers)
    $response_parts = explode("\r\n\r\n", $info_result['response'], 2);
    if (count($response_parts) > 1) {
        $body = $response_parts[1];
        $device_info = json_decode($body, true);
        if ($device_info) {
            echo "<div class='code'>";
            echo "<strong>Información del dispositivo:</strong><br>";
            echo "ID: " . ($device_info['id'] ?? 'N/A') . "<br>";
            echo "Modelo: " . ($device_info['model'] ?? 'N/A') . "<br>";
            echo "Generación: " . ($device_info['gen'] ?? 'N/A') . "<br>";
            echo "Firmware: " . ($device_info['fw_id'] ?? 'N/A') . "<br>";
            echo "MAC: " . ($device_info['mac'] ?? 'N/A') . "<br>";
            echo "</div>";
        }
    }
}
echo "</div>";

echo "</div>";

echo "<div class='section'>";
echo "<h2>🔧 3. Test de Comandos de Control</h2>";

// Test de comando ABRIR
echo "<h3>🔓 Test 4: Comando ABRIR Barrera</h3>";
$open_result = testHttpRequest(SHELLY_OPEN_URL, SHELLY_USERNAME, SHELLY_PASSWORD);

echo "<div class='test-result " . ($open_result['success'] ? 'test-success' : 'test-error') . "'>";
echo "<strong>URL:</strong> " . SHELLY_OPEN_URL . "<br>";
echo "<strong>Código HTTP:</strong> " . $open_result['http_code'] . "<br>";
echo "<strong>Tiempo de respuesta:</strong> " . $open_result['response_time'] . " ms<br>";
if ($open_result['error']) {
    echo "<strong>Error cURL:</strong> " . $open_result['error'] . "<br>";
}
echo "<strong>Estado:</strong> " . ($open_result['success'] ? "✅ Comando exitoso" : "❌ Comando falló") . "<br>";
echo "</div>";

// Test de comando CERRAR
echo "<h3>🔒 Test 5: Comando CERRAR Barrera</h3>";
$close_result = testHttpRequest(SHELLY_CLOSE_URL, SHELLY_USERNAME, SHELLY_PASSWORD);

echo "<div class='test-result " . ($close_result['success'] ? 'test-success' : 'test-error') . "'>";
echo "<strong>URL:</strong> " . SHELLY_CLOSE_URL . "<br>";
echo "<strong>Código HTTP:</strong> " . $close_result['http_code'] . "<br>";
echo "<strong>Tiempo de respuesta:</strong> " . $close_result['response_time'] . " ms<br>";
if ($close_result['error']) {
    echo "<strong>Error cURL:</strong> " . $close_result['error'] . "<br>";
}
echo "<strong>Estado:</strong> " . ($close_result['success'] ? "✅ Comando exitoso" : "❌ Comando falló") . "<br>";
echo "</div>";

echo "</div>";

echo "<div class='section'>";
echo "<h2>🔍 4. Análisis de Errores HTTP</h2>";

$all_results = [$public_result, $shelly_result, $info_result, $open_result, $close_result];
$error_codes = [];

foreach ($all_results as $result) {
    if (!$result['success']) {
        $code = $result['http_code'];
        if (!isset($error_codes[$code])) {
            $error_codes[$code] = 0;
        }
        $error_codes[$code]++;
    }
}

if (!empty($error_codes)) {
    echo "<table class='table'>";
    echo "<tr><th>Código HTTP</th><th>Frecuencia</th><th>Significado</th><th>Posible Causa</th></tr>";
    
    $http_meanings = [
        302 => ['Redirección Temporal', 'Problema de autenticación o configuración incorrecta'],
        401 => ['No Autorizado', 'Credenciales incorrectas'],
        403 => ['Prohibido', 'Acceso denegado, verificar permisos'],
        404 => ['No Encontrado', 'URL incorrecta o dispositivo no disponible'],
        500 => ['Error del Servidor', 'Error interno del dispositivo Shelly'],
        0 => ['Sin Respuesta', 'Timeout de conexión, dispositivo no alcanzable']
    ];
    
    foreach ($error_codes as $code => $count) {
        $meaning = $http_meanings[$code] ?? ['Desconocido', 'Error no documentado'];
        echo "<tr>";
        echo "<td><strong>$code</strong></td>";
        echo "<td>$count</td>";
        echo "<td>{$meaning[0]}</td>";
        echo "<td>{$meaning[1]}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='test-success'>✅ No se detectaron errores HTTP</div>";
}

echo "</div>";

echo "<div class='section'>";
echo "<h2>💡 5. Recomendaciones de Solución</h2>";

$recommendations = [];

// Analizar problemas y generar recomendaciones
if (in_array(302, array_keys($error_codes))) {
    $recommendations[] = [
        'title' => '🔐 Problema de Autenticación (HTTP 302)',
        'description' => 'El dispositivo está redirigiendo las peticiones, probablemente por autenticación incorrecta.',
        'solutions' => [
            'Verificar que las credenciales (admin:67da6c) sean correctas',
            'Comprobar si el dispositivo requiere autenticación digest en lugar de basic',
            'Revisar si hay configuración de seguridad adicional en el Shelly'
        ]
    ];
}

if (in_array(0, array_keys($error_codes))) {
    $recommendations[] = [
        'title' => '🌐 Problema de Conectividad (Timeout)',
        'description' => 'El servidor no puede conectar con el dispositivo Shelly.',
        'solutions' => [
            'Verificar que el port forwarding esté configurado correctamente en el router',
            'Comprobar que el puerto 80 del router apunte a 192.168.1.95:80',
            'Verificar que el dispositivo Shelly esté encendido y conectado a la red',
            'Comprobar configuración de firewall en el router y el dispositivo'
        ]
    ];
}

if (empty($recommendations)) {
    $recommendations[] = [
        'title' => '✅ Configuración Básica Correcta',
        'description' => 'Los tests básicos fueron exitosos.',
        'solutions' => [
            'Probar la integración completa en el sistema',
            'Monitorear logs para detectar problemas intermitentes',
            'Configurar alertas para fallos de conectividad'
        ]
    ];
}

foreach ($recommendations as $rec) {
    echo "<div class='recommendation'>";
    echo "<h4>{$rec['title']}</h4>";
    echo "<p>{$rec['description']}</p>";
    echo "<ul>";
    foreach ($rec['solutions'] as $solution) {
        echo "<li>$solution</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "</div>";

echo "<div class='section'>";
echo "<h2>⚙️ 6. Comandos de Solución Sugeridos</h2>";

echo "<div class='recommendation'>";
echo "<h4>🔧 Para Configurar Port Forwarding:</h4>";
echo "<div class='code'>";
echo "1. Acceder al router (generalmente 192.168.1.1)<br>";
echo "2. Buscar 'Port Forwarding' o 'NAT'<br>";
echo "3. Crear regla:<br>";
echo "&nbsp;&nbsp;- Puerto externo: 80<br>";
echo "&nbsp;&nbsp;- IP interna: 192.168.1.95<br>";
echo "&nbsp;&nbsp;- Puerto interno: 80<br>";
echo "&nbsp;&nbsp;- Protocolo: TCP<br>";
echo "</div>";
echo "</div>";

echo "<div class='recommendation'>";
echo "<h4>🔍 Para Verificar IP Pública:</h4>";
echo "<div class='code'>";
echo "curl -s https://api.ipify.org<br>";
echo "# Debe devolver: 162.215.121.70";
echo "</div>";
echo "</div>";

echo "<div class='recommendation'>";
echo "<h4>🧪 Para Probar Comandos Directos:</h4>";
echo "<div class='code'>";
echo "# Desde línea de comandos (con curl):<br>";
echo "curl -u admin:67da6c http://162.215.121.70/rpc/Shelly.GetInfo<br>";
echo "curl -u admin:67da6c http://162.215.121.70/rpc/Switch.Set?id=0&on=false<br>";
echo "curl -u admin:67da6c http://162.215.121.70/rpc/Switch.Set?id=0&on=true";
echo "</div>";
echo "</div>";

echo "</div>";

echo "<div class='section'>";
echo "<h2>📊 7. Resumen del Diagnóstico</h2>";

$total_tests = count($all_results);
$successful_tests = count(array_filter($all_results, function($r) { return $r['success']; }));
$success_rate = round(($successful_tests / $total_tests) * 100, 1);

echo "<table class='table'>";
echo "<tr><th>Métrica</th><th>Valor</th></tr>";
echo "<tr><td>Tests realizados</td><td>$total_tests</td></tr>";
echo "<tr><td>Tests exitosos</td><td>$successful_tests</td></tr>";
echo "<tr><td>Tasa de éxito</td><td>$success_rate%</td></tr>";
echo "<tr><td>Estado general</td><td>" . ($success_rate >= 80 ? "✅ Bueno" : ($success_rate >= 50 ? "⚠️ Regular" : "❌ Crítico")) . "</td></tr>";
echo "</table>";

echo "</div>";

echo "</div>";
echo "</body></html>";
?>