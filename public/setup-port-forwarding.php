<?php
/**
 * Diagnóstico y configuración de Port Forwarding para Shelly
 */

require_once __DIR__ . '/../config/config.php';

echo "<h1>🔧 Configuración de Port Forwarding - Shelly</h1>";

echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>⚠️ Problema detectado:</h3>";
echo "<p><strong>Tu servidor web está en internet pero el Shelly está en tu red local.</strong></p>";
echo "<p>El servidor web no puede acceder directamente a <code>192.168.1.95</code> porque es una IP privada.</p>";
echo "</div>";

// Obtener IP pública
echo "<h2>🌐 Información de red</h2>";

echo "<h3>1. Tu IP pública actual:</h3>";
$publicIP = null;
$services = [
    'https://api.ipify.org' => 'ipify.org',
    'https://icanhazip.com' => 'icanhazip.com',
    'https://ipecho.net/plain' => 'ipecho.net'
];

foreach ($services as $service => $name) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $service);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyAPI/1.0');
    
    $ip = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if (!$error && $ip && filter_var(trim($ip), FILTER_VALIDATE_IP)) {
        $publicIP = trim($ip);
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ <strong>IP pública detectada:</strong> <code>$publicIP</code> (vía $name)";
        echo "</div>";
        break;
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ <strong>Error con $name:</strong> " . ($error ?: 'Sin respuesta');
        echo "</div>";
    }
}

if (!$publicIP) {
    echo "<div style='background: #f8d7da; border: 1px solid #f1aeb5; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ No se pudo obtener IP pública</h4>";
    echo "<p>No se puede configurar port forwarding sin conocer tu IP pública.</p>";
    echo "</div>";
    exit;
}

echo "<h3>2. Configuración actual del sistema:</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<ul>";
echo "<li><strong>SHELLY_API_URL:</strong> " . SHELLY_API_URL . "</li>";
echo "<li><strong>SHELLY_OPEN_URL:</strong> " . SHELLY_OPEN_URL . "</li>";
echo "<li><strong>SHELLY_CLOSE_URL:</strong> " . SHELLY_CLOSE_URL . "</li>";
echo "<li><strong>IP del Shelly (local):</strong> 192.168.1.95</li>";
echo "<li><strong>Tu IP pública:</strong> $publicIP</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🔧 Configuración requerida en tu router</h2>";

echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📋 Pasos para configurar Port Forwarding:</h3>";
echo "<ol style='font-size: 16px; line-height: 1.6;'>";
echo "<li><strong>Accede a tu router:</strong>";
echo "<ul>";
echo "<li>Abre tu navegador y ve a <code>192.168.1.1</code> o <code>192.168.0.1</code></li>";
echo "<li>Inicia sesión con las credenciales del router</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Busca la sección 'Port Forwarding' o 'NAT'</strong></li>";
echo "<li><strong>Configura una nueva regla:</strong>";
echo "<ul>";
echo "<li><strong>Nombre:</strong> Shelly-Control</li>";
echo "<li><strong>Puerto externo:</strong> 8095</li>";
echo "<li><strong>IP interna:</strong> 192.168.1.95</li>";
echo "<li><strong>Puerto interno:</strong> 80</li>";
echo "<li><strong>Protocolo:</strong> TCP</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Guarda la configuración</strong> y reinicia el router si es necesario</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🧪 Pruebas de conectividad</h2>";

// Test 1: Verificar si el port forwarding ya está configurado
echo "<h3>1. Probando acceso vía port forwarding</h3>";
$portForwardURL = "http://$publicIP:8095/shelly";
echo "<div style='padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Probando:</strong> <code>$portForwardURL</code><br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $portForwardURL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, SHELLY_USERNAME . ':' . SHELLY_PASSWORD);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if (!$error && $httpCode == 200) {
    echo "✅ <strong style='color: green;'>¡Port forwarding funcionando!</strong><br>";
    echo "📄 Respuesta del Shelly recibida correctamente<br>";
    $forwardingWorking = true;
} else {
    echo "❌ <strong style='color: red;'>Port forwarding no configurado</strong><br>";
    if ($error) {
        echo "🔍 Error: $error<br>";
    }
    echo "🔢 Código HTTP: $httpCode<br>";
    $forwardingWorking = false;
}
echo "</div>";

// Test 2: Si el port forwarding funciona, probar comandos
if ($forwardingWorking) {
    echo "<h3>2. Probando comandos de control</h3>";
    
    // Test abrir
    echo "<h4>🔓 Comando ABRIR</h4>";
    $openURL = "http://$publicIP:8095/rpc/Switch.Set?id=0&on=false";
    echo "<div style='padding: 15px; border: 1px solid #28a745; border-radius: 5px; margin: 10px 0; background: #d4edda;'>";
    echo "<strong>URL:</strong> <code>$openURL</code><br>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $openURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, SHELLY_USERNAME . ':' . SHELLY_PASSWORD);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if (!$error && $httpCode == 200) {
        echo "✅ <strong>Comando ABRIR exitoso</strong><br>";
        echo "📄 Respuesta: <code>$response</code>";
    } else {
        echo "❌ <strong>Error en comando ABRIR</strong><br>";
        echo "🔍 Error: " . ($error ?: 'HTTP ' . $httpCode);
    }
    echo "</div>";
    
    sleep(2);
    
    // Test cerrar
    echo "<h4>🔒 Comando CERRAR</h4>";
    $closeURL = "http://$publicIP:8095/rpc/Switch.Set?id=0&on=true";
    echo "<div style='padding: 15px; border: 1px solid #dc3545; border-radius: 5px; margin: 10px 0; background: #f8d7da;'>";
    echo "<strong>URL:</strong> <code>$closeURL</code><br>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $closeURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, SHELLY_USERNAME . ':' . SHELLY_PASSWORD);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if (!$error && $httpCode == 200) {
        echo "✅ <strong>Comando CERRAR exitoso</strong><br>";
        echo "📄 Respuesta: <code>$response</code>";
    } else {
        echo "❌ <strong>Error en comando CERRAR</strong><br>";
        echo "🔍 Error: " . ($error ?: 'HTTP ' . $httpCode);
    }
    echo "</div>";
}

echo "<h2>📋 Resumen y próximos pasos</h2>";

if ($forwardingWorking) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px;'>";
    echo "<h3>🎉 ¡Port forwarding configurado correctamente!</h3>";
    echo "<p>Tu sistema web ya puede controlar el Shelly remotamente.</p>";
    echo "<p><strong>URLs que funcionan:</strong></p>";
    echo "<ul>";
    echo "<li>Abrir: <code>http://$publicIP:8095/rpc/Switch.Set?id=0&on=false</code></li>";
    echo "<li>Cerrar: <code>http://$publicIP:8095/rpc/Switch.Set?id=0&on=true</code></li>";
    echo "</ul>";
    echo "<p><strong>Ahora ve a:</strong></p>";
    echo "<ul>";
    echo "<li><a href='https://fix360.app/dunas/dunasshelly/public/access/create' target='_blank'>Registrar Entrada</a> (debería abrir la barrera)</li>";
    echo "<li>Registrar Salida desde los detalles del acceso (debería cerrar la barrera)</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f1aeb5; padding: 20px; border-radius: 5px;'>";
    echo "<h3>❌ Port forwarding no configurado</h3>";
    echo "<p><strong>Necesitas configurar port forwarding en tu router:</strong></p>";
    echo "<ol>";
    echo "<li>Accede a tu router (192.168.1.1 o 192.168.0.1)</li>";
    echo "<li>Configura port forwarding: Puerto 8095 → 192.168.1.95:80</li>";
    echo "<li>Vuelve a ejecutar este script para verificar</li>";
    echo "</ol>";
    
    echo "<h4>🔍 URLs de prueba que deberían funcionar después:</h4>";
    echo "<ul>";
    echo "<li><code>http://$publicIP:8095/shelly</code> (información del dispositivo)</li>";
    echo "<li><code>http://$publicIP:8095/rpc/Switch.Set?id=0&on=false</code> (abrir)</li>";
    echo "<li><code>http://$publicIP:8095/rpc/Switch.Set?id=0&on=true</code> (cerrar)</li>";
    echo "</ul>";
    
    echo "<h4>📖 Guías por marca de router:</h4>";
    echo "<ul>";
    echo "<li><strong>TP-Link:</strong> Advanced → NAT Forwarding → Port Forwarding</li>";
    echo "<li><strong>Netgear:</strong> Dynamic DNS → Port Forwarding</li>";
    echo "<li><strong>Linksys:</strong> Smart Wi-Fi Tools → Port Range Forwarding</li>";
    echo "<li><strong>D-Link:</strong> Advanced → Port Forwarding</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<style>";
echo "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 20px; line-height: 1.6; }";
echo "h1, h2, h3, h4 { color: #333; }";
echo "code { background: #f1f3f4; padding: 2px 6px; border-radius: 4px; font-family: 'Consolas', monospace; }";
echo "ol, ul { padding-left: 20px; }";
echo "li { margin: 8px 0; }";
echo "</style>";
?>