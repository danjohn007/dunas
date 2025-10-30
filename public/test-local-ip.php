<?php
/**
 * Test directo contra IP local del Shelly Pro 4PM
 * IP Local: 192.168.1.95
 * Device ID: 34987a67da6c
 */

echo "<h1>🎯 Test Directo - IP Local del Shelly</h1>";

// IP local del Shelly (NO la IP pública del servidor)
$shellyLocalIP = "192.168.1.95";

echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>⚠️ Problema identificado:</h3>";
echo "<ul>";
echo "<li><strong>IP del servidor:</strong> 162.215.121.70 (donde se ejecuta este script)</li>";
echo "<li><strong>IP del Shelly:</strong> 192.168.1.95 (en tu red local)</li>";
echo "<li><strong>El problema:</strong> El servidor no puede acceder directamente a tu red local</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>✅ Información confirmada del dispositivo:</h3>";
echo "<ul>";
echo "<li><strong>Device Type:</strong> Shelly Pro 4 PM</li>";
echo "<li><strong>Device Model:</strong> SPSW-104PE16EU</li>";
echo "<li><strong>Device ID:</strong> 34987a67da6c</li>";
echo "<li><strong>IP Local:</strong> $shellyLocalIP</li>";
echo "<li><strong>Firmware:</strong> 20240625-123010/1.3.3-gbdfd9b3</li>";
echo "</ul>";
echo "</div>";

// Credenciales más probables basadas en Device ID
$specificCredentials = [
    ['admin', '67da6c', 'Últimos 6 dígitos de Device ID (minúsculas) - MÁS PROBABLE'],
    ['admin', '67DA6C', 'Últimos 6 dígitos de Device ID (MAYÚSCULAS)'],
    ['admin', '', 'Contraseña vacía (factory reset)'],
    ['admin', 'da6c', 'Últimos 4 dígitos de Device ID (minúsculas)'],
    ['admin', 'DA6C', 'Últimos 4 dígitos de Device ID (MAYÚSCULAS)'],
    ['admin', 'admin', 'Contraseña admin clásica'],
];

echo "<h2>🧪 Probando credenciales contra IP local</h2>";
echo "<p><strong>Probando contra:</strong> <code>$shellyLocalIP</code></p>";

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
echo "<tr style='background: #f8f9fa;'><th>Usuario</th><th>Contraseña</th><th>Descripción</th><th>Estado</th><th>Resultado</th></tr>";

$foundCredentials = false;
$workingUsername = '';
$workingPassword = '';

foreach ($specificCredentials as $index => $cred) {
    $username = $cred[0];
    $password = $cred[1];
    $description = $cred[2];
    
    echo "<tr id='row_$index'>";
    echo "<td><strong>$username</strong></td>";
    echo "<td><code>" . ($password ? $password : '(vacía)') . "</code></td>";
    echo "<td>$description</td>";
    echo "<td id='status_$index' style='color: #007bff;'>🧪 Probando...</td>";
    echo "<td id='result_$index'>-</td>";
    echo "</tr>";
    
    echo "<script>document.getElementById('status_$index').scrollIntoView();</script>";
    flush();
    
    // Probar la credencial contra IP local
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://$shellyLocalIP/shelly");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Actualizar resultado en tiempo real
    echo "<script>";
    echo "var statusCell = document.getElementById('status_$index');";
    echo "var resultCell = document.getElementById('result_$index');";
    echo "var row = document.getElementById('row_$index');";
    
    if (!$error && $httpCode == 200) {
        echo "statusCell.innerHTML = '✅ ¡FUNCIONA!';";
        echo "statusCell.style.color = '#28a745';";
        echo "resultCell.innerHTML = '<strong>HTTP 200 - OK</strong>';";
        echo "resultCell.style.color = '#28a745';";
        echo "row.style.backgroundColor = '#d4edda';";
        
        $foundCredentials = true;
        $workingUsername = $username;
        $workingPassword = $password;
        
        break; // Salir del bucle cuando encontremos credenciales válidas
        
    } else {
        echo "statusCell.innerHTML = '❌ Falló';";
        echo "statusCell.style.color = '#dc3545';";
        
        if ($error) {
            echo "resultCell.innerHTML = 'Error: $error';";
        } else {
            echo "resultCell.innerHTML = 'HTTP $httpCode';";
        }
        echo "resultCell.style.color = '#dc3545';";
        echo "row.style.backgroundColor = '#f8d7da';";
    }
    
    echo "</script>";
    flush();
    
    usleep(1000000); // Pausa de 1 segundo
}

echo "</table>";

if ($foundCredentials) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h2>🎉 ¡ÉXITO! Credenciales encontradas</h2>";
    echo "<div style='font-size: 18px; margin: 15px 0;'>";
    echo "<p><strong>✅ Usuario:</strong> <code style='background: #e9ecef; padding: 5px;'>$workingUsername</code></p>";
    echo "<p><strong>✅ Contraseña:</strong> <code style='background: #e9ecef; padding: 5px;'>" . ($workingPassword ? $workingPassword : '(vacía)') . "</code></p>";
    echo "</div>";
    
    echo "<h3>🧪 Probar control del relay</h3>";
    echo "<div style='margin: 15px 0;'>";
    echo "<button onclick='testLocalRelay(\"on\")' style='background: #dc3545; color: white; padding: 12px 20px; border: none; border-radius: 5px; margin: 5px; cursor: pointer;'>🔒 Cerrar Barrera (ON)</button>";
    echo "<button onclick='testLocalRelay(\"off\")' style='background: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 5px; margin: 5px; cursor: pointer;'>🔓 Abrir Barrera (OFF)</button>";
    echo "</div>";
    echo "<div id='relay-result' style='margin: 15px 0; padding: 10px; border-radius: 5px;'></div>";
    
    echo "<script>";
    echo "function testLocalRelay(action) {";
    echo "  var resultDiv = document.getElementById('relay-result');";
    echo "  resultDiv.innerHTML = '🧪 Probando relay ' + action + ' contra IP local...';";
    echo "  resultDiv.style.background = '#fff3cd';";
    echo "  resultDiv.style.border = '1px solid #ffeaa7';";
    echo "  ";
    echo "  var xhr = new XMLHttpRequest();";
    echo "  xhr.open('GET', 'test-local-relay.php?action=' + action + '&username=$workingUsername&password=$workingPassword', true);";
    echo "  xhr.onreadystatechange = function() {";
    echo "    if (xhr.readyState === 4) {";
    echo "      if (xhr.status === 200) {";
    echo "        resultDiv.innerHTML = '✅ ' + xhr.responseText;";
    echo "        resultDiv.style.background = '#d4edda';";
    echo "        resultDiv.style.border = '1px solid #c3e6cb';";
    echo "      } else {";
    echo "        resultDiv.innerHTML = '❌ Error: ' + xhr.status;";
    echo "        resultDiv.style.background = '#f8d7da';";
    echo "        resultDiv.style.border = '1px solid #f1aeb5';";
    echo "      }";
    echo "    }";
    echo "  };";
    echo "  xhr.send();";
    echo "}";
    echo "</script>";
    
    echo "<h3>🔧 Configurar sistema</h3>";
    echo "<p>Una vez confirmado que funciona, actualizar el archivo de configuración:</p>";
    echo "<textarea readonly style='width: 100%; height: 100px; font-family: monospace; padding: 10px;'>";
    echo "// En config/config.php\n";
    echo "define('SHELLY_USERNAME', '$workingUsername');\n";
    echo "define('SHELLY_PASSWORD', '$workingPassword');\n";
    echo "define('SHELLY_API_URL', 'http://192.168.1.95');\n";
    echo "</textarea>";
    
    echo "</div>";
    
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f1aeb5; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h2>❌ No se pudieron probar las credenciales</h2>";
    echo "<p><strong>Posibles razones:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Servidor no puede acceder a red local:</strong> El servidor está en internet pero el Shelly en tu red local</li>";
    echo "<li><strong>Firewall bloqueando:</strong> Tu router podría estar bloqueando conexiones externas</li>";
    echo "<li><strong>Red privada:</strong> 192.168.1.95 no es accesible desde internet</li>";
    echo "</ul>";
    
    echo "<h3>🏠 Solución: Prueba desde tu red local</h3>";
    echo "<ol>";
    echo "<li><strong>Descarga este script</strong> a una computadora en tu red local</li>";
    echo "<li><strong>Ejecutalo localmente</strong> o accede desde tu navegador local</li>";
    echo "<li><strong>O usa curl/wget</strong> desde tu computadora local</li>";
    echo "</ol>";
    
    echo "<h4>📋 Comandos para probar desde tu computadora local:</h4>";
    echo "<textarea readonly style='width: 100%; height: 120px; font-family: monospace; padding: 10px;'>";
    echo "# Probar sin credenciales\n";
    echo "curl -v http://192.168.1.95/shelly\n\n";
    echo "# Probar con credenciales más probables\n";
    echo "curl -u admin:67da6c http://192.168.1.95/shelly\n";
    echo "curl -u admin:67DA6C http://192.168.1.95/shelly\n";
    echo "curl -u admin: http://192.168.1.95/shelly\n";
    echo "</textarea>";
    
    echo "</div>";
}

echo "<style>";
echo "table { width: 100%; border-collapse: collapse; }";
echo "th, td { padding: 12px; text-align: left; border: 1px solid #dee2e6; }";
echo "th { background-color: #f8f9fa; font-weight: bold; }";
echo "code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }";
echo "</style>";
?>