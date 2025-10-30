<?php
/**
 * Test final con credenciales específicas del Shelly Pro 4PM V2
 * MAC: 34987A67DA6C
 * SSID: ShellyPro4PM-34987A67DA6C
 */

require_once __DIR__ . '/../config/config.php';

echo "<h1>🎯 Test Final - Credenciales Específicas</h1>";

// Obtener IP del Shelly
$shellyIP = str_replace(['http://', 'https://', '/'], '', SHELLY_API_URL);
$shellyIP = rtrim($shellyIP, '/');

echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>✅ Información confirmada del dispositivo:</h3>";
echo "<ul>";
echo "<li><strong>Device Type:</strong> Shelly Pro 4 PM</li>";
echo "<li><strong>Device Model:</strong> SPSW-104PE16EU</li>";
echo "<li><strong>Device ID:</strong> 34987a67da6c</li>";
echo "<li><strong>Device IP:</strong> 192.168.1.95 (confirmada)</li>";
echo "<li><strong>Firmware:</strong> 20240625-123010/1.3.3-gbdfd9b3</li>";
echo "<li><strong>Last Seen:</strong> 29-10-25 15:41:54</li>";
echo "<li><strong>IP actual del test:</strong> $shellyIP</li>";
echo "</ul>";
echo "</div>";

// Credenciales específicas basadas en Device ID confirmado: 34987a67da6c
$specificCredentials = [
    ['admin', '67da6c', 'Últimos 6 dígitos de Device ID (minúsculas) - MÁS PROBABLE'],
    ['admin', '67DA6C', 'Últimos 6 dígitos de Device ID (MAYÚSCULAS)'],
    ['admin', 'da6c', 'Últimos 4 dígitos de Device ID (minúsculas)'],
    ['admin', 'DA6C', 'Últimos 4 dígitos de Device ID (MAYÚSCULAS)'],
    ['admin', '', 'Contraseña vacía (factory reset)'],
    ['admin', '34987a67da6c', 'Device ID completo (minúsculas)'],
    ['admin', '34987A67DA6C', 'Device ID completo (MAYÚSCULAS)'],
    ['admin', '34987a', 'Primeros 6 dígitos de Device ID (minúsculas)'],
    ['admin', '34987A', 'Primeros 6 dígitos de Device ID (MAYÚSCULAS)'],
    ['admin', 'admin', 'Contraseña admin clásica'],
];

echo "<h2>🧪 Probando credenciales específicas</h2>";
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
    
    // Probar la credencial
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://$shellyIP/shelly");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
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
        
        echo "statusCell.innerHTML = '🎉 ¡CREDENCIALES ENCONTRADAS!';";
        
        break; // Salir del bucle cuando encontremos credenciales válidas
        
    } else {
        echo "statusCell.innerHTML = '❌ Falló';";
        echo "statusCell.style.color = '#dc3545';";
        echo "resultCell.innerHTML = 'HTTP $httpCode" . ($error ? " - $error" : "") . "';";
        echo "resultCell.style.color = '#dc3545';";
        echo "row.style.backgroundColor = '#f8d7da';";
    }
    
    echo "</script>";
    flush();
    
    usleep(1000000); // Pausa de 1 segundo entre pruebas
}

echo "</table>";

if ($foundCredentials) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h2>🎉 ¡ÉXITO! Credenciales encontradas</h2>";
    echo "<div style='font-size: 18px; margin: 15px 0;'>";
    echo "<p><strong>✅ Usuario:</strong> <code style='background: #e9ecef; padding: 5px;'>$workingUsername</code></p>";
    echo "<p><strong>✅ Contraseña:</strong> <code style='background: #e9ecef; padding: 5px;'>" . ($workingPassword ? $workingPassword : '(vacía)') . "</code></p>";
    echo "</div>";
    
    echo "<h3>🔧 Configurar el sistema automáticamente</h3>";
    echo "<form method='post' action='setup-auth.php'>";
    echo "<input type='hidden' name='username' value='$workingUsername'>";
    echo "<input type='hidden' name='password' value='$workingPassword'>";
    echo "<button type='submit' style='background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin: 10px 0;'>🚀 Configurar sistema con estas credenciales</button>";
    echo "</form>";
    
    echo "<h3>🧪 Probar control del relay</h3>";
    echo "<div style='margin: 15px 0;'>";
    echo "<button onclick='testRelay(\"on\")' style='background: #dc3545; color: white; padding: 12px 20px; border: none; border-radius: 5px; margin: 5px; cursor: pointer;'>🔒 Cerrar Barrera (ON)</button>";
    echo "<button onclick='testRelay(\"off\")' style='background: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 5px; margin: 5px; cursor: pointer;'>🔓 Abrir Barrera (OFF)</button>";
    echo "</div>";
    echo "<div id='relay-result' style='margin: 15px 0; padding: 10px; border-radius: 5px;'></div>";
    
    echo "<script>";
    echo "function testRelay(action) {";
    echo "  var resultDiv = document.getElementById('relay-result');";
    echo "  resultDiv.innerHTML = '🧪 Probando relay ' + action + '...';";
    echo "  resultDiv.style.background = '#fff3cd';";
    echo "  resultDiv.style.border = '1px solid #ffeaa7';";
    echo "  ";
    echo "  var xhr = new XMLHttpRequest();";
    echo "  xhr.open('GET', 'test-relay.php?action=' + action + '&username=$workingUsername&password=$workingPassword', true);";
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
    
    echo "</div>";
    
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f1aeb5; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h2>❌ No se encontraron credenciales válidas</h2>";
    echo "<p>Ninguna de las combinaciones específicas funcionó. Esto podría indicar:</p>";
    echo "<ul>";
    echo "<li><strong>Las credenciales fueron cambiadas manualmente</strong></li>";
    echo "<li><strong>El dispositivo tiene un firmware personalizado</strong></li>";
    echo "<li><strong>Hay un problema de conectividad específico</strong></li>";
    echo "</ul>";
    
    echo "<h3>🔄 Solución recomendada: Reset del dispositivo</h3>";
    echo "<ol>";
    echo "<li><strong>Localiza el botón RESET</strong> en el Shelly Pro 4PM V2</li>";
    echo "<li><strong>Mantén presionado 10 segundos</strong> hasta que parpadee</li>";
    echo "<li><strong>Espera que reinicie</strong> completamente</li>";
    echo "<li><strong>Las credenciales serán:</strong> admin/(vacía)</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<style>";
echo "table { width: 100%; border-collapse: collapse; }";
echo "th, td { padding: 12px; text-align: left; border: 1px solid #dee2e6; }";
echo "th { background-color: #f8f9fa; font-weight: bold; }";
echo "code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }";
echo "</style>";
?>