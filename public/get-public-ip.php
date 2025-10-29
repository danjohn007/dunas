<?php
/**
 * Script para obtener la IP pública y verificar conectividad con Shelly
 */

echo "<h1>Configuración automática de Shelly con Port Forwarding</h1>";

// Función para obtener IP pública
function getPublicIP() {
    $services = [
        'https://api.ipify.org',
        'https://icanhazip.com',
        'https://ipecho.net/plain',
        'https://whatismyipaddress.com/api/ip.php'
    ];
    
    foreach ($services as $service) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $service);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        
        $ip = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if (!$error && $ip && filter_var(trim($ip), FILTER_VALIDATE_IP)) {
            return trim($ip);
        }
    }
    
    return false;
}

// Obtener IP pública
echo "<h2>🌐 Obteniendo IP pública...</h2>";
$publicIP = getPublicIP();

if ($publicIP) {
    echo "<p><strong style='color: green;'>✅ IP pública detectada:</strong> $publicIP</p>";
    
    // URLs que se usarán
    $shellyBaseURL = "http://$publicIP/";
    $openURL = "http://$publicIP/rpc/Switch.Set?id=0&on=false";
    $closeURL = "http://$publicIP/rpc/Switch.Set?id=0&on=true";
    
    echo "<h2>📋 URLs configuradas:</h2>";
    echo "<ul>";
    echo "<li><strong>Base:</strong> $shellyBaseURL</li>";
    echo "<li><strong>Abrir:</strong> $openURL</li>";
    echo "<li><strong>Cerrar:</strong> $closeURL</li>";
    echo "</ul>";
    
    // Probar conectividad
    echo "<h2>🧪 Probando conectividad...</h2>";
    
    $tests = [
        'Base' => $shellyBaseURL,
        'Status' => "http://$publicIP/status",
        'Shelly Info' => "http://$publicIP/shelly",
        'Switch Status' => "http://$publicIP/rpc/Switch.GetStatus?id=0"
    ];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Prueba</th><th>URL</th><th>Resultado</th><th>Tiempo</th></tr>";
    
    $allGood = true;
    
    foreach ($tests as $test => $url) {
        $start = microtime(true);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        $time = round((microtime(true) - $start) * 1000, 2);
        
        if ($error) {
            echo "<tr style='background-color: #ffebee;'>";
            echo "<td>$test</td>";
            echo "<td style='font-size: 11px;'>$url</td>";
            echo "<td style='color: red;'>❌ Error: $error</td>";
            echo "<td>{$time}ms</td>";
            echo "</tr>";
            $allGood = false;
        } elseif ($httpCode == 200) {
            echo "<tr style='background-color: #e8f5e8;'>";
            echo "<td>$test</td>";
            echo "<td style='font-size: 11px;'>$url</td>";
            echo "<td style='color: green;'>✅ HTTP $httpCode - OK</td>";
            echo "<td>{$time}ms</td>";
            echo "</tr>";
        } else {
            echo "<tr style='background-color: #fff3e0;'>";
            echo "<td>$test</td>";
            echo "<td style='font-size: 11px;'>$url</td>";
            echo "<td style='color: orange;'>⚠️ HTTP $httpCode</td>";
            echo "<td>{$time}ms</td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    
    // Mostrar código para actualizar configuración
    echo "<h2>🔧 Código para actualizar config.php:</h2>";
    
    if ($allGood) {
        echo "<p style='color: green;'><strong>✅ ¡Perfecto! La conectividad funciona.</strong></p>";
    } else {
        echo "<p style='color: orange;'><strong>⚠️ Algunos tests fallaron, pero puedes probar la configuración.</strong></p>";
    }
    
    echo "<p><strong>Copia este código y pégalo en tu archivo <code>config/config.php</code>:</strong></p>";
    
    echo "<textarea style='width: 100%; height: 200px; font-family: monospace;'>";
    echo "// Configuración de Shelly Relay API con Port Forwarding\n";
    echo "define('SHELLY_API_URL', '$shellyBaseURL'); // IP pública con port forwarding\n";
    echo "define('SHELLY_API_TIMEOUT', 15); // Timeout aumentado para conexión externa\n";
    echo "define('SHELLY_SWITCH_ID', 0);  // ID del switch para abrir/cerrar barrera\n";
    echo "define('SHELLY_ENABLED', true); // Habilitado con port forwarding\n";
    echo "// URLs completas para las acciones\n";
    echo "define('SHELLY_OPEN_URL', '$openURL');  // Abrir\n";
    echo "define('SHELLY_CLOSE_URL', '$closeURL');  // Cerrar";
    echo "</textarea>";
    
    // Botón para aplicar cambios automáticamente
    echo "<h2>🚀 Aplicar cambios automáticamente:</h2>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='public_ip' value='$publicIP'>";
    echo "<input type='hidden' name='apply_config' value='1'>";
    echo "<button type='submit' style='background: #4CAF50; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px;'>✅ Actualizar configuración automáticamente</button>";
    echo "</form>";
    
} else {
    echo "<p style='color: red;'><strong>❌ No se pudo obtener la IP pública.</strong></p>";
    echo "<p>Puedes obtenerla manualmente desde: <a href='https://whatismyipaddress.com/' target='_blank'>whatismyipaddress.com</a></p>";
}

// Procesar aplicación automática de cambios
if (isset($_POST['apply_config']) && isset($_POST['public_ip'])) {
    $publicIP = $_POST['public_ip'];
    
    echo "<hr>";
    echo "<h2>🔧 Aplicando configuración...</h2>";
    
    $configFile = '../config/config.php';
    $configContent = file_get_contents($configFile);
    
    if ($configContent) {
        // Crear el nuevo bloque de configuración
        $newShellyConfig = "// Configuración de Shelly Relay API con Port Forwarding
define('SHELLY_API_URL', 'http://$publicIP/'); // IP pública con port forwarding
define('SHELLY_API_TIMEOUT', 15); // Timeout aumentado para conexión externa
define('SHELLY_SWITCH_ID', 0);  // ID del switch para abrir/cerrar barrera
define('SHELLY_ENABLED', true); // Habilitado con port forwarding
// URLs completas para las acciones
define('SHELLY_OPEN_URL', 'http://$publicIP/rpc/Switch.Set?id=0&on=false');  // Abrir
define('SHELLY_CLOSE_URL', 'http://$publicIP/rpc/Switch.Set?id=0&on=true');  // Cerrar";
        
        // Buscar y reemplazar el bloque existente
        $pattern = '/\/\/ Configuración de Shelly Relay API.*?define\(\'SHELLY_CLOSE_URL\'[^;]*\);/s';
        
        if (preg_match($pattern, $configContent)) {
            $newContent = preg_replace($pattern, $newShellyConfig, $configContent);
            
            // Hacer backup
            $backupFile = '../config/config.php.backup.' . date('Y-m-d-H-i-s');
            file_put_contents($backupFile, $configContent);
            
            // Escribir nueva configuración
            if (file_put_contents($configFile, $newContent)) {
                echo "<p style='color: green;'><strong>✅ Configuración actualizada exitosamente!</strong></p>";
                echo "<p><strong>Backup creado:</strong> $backupFile</p>";
                
                echo "<h3>🧪 Próximo paso:</h3>";
                echo "<p><a href='test-shelly.php' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Probar conexión con Shelly</a></p>";
                
            } else {
                echo "<p style='color: red;'><strong>❌ Error al escribir el archivo de configuración.</strong></p>";
            }
        } else {
            echo "<p style='color: red;'><strong>❌ No se encontró el bloque de configuración de Shelly.</strong></p>";
        }
    } else {
        echo "<p style='color: red;'><strong>❌ No se pudo leer el archivo de configuración.</strong></p>";
    }
}
?>