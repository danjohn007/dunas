<?php
/**
 * Test específico para port forwarding configurado
 * Puerto 8095 -> 192.168.1.95:80
 */

require_once __DIR__ . '/../config/config.php';

echo "<h1>🔧 Test Port Forwarding Configurado</h1>";

echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>✅ Configuración confirmada en router:</h3>";
echo "<ul>";
echo "<li><strong>Nombre servicio:</strong> ShellyPort</li>";
echo "<li><strong>Dispositivo:</strong> 192.168.1.95</li>";
echo "<li><strong>Protocolo:</strong> TCP</li>";
echo "<li><strong>Puerto LAN:</strong> 80</li>";
echo "<li><strong>Puerto público:</strong> 9000 (ACTUALIZADO)</li>";
echo "<li><strong>Estado:</strong> Activado (SÍ)</li>";
echo "</ul>";
echo "</div>";

// Detectar IP pública
echo "<h2>🌐 Detectando IP pública</h2>";
$publicIP = null;
$services = [
    'https://api.ipify.org' => 'ipify.org',
    'https://icanhazip.com' => 'icanhazip.com',
    'https://ipecho.net/plain' => 'ipecho.net'
];

foreach ($services as $service => $name) {
    echo "<div style='padding: 10px; margin: 5px 0; background: #f8f9fa; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "Probando $name...";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $service);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyTest/1.0');
    
    $ip = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if (!$error && $ip && filter_var(trim($ip), FILTER_VALIDATE_IP)) {
        $publicIP = trim($ip);
        echo " ✅ <strong>$publicIP</strong>";
        echo "</div>";
        break;
    } else {
        echo " ❌ Error: " . ($error ?: 'Sin respuesta');
        echo "</div>";
    }
}

if (!$publicIP) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "❌ No se pudo detectar IP pública. Usando IP estática para pruebas.";
    echo "</div>";
    $publicIP = "TU_IP_PUBLICA"; // Placeholder
}

echo "<h2>🧪 Pruebas de conectividad</h2>";

// URLs de prueba con tu configuración exacta
$baseUrl = "http://$publicIP:9000";
$testUrls = [
    "Información básica" => "$baseUrl/shelly",
    "Status del dispositivo" => "$baseUrl/status", 
    "Comando ABRIR" => "$baseUrl/rpc/Switch.Set?id=0&on=false",
    "Comando CERRAR" => "$baseUrl/rpc/Switch.Set?id=0&on=true"
];

foreach ($testUrls as $descripcion => $url) {
    echo "<h3>$descripcion</h3>";
    echo "<div style='padding: 15px; margin: 10px 0; border: 2px solid #007bff; border-radius: 8px; background: #f8f9fa;'>";
    echo "<strong>URL:</strong> <code>$url</code><br>";
    echo "<strong>Credenciales:</strong> admin:67da6c<br>";
    echo "<strong>Probando...</strong><br>";
    flush();
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "admin:67da6c");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyAPI/1.0 (Dunas Control)');
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    
    $startTime = microtime(true);
    $response = curl_exec($ch);
    $endTime = microtime(true);
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $totalTime = $endTime - $startTime;
    $curlInfo = curl_getinfo($ch);
    curl_close($ch);
    
    if (!$error && $httpCode == 200) {
        echo "<span style='color: green; font-weight: bold;'>✅ ÉXITO</span><br>";
        echo "<strong>Tiempo:</strong> " . round($totalTime, 3) . "s<br>";
        echo "<strong>Respuesta:</strong> <code>" . htmlspecialchars(substr($response, 0, 200)) . "...</code><br>";
        
        if (strpos($descripcion, "ABRIR") !== false || strpos($descripcion, "CERRAR") !== false) {
            $jsonResponse = json_decode($response, true);
            if ($jsonResponse) {
                echo "<strong>Estado del relay:</strong> " . json_encode($jsonResponse) . "<br>";
            }
        }
    } else {
        echo "<span style='color: red; font-weight: bold;'>❌ ERROR</span><br>";
        echo "<strong>Código HTTP:</strong> $httpCode<br>";
        if ($error) {
            echo "<strong>Error cURL:</strong> $error<br>";
        }
        echo "<strong>IP de conexión:</strong> " . ($curlInfo['primary_ip'] ?? 'N/A') . "<br>";
        echo "<strong>Puerto de conexión:</strong> " . ($curlInfo['primary_port'] ?? 'N/A') . "<br>";
        
        // Diagnóstico adicional
        if (strpos($error, "Connection refused") !== false) {
            echo "<strong>💡 Diagnóstico:</strong> Port forwarding no está funcionando o el puerto está cerrado<br>";
        } elseif (strpos($error, "timeout") !== false) {
            echo "<strong>💡 Diagnóstico:</strong> Timeout - puede ser firewall o port forwarding incorrecto<br>";
        } elseif ($httpCode == 401) {
            echo "<strong>💡 Diagnóstico:</strong> Autenticación requerida - credenciales incorrectas<br>";
        } elseif ($httpCode == 404) {
            echo "<strong>💡 Diagnóstico:</strong> Endpoint no encontrado - URL incorrecta<br>";
        }
    }
    echo "</div>";
    
    // Pausa entre pruebas
    if (strpos($descripcion, "Comando") !== false) {
        sleep(2);
    }
}

echo "<h2>🔍 Diagnóstico adicional</h2>";

// Test de ping al puerto
echo "<h3>Test de conectividad básica</h3>";
echo "<div style='padding: 15px; border: 1px solid #6c757d; border-radius: 5px; background: #f8f9fa;'>";
echo "<p>Probando conectividad básica al puerto 9000...</p>";

$connection = @fsockopen($publicIP, 9000, $errno, $errstr, 10);
if ($connection) {
    echo "✅ <strong>Puerto 9000 está abierto y accesible</strong><br>";
    echo "El port forwarding está funcionando correctamente.<br>";
    fclose($connection);
} else {
    echo "❌ <strong>No se puede conectar al puerto 9000</strong><br>";
    echo "Error: $errstr ($errno)<br>";
    echo "<strong>Posibles causas:</strong><br>";
    echo "<ul>";
    echo "<li>El port forwarding no está activo aún (puede tardar unos minutos)</li>";
    echo "<li>Firewall del router bloqueando el puerto</li>";
    echo "<li>ISP bloqueando el puerto 9000</li>";
    echo "<li>Router necesita reinicio después de configurar port forwarding</li>";
    echo "</ul>";
}
echo "</div>";

echo "<h2>🛠️ Soluciones recomendadas</h2>";

echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🔧 Pasos a seguir:</h3>";
echo "<ol>";
echo "<li><strong>Reinicia el router</strong> - A veces el port forwarding necesita reinicio para activarse</li>";
echo "<li><strong>Verifica el firewall del router</strong> - Asegúrate de que no esté bloqueando el puerto 8095</li>";
echo "<li><strong>Prueba otro puerto</strong> - Algunos ISP bloquean ciertos puertos</li>";
echo "<li><strong>Verifica que el Shelly esté encendido</strong> - Debe estar accesible en 192.168.1.95</li>";
echo "</ol>";

echo "<h4>📋 Comandos de verificación local:</h4>";
echo "<p>Desde tu computadora local (para confirmar que el Shelly funciona):</p>";
echo "<textarea readonly style='width: 100%; height: 80px; font-family: monospace; padding: 10px;'>";
echo "curl -u admin:67da6c \"http://192.168.1.95/shelly\"\n";
echo "curl -u admin:67da6c \"http://192.168.1.95/rpc/Switch.Set?id=0&on=false\"\n";
echo "curl -u admin:67da6c \"http://192.168.1.95/rpc/Switch.Set?id=0&on=true\"";
echo "</textarea>";

echo "<h4>🔄 Alternativas de puerto:</h4>";
echo "<p>Si el puerto 9000 no funciona, prueba estos en tu router:</p>";
echo "<ul>";
echo "<li><strong>Puerto 3000</strong> (desarrollo web común)</li>";
echo "<li><strong>Puerto 8443</strong> (HTTPS alternativo)</li>";
echo "<li><strong>Puerto 2222</strong> (SSH alternativo)</li>";
echo "</ul>";
echo "</div>";

echo "<style>";
echo "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 20px; line-height: 1.6; }";
echo "h1, h2, h3 { color: #333; }";
echo "code { background: #f1f3f4; padding: 2px 6px; border-radius: 4px; font-family: 'Consolas', monospace; }";
echo "textarea { border: 1px solid #ddd; border-radius: 4px; }";
echo "</style>";
?>