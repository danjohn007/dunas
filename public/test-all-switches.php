<?php
/**
 * Probador de todos los switches físicos del Shelly Pro 4PM
 */

require_once __DIR__ . '/../config/config.php';

echo "<h1>🔧 Probador de Switches Físicos - Shelly Pro 4PM</h1>";
echo "<p><strong>Objetivo:</strong> Encontrar cuál switch controla realmente tu barrera</p>";

// Obtener IP base del Shelly
$shellyIP = str_replace(['http://', 'https://', '/'], '', SHELLY_API_URL);
$shellyIP = rtrim($shellyIP, '/');

echo "<h2>📋 Información:</h2>";
echo "<p><strong>IP del Shelly:</strong> $shellyIP</p>";
echo "<p><strong>El Shelly Pro 4PM tiene 4 switches (0, 1, 2, 3)</strong></p>";
echo "<p><strong>Actualmente usas:</strong> Switch 0</p>";

// CSS para los botones
echo "<style>
.switch-container { 
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
    gap: 20px; 
    margin: 20px 0; 
}
.switch-box { 
    border: 2px solid #ddd; 
    padding: 20px; 
    border-radius: 10px; 
    text-align: center; 
    background: #f9f9f9;
}
.switch-box h3 { 
    margin-top: 0; 
    color: #333; 
}
.btn { 
    padding: 12px 20px; 
    margin: 5px; 
    border: none; 
    border-radius: 5px; 
    font-weight: bold; 
    text-decoration: none; 
    display: inline-block; 
    cursor: pointer;
}
.btn-on { background: #28a745; color: white; }
.btn-off { background: #dc3545; color: white; }
.btn-status { background: #17a2b8; color: white; }
.result { 
    margin: 10px 0; 
    padding: 10px; 
    border-radius: 5px; 
    font-size: 12px;
}
.success { background: #d4edda; border: 1px solid #c3e6cb; }
.error { background: #f8d7da; border: 1px solid #f1aeb5; }
</style>";

// Función para probar un switch
function testSwitch($ip, $switchId, $action) {
    $actionValue = ($action === 'on') ? 'true' : 'false';
    
    // Probar ambos formatos de API
    $urls = [
        'rpc' => "http://$ip/rpc/Switch.Set?id=$switchId&on=$actionValue",
        'classic' => "http://$ip/relay/$switchId?turn=$action"
    ];
    
    $results = [];
    
    foreach ($urls as $format => $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        $results[$format] = [
            'success' => !$error && $httpCode == 200,
            'http_code' => $httpCode,
            'error' => $error,
            'response' => $response,
            'url' => $url
        ];
    }
    
    return $results;
}

// Función para obtener estado de un switch
function getSwitchStatus($ip, $switchId) {
    $urls = [
        'rpc' => "http://$ip/rpc/Switch.GetStatus?id=$switchId",
        'classic' => "http://$ip/relay/$switchId"
    ];
    
    foreach ($urls as $format => $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if (!$error && $httpCode == 200) {
            $decoded = json_decode($response, true);
            return [
                'success' => true,
                'format' => $format,
                'data' => $decoded,
                'response' => $response
            ];
        }
    }
    
    return ['success' => false, 'error' => 'No se pudo obtener estado'];
}

echo "<h2>🎮 Panel de Control Manual</h2>";
echo "<p><strong>INSTRUCCIONES:</strong></p>";
echo "<ol>";
echo "<li>Haz clic en <strong>ON</strong> en cada switch y observa si se mueve la barrera</li>";
echo "<li>Si un switch mueve la barrera, haz clic en <strong>OFF</strong> para confirmar</li>";
echo "<li>Anota cuál switch funciona</li>";
echo "<li>Al final, actualiza la configuración con el switch correcto</li>";
echo "</ol>";

echo "<div class='switch-container'>";

// Crear panel de control para cada switch
for ($i = 0; $i <= 3; $i++) {
    echo "<div class='switch-box'>";
    echo "<h3>🔌 Switch $i</h3>";
    
    // Obtener estado actual
    $status = getSwitchStatus($shellyIP, $i);
    if ($status['success']) {
        echo "<p><strong>Estado actual:</strong> ";
        if ($status['format'] === 'rpc' && isset($status['data']['output'])) {
            echo $status['data']['output'] ? '🟢 ON' : '🔴 OFF';
        } elseif ($status['format'] === 'classic' && isset($status['data']['ison'])) {
            echo $status['data']['ison'] ? '🟢 ON' : '🔴 OFF';
        } else {
            echo "❓ Desconocido";
        }
        echo "</p>";
    }
    
    // Botones de control
    echo "<a href='?switch=$i&action=on' class='btn btn-on'>🔛 ENCENDER</a>";
    echo "<a href='?switch=$i&action=off' class='btn btn-off'>⏹️ APAGAR</a>";
    echo "<a href='?switch=$i&action=status' class='btn btn-status'>📊 ESTADO</a>";
    
    echo "</div>";
}

echo "</div>";

// Procesar acciones
if (isset($_GET['switch']) && isset($_GET['action'])) {
    $switchId = (int)$_GET['switch'];
    $action = $_GET['action'];
    
    echo "<hr>";
    echo "<h2>🧪 Resultado de la prueba</h2>";
    echo "<h3>Switch $switchId - Acción: " . strtoupper($action) . "</h3>";
    
    if ($action === 'status') {
        $status = getSwitchStatus($shellyIP, $switchId);
        if ($status['success']) {
            echo "<div class='result success'>";
            echo "<strong>✅ Estado obtenido exitosamente</strong><br>";
            echo "<strong>Formato API:</strong> " . $status['format'] . "<br>";
            echo "<strong>Datos:</strong><br>";
            echo "<pre>" . json_encode($status['data'], JSON_PRETTY_PRINT) . "</pre>";
            echo "</div>";
        } else {
            echo "<div class='result error'>";
            echo "<strong>❌ No se pudo obtener el estado</strong>";
            echo "</div>";
        }
    } else {
        $results = testSwitch($shellyIP, $switchId, $action);
        
        foreach ($results as $format => $result) {
            echo "<h4>Formato $format:</h4>";
            echo "<p><strong>URL:</strong> <code>" . $result['url'] . "</code></p>";
            
            if ($result['success']) {
                echo "<div class='result success'>";
                echo "<strong>✅ Comando ejecutado exitosamente</strong><br>";
                echo "<strong>HTTP:</strong> " . $result['http_code'] . "<br>";
                if ($result['response']) {
                    $decoded = json_decode($result['response'], true);
                    if ($decoded) {
                        echo "<strong>Respuesta:</strong> " . json_encode($decoded) . "<br>";
                    }
                }
                echo "<strong>🎯 Si la barrera se movió, este es tu switch correcto!</strong>";
                echo "</div>";
            } else {
                echo "<div class='result error'>";
                echo "<strong>❌ Error en comando</strong><br>";
                echo "<strong>HTTP:</strong> " . $result['http_code'] . "<br>";
                if ($result['error']) {
                    echo "<strong>Error:</strong> " . $result['error'] . "<br>";
                }
                echo "</div>";
            }
        }
        
        echo "<div style='background: #e7f3ff; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>🔍 ¿Se movió la barrera?</h4>";
        echo "<p>Si la barrera se movió con este comando, este es tu switch correcto.</p>";
        echo "<p><strong>Para configurar el sistema:</strong></p>";
        echo "<ol>";
        echo "<li>Anota que el <strong>Switch $switchId</strong> es el correcto</li>";
        echo "<li>Haz clic en el botón de abajo para actualizar la configuración automáticamente</li>";
        echo "</ol>";
        
        // Determinar qué formato funcionó
        $workingFormat = null;
        if ($results['rpc']['success']) {
            $workingFormat = 'rpc';
        } elseif ($results['classic']['success']) {
            $workingFormat = 'classic';
        }
        
        if ($workingFormat) {
            echo "<form method='post' style='margin: 10px 0;'>";
            echo "<input type='hidden' name='correct_switch' value='$switchId'>";
            echo "<input type='hidden' name='api_format' value='$workingFormat'>";
            echo "<button type='submit' style='background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>";
            echo "✅ Configurar Switch $switchId como control de barrera";
            echo "</button>";
            echo "</form>";
        }
        echo "</div>";
    }
}

// Procesar configuración automática
if (isset($_POST['correct_switch']) && isset($_POST['api_format'])) {
    $correctSwitch = (int)$_POST['correct_switch'];
    $apiFormat = $_POST['api_format'];
    
    echo "<hr>";
    echo "<h2>🔧 Actualizando configuración...</h2>";
    
    // Leer archivo de configuración
    $configFile = '../config/config.php';
    $configContent = file_get_contents($configFile);
    
    if ($configContent) {
        // Crear nuevas URLs
        if ($apiFormat === 'rpc') {
            $newOpenUrl = "http://$shellyIP/rpc/Switch.Set?id=$correctSwitch&on=false";
            $newCloseUrl = "http://$shellyIP/rpc/Switch.Set?id=$correctSwitch&on=true";
        } else {
            $newOpenUrl = "http://$shellyIP/relay/$correctSwitch?turn=off";
            $newCloseUrl = "http://$shellyIP/relay/$correctSwitch?turn=on";
        }
        
        // Reemplazar URLs
        $newContent = preg_replace(
            '/define\(\'SHELLY_OPEN_URL\',.*?\);/',
            "define('SHELLY_OPEN_URL', '$newOpenUrl');  // Switch $correctSwitch - $apiFormat API",
            $configContent
        );
        
        $newContent = preg_replace(
            '/define\(\'SHELLY_CLOSE_URL\',.*?\);/',
            "define('SHELLY_CLOSE_URL', '$newCloseUrl');  // Switch $correctSwitch - $apiFormat API",
            $newContent
        );
        
        // Hacer backup
        $backupFile = '../config/config.php.backup-switch-' . date('Y-m-d-H-i-s');
        file_put_contents($backupFile, $configContent);
        
        // Escribir nueva configuración
        if (file_put_contents($configFile, $newContent)) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px;'>";
            echo "<h3>✅ ¡Configuración actualizada exitosamente!</h3>";
            echo "<p><strong>Switch configurado:</strong> $correctSwitch</p>";
            echo "<p><strong>Formato API:</strong> $apiFormat</p>";
            echo "<p><strong>Nuevas URLs:</strong></p>";
            echo "<ul>";
            echo "<li><strong>Abrir:</strong> <code>$newOpenUrl</code></li>";
            echo "<li><strong>Cerrar:</strong> <code>$newCloseUrl</code></li>";
            echo "</ul>";
            echo "<p><strong>Backup:</strong> $backupFile</p>";
            echo "<p>🎉 <strong>¡Tu sistema ahora debería funcionar correctamente!</strong></p>";
            echo "<p><a href='test-shelly-portforward.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Probar sistema completo</a></p>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Error al escribir archivo de configuración</p>";
        }
    }
}

echo "<h2>💡 Información adicional</h2>";
echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px;'>";
echo "<h4>🔍 Si ningún switch mueve la barrera:</h4>";
echo "<ul>";
echo "<li><strong>Verifica el cableado:</strong> La barrera debe estar conectada a uno de los 4 relés del Shelly</li>";
echo "<li><strong>Configuración del Shelly:</strong> Los switches deben estar configurados como 'relay' no como 'switch'</li>";
echo "<li><strong>Voltaje:</strong> Verifica que el voltaje de control sea compatible</li>";
echo "<li><strong>Manual del fabricante:</strong> Consulta qué señal necesita tu barrera (ON/OFF, pulso, etc.)</li>";
echo "</ul>";
echo "</div>";
?>