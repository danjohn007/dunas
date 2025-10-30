<?php
/**
 * Diagnóstico simple y directo de Shelly
 */

require_once __DIR__ . '/../config/config.php';

echo "<h1>🔧 Diagnóstico Simple de Shelly</h1>";
echo "<p><strong>Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Mostrar configuración actual
echo "<h2>📋 Configuración actual:</h2>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Variable</th><th>Valor</th></tr>";
echo "<tr><td>SHELLY_API_URL</td><td>" . SHELLY_API_URL . "</td></tr>";
echo "<tr><td>SHELLY_OPEN_URL</td><td>" . SHELLY_OPEN_URL . "</td></tr>";
echo "<tr><td>SHELLY_CLOSE_URL</td><td>" . SHELLY_CLOSE_URL . "</td></tr>";
echo "<tr><td>SHELLY_ENABLED</td><td>" . (SHELLY_ENABLED ? 'true' : 'false') . "</td></tr>";
echo "</table>";

// Función simple para probar URLs
function testUrl($url, $description) {
    echo "<h3>🧪 $description</h3>";
    echo "<p><strong>URL:</strong> <code>$url</code></p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyTest/1.0');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $start = microtime(true);
    $response = curl_exec($ch);
    $time = microtime(true) - $start;
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $responseSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
    curl_close($ch);
    
    // Mostrar resultados en tabla
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    
    if ($error) {
        echo "<tr style='background-color: #ffebee;'>";
        echo "<td><strong>Estado</strong></td>";
        echo "<td style='color: red;'>❌ ERROR</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td><strong>Error cURL</strong></td>";
        echo "<td style='color: red; font-family: monospace;'>$error</td>";
        echo "</tr>";
    } else {
        if ($httpCode == 200) {
            echo "<tr style='background-color: #e8f5e8;'>";
            echo "<td><strong>Estado</strong></td>";
            echo "<td style='color: green;'>✅ OK</td>";
            echo "</tr>";
        } else {
            echo "<tr style='background-color: #fff3e0;'>";
            echo "<td><strong>Estado</strong></td>";
            echo "<td style='color: orange;'>⚠️ HTTP $httpCode</td>";
            echo "</tr>";
        }
    }
    
    echo "<tr><td><strong>Código HTTP</strong></td><td>$httpCode</td></tr>";
    echo "<tr><td><strong>Tiempo respuesta</strong></td><td>" . round($time * 1000, 2) . "ms</td></tr>";
    echo "<tr><td><strong>Tamaño respuesta</strong></td><td>" . $responseSize . " bytes</td></tr>";
    
    if ($response && !$error) {
        $decoded = json_decode($response, true);
        if ($decoded) {
            echo "<tr><td><strong>Respuesta JSON</strong></td><td><pre style='margin: 0;'>" . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "</pre></td></tr>";
        } else {
            echo "<tr><td><strong>Respuesta RAW</strong></td><td><pre style='margin: 0;'>" . htmlspecialchars(substr($response, 0, 300)) . "</pre></td></tr>";
        }
    }
    
    echo "</table>";
    
    return [
        'success' => !$error && $httpCode == 200,
        'http_code' => $httpCode,
        'error' => $error,
        'response' => $response,
        'time' => $time
    ];
}

// 1. Probar conectividad básica
$basicTest = testUrl(SHELLY_API_URL, "Conectividad básica");

// 2. Probar endpoint de información
$infoTest = testUrl(SHELLY_API_URL . 'shelly', "Información del dispositivo");

// 3. Probar comando de apertura
$openTest = testUrl(SHELLY_OPEN_URL, "Comando ABRIR barrera");

// 4. Probar comando de cierre  
$closeTest = testUrl(SHELLY_CLOSE_URL, "Comando CERRAR barrera");

// 5. Análisis de resultados
echo "<h2>📊 Análisis de resultados</h2>";

if (!$basicTest['success']) {
    echo "<div style='background: #f8d7da; border: 1px solid #f1aeb5; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ Problema de conectividad básica</h4>";
    echo "<p><strong>Error:</strong> " . $basicTest['error'] . "</p>";
    echo "<p><strong>Posibles causas:</strong></p>";
    echo "<ul>";
    echo "<li>Tu IP pública cambió</li>";
    echo "<li>El port forwarding no está funcionando</li>";
    echo "<li>El dispositivo Shelly está offline</li>";
    echo "<li>Hay un firewall bloqueando la conexión</li>";
    echo "</ul>";
    echo "</div>";
} elseif (!$openTest['success'] || !$closeTest['success']) {
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>⚠️ Conectividad OK, pero comandos fallan</h4>";
    echo "<p><strong>La conexión al Shelly funciona, pero los comandos RPC no.</strong></p>";
    echo "<p><strong>Códigos HTTP:</strong></p>";
    echo "<ul>";
    echo "<li>Comando ABRIR: HTTP " . $openTest['http_code'] . ($openTest['error'] ? " - " . $openTest['error'] : "") . "</li>";
    echo "<li>Comando CERRAR: HTTP " . $closeTest['http_code'] . ($closeTest['error'] ? " - " . $closeTest['error'] : "") . "</li>";
    echo "</ul>";
    echo "</div>";
    
    // Sugerir URLs alternativas
    echo "<h3>🔧 Pruebas con URLs alternativas</h3>";
    echo "<p>Tu Shelly Pro 4PM podría usar un formato de API diferente. Probemos:</p>";
    
    // Formato API REST clásico
    $altUrl1 = str_replace('/rpc/Switch.Set?id=0&on=false', '/relay/0?turn=on', SHELLY_OPEN_URL);
    $altUrl2 = str_replace('/rpc/Switch.Set?id=0&on=true', '/relay/0?turn=off', SHELLY_CLOSE_URL);
    
    $alt1Test = testUrl($altUrl1, "Formato API clásico - ABRIR");
    $alt2Test = testUrl($altUrl2, "Formato API clásico - CERRAR");
    
    if ($alt1Test['success'] || $alt2Test['success']) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ ¡Encontré el formato correcto!</h4>";
        echo "<p>Tu Shelly responde al formato API clásico en lugar del RPC.</p>";
        echo "<p><strong>URLs que funcionan:</strong></p>";
        echo "<ul>";
        echo "<li><strong>Abrir:</strong> <code>$altUrl1</code></li>";
        echo "<li><strong>Cerrar:</strong> <code>$altUrl2</code></li>";
        echo "</ul>";
        echo "<p><a href='?fix_config=classic' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Aplicar configuración automáticamente</a></p>";
        echo "</div>";
    }
    
} else {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ ¡Todos los comandos funcionan correctamente!</h4>";
    echo "<p>Los comandos se están ejecutando sin errores HTTP.</p>";
    echo "<p><strong>Tiempos de respuesta:</strong></p>";
    echo "<ul>";
    echo "<li>Comando ABRIR: " . round($openTest['time'] * 1000, 2) . "ms</li>";
    echo "<li>Comando CERRAR: " . round($closeTest['time'] * 1000, 2) . "ms</li>";
    echo "</ul>";
    echo "<p><strong>Si la barrera no se mueve físicamente, el problema está en:</strong></p>";
    echo "<ul>";
    echo "<li>El número de switch (quizás no es el 0)</li>";
    echo "<li>La configuración del relé en el Shelly</li>";
    echo "<li>El cableado físico</li>";
    echo "</ul>";
    echo "</div>";
}

// Procesar aplicación automática de configuración clásica
if (isset($_GET['fix_config']) && $_GET['fix_config'] === 'classic') {
    echo "<hr>";
    echo "<h2>🔧 Aplicando configuración API clásica...</h2>";
    
    // Leer archivo actual
    $configFile = '../config/config.php';
    $configContent = file_get_contents($configFile);
    
    if ($configContent) {
        // Obtener IP pública actual
        $currentIP = '';
        if (preg_match('/SHELLY_API_URL.*?http:\/\/([\d\.]+)\//', $configContent, $matches)) {
            $currentIP = $matches[1];
        }
        
        if ($currentIP) {
            // Crear nuevas URLs con formato clásico
            $newOpenUrl = "http://$currentIP/relay/0?turn=on";
            $newCloseUrl = "http://$currentIP/relay/0?turn=off";
            
            // Reemplazar las URLs
            $newContent = preg_replace(
                '/define\(\'SHELLY_OPEN_URL\',.*?\);/',
                "define('SHELLY_OPEN_URL', '$newOpenUrl');  // Abrir - API clásica",
                $configContent
            );
            
            $newContent = preg_replace(
                '/define\(\'SHELLY_CLOSE_URL\',.*?\);/',
                "define('SHELLY_CLOSE_URL', '$newCloseUrl');  // Cerrar - API clásica",
                $newContent
            );
            
            // Hacer backup
            $backupFile = '../config/config.php.backup-' . date('Y-m-d-H-i-s');
            file_put_contents($backupFile, $configContent);
            
            // Escribir nueva configuración
            if (file_put_contents($configFile, $newContent)) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
                echo "<h4>✅ Configuración actualizada exitosamente</h4>";
                echo "<p><strong>Nuevas URLs:</strong></p>";
                echo "<ul>";
                echo "<li><strong>Abrir:</strong> <code>$newOpenUrl</code></li>";
                echo "<li><strong>Cerrar:</strong> <code>$newCloseUrl</code></li>";
                echo "</ul>";
                echo "<p><strong>Backup guardado en:</strong> <code>$backupFile</code></p>";
                echo "<p><a href='.' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Probar nueva configuración</a></p>";
                echo "</div>";
            } else {
                echo "<p style='color: red;'>❌ Error al escribir el archivo de configuración</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ No se pudo detectar la IP actual</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se pudo leer el archivo de configuración</p>";
    }
}

echo "<h3>🔗 Enlaces útiles:</h3>";
echo "<ul>";
echo "<li><a href='test-shelly-portforward.php'>🧪 Prueba completa de Shelly</a></li>";
echo "<li><a href='get-public-ip.php'>🌐 Verificar IP pública</a></li>";
echo "<li><a href='" . SHELLY_API_URL . "' target='_blank'>🔗 Abrir interfaz web del Shelly</a></li>";
echo "</ul>";
?>