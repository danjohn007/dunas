<?php
/**
 * Test específico para Shelly Pro 4PM V2 con información del dispositivo
 */

require_once __DIR__ . '/../config/config.php';

echo "<h1>🔍 Test Específico - Shelly Pro 4PM V2</h1>";
echo "<p><strong>Información del dispositivo detectada:</strong></p>";

// Obtener IP del Shelly
$shellyIP = str_replace(['http://', 'https://', '/'], '', SHELLY_API_URL);
$shellyIP = rtrim($shellyIP, '/');

echo "<div style='background: #e7f3ff; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📋 Información conocida del dispositivo:</h3>";
echo "<ul>";
echo "<li><strong>Device name:</strong> Shelly Pro 4PM V2</li>";
echo "<li><strong>Device model:</strong> SPSW-104PE16EU</li>";
echo "<li><strong>Device SSID:</strong> ShellyPro4PM-XXXXXX</li>";
echo "<li><strong>IP actual:</strong> $shellyIP</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🔐 Necesitamos los últimos dígitos del SSID</h2>";
echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🔍 ¿Puedes ver los últimos dígitos del SSID?</h3>";
echo "<p>En lugar de <code>ShellyPro4PM-XXXXXX</code>, deberías ver algo como:</p>";
echo "<ul>";
echo "<li><code>ShellyPro4PM-34987A</code></li>";
echo "<li><code>ShellyPro4PM-A67DA6</code></li>";
echo "<li><code>ShellyPro4PM-123456</code></li>";
echo "</ul>";
echo "<p><strong>Estos últimos 6 dígitos son generalmente la contraseña por defecto.</strong></p>";
echo "</div>";

// Formulario para ingresar el SSID completo
echo "<form method='post' style='background: #f8f9fa; padding: 20px; border-radius: 5px; border: 1px solid #dee2e6; margin: 20px 0;'>";
echo "<h3>📝 Ingresar SSID completo</h3>";
echo "<p>Si puedes ver el SSID completo, ingrésalo aquí:</p>";
echo "<div style='margin: 15px 0;'>";
echo "<label style='display: block; margin-bottom: 5px;'><strong>SSID completo:</strong></label>";
echo "<input type='text' name='full_ssid' placeholder='ShellyPro4PM-123456' style='width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;'>";
echo "<br><small>Ejemplo: ShellyPro4PM-A67DA6</small>";
echo "</div>";
echo "<button type='submit' name='test_ssid' style='background: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer;'>🧪 Probar con SSID</button>";
echo "</form>";

// Si se proporciona el SSID, probar
if (isset($_POST['test_ssid']) && !empty($_POST['full_ssid'])) {
    $fullSSID = trim($_POST['full_ssid']);
    
    echo "<h2>🧪 Probando con SSID: $fullSSID</h2>";
    
    // Extraer los últimos dígitos del SSID
    $ssidParts = explode('-', $fullSSID);
    $lastPart = end($ssidParts);
    
    echo "<p><strong>Últimos dígitos extraídos:</strong> $lastPart</p>";
    
    // Generar credenciales basadas en el SSID
    $ssidCredentials = [
        ['admin', strtolower($lastPart), 'SSID últimos dígitos (minúsculas)'],
        ['admin', strtoupper($lastPart), 'SSID últimos dígitos (MAYÚSCULAS)'],
        ['admin', $lastPart, 'SSID últimos dígitos (original)'],
    ];
    
    // Si tiene 6 dígitos, probar variaciones
    if (strlen($lastPart) == 6) {
        $ssidCredentials[] = ['admin', substr($lastPart, -4), 'Últimos 4 dígitos del SSID'];
        $ssidCredentials[] = ['admin', substr($lastPart, 0, 4), 'Primeros 4 dígitos del SSID'];
    }
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr><th>Usuario</th><th>Contraseña</th><th>Descripción</th><th>Resultado</th></tr>";
    
    $foundCredentials = false;
    
    foreach ($ssidCredentials as $cred) {
        $username = $cred[0];
        $password = $cred[1];
        $description = $cred[2];
        
        echo "<tr>";
        echo "<td>$username</td>";
        echo "<td>$password</td>";
        echo "<td>$description</td>";
        echo "<td>🧪 Probando...</td>";
        echo "</tr>";
        flush();
        
        // Probar la credencial
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://$shellyIP/shelly");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Actualizar resultado
        echo "<script>";
        echo "var rows = document.querySelectorAll('tr');";
        echo "var lastRow = rows[rows.length - 1];";
        
        if (!$error && $httpCode == 200) {
            echo "lastRow.style.backgroundColor = '#d4edda';";
            echo "lastRow.cells[3].innerHTML = '✅ ¡FUNCIONA!';";
            $foundCredentials = true;
            
            // Guardar las credenciales que funcionan
            $workingUsername = $username;
            $workingPassword = $password;
        } else {
            echo "lastRow.style.backgroundColor = '#f8d7da';";
            echo "lastRow.cells[3].innerHTML = '❌ HTTP $httpCode';";
        }
        
        echo "</script>";
        flush();
        
        usleep(500000); // Pausa de 0.5 segundos
    }
    
    echo "</table>";
    
    if ($foundCredentials) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>🎉 ¡Credenciales encontradas!</h3>";
        echo "<p><strong>Usuario:</strong> $workingUsername</p>";
        echo "<p><strong>Contraseña:</strong> $workingPassword</p>";
        
        echo "<form method='post' action='setup-auth.php'>";
        echo "<input type='hidden' name='username' value='$workingUsername'>";
        echo "<input type='hidden' name='password' value='$workingPassword'>";
        echo "<button type='submit' style='background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>🔧 Configurar sistema con estas credenciales</button>";
        echo "</form>";
        echo "</div>";
    }
}

// Método alternativo: Reset del dispositivo
echo "<h2>🔄 Método alternativo: Reset del dispositivo</h2>";
echo "<div style='background: #f8d7da; border: 1px solid #f1aeb5; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>⚠️ Reset a configuración de fábrica</h3>";
echo "<p><strong>Si no puedes encontrar el SSID completo, puedes resetear el dispositivo:</strong></p>";
echo "<ol>";
echo "<li><strong>Localiza el botón RESET</strong> en el Shelly Pro 4PM V2</li>";
echo "<li><strong>Con el dispositivo encendido,</strong> mantén presionado el botón RESET durante <strong>10 segundos</strong></li>";
echo "<li><strong>Suelta el botón</strong> y espera a que el dispositivo reinicie</li>";
echo "<li><strong>El LED parpadeará</strong> indicando que se está reseteando</li>";
echo "<li><strong>Después del reset:</strong>";
echo "<ul>";
echo "<li>Usuario: <code>admin</code></li>";
echo "<li>Contraseña: (vacía)</li>";
echo "<li>IP: Podría cambiar, búscala de nuevo</li>";
echo "</ul>";
echo "</li>";
echo "</ol>";
echo "<p><strong>⚠️ IMPORTANTE:</strong> Perderás toda la configuración actual del dispositivo.</p>";
echo "</div>";

// Método alternativo: Acceso desde red local
echo "<h2>🏠 Método alternativo: Acceso desde red local</h2>";
echo "<div style='background: #e2e3e5; border: 1px solid #d1ecf1; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>💻 Acceso directo desde tu red WiFi</h3>";
echo "<p><strong>Si tienes una computadora conectada a la misma red WiFi:</strong></p>";
echo "<ol>";
echo "<li><strong>Conecta una laptop/PC</strong> a la misma red WiFi donde está el Shelly</li>";
echo "<li><strong>Abre el navegador</strong> y ve a: <a href='http://$shellyIP' target='_blank'>http://$shellyIP</a></li>";
echo "<li><strong>Desde la red local</strong> podría no pedir credenciales</li>";
echo "<li><strong>Si accedes,</strong> ve a Settings → Authentication para ver/cambiar las credenciales</li>";
echo "</ol>";
echo "<p><strong>Nota:</strong> Tu servidor web está en internet, pero el Shelly en red local, por eso las credenciales podrían ser diferentes.</p>";
echo "</div>";

// Información sobre la V2
echo "<h2>📖 Información específica del Shelly Pro 4PM V2</h2>";
echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px;'>";
echo "<h4>🔍 Características de la V2:</h4>";
echo "<ul>";
echo "<li><strong>Modelo:</strong> SPSW-104PE16EU (Europa, 16A)</li>";
echo "<li><strong>Credenciales por defecto:</strong> Generalmente admin + últimos dígitos del SSID</li>";
echo "<li><strong>SSID format:</strong> ShellyPro4PM-[6 dígitos]</li>";
echo "<li><strong>Botón reset:</strong> Mantener 10 segundos para factory reset</li>";
echo "<li><strong>API:</strong> Soporta tanto RPC como API clásica</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🆘 Si nada funciona</h2>";
echo "<div style='background: #e7f3ff; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px;'>";
echo "<h4>📞 Opciones adicionales:</h4>";
echo "<ul>";
echo "<li><strong>Manual oficial:</strong> <a href='https://shelly-api-docs.shelly.cloud/' target='_blank'>Shelly API Docs</a></li>";
echo "<li><strong>Soporte Shelly:</strong> <a href='https://support.shelly.cloud/' target='_blank'>support.shelly.cloud</a></li>";
echo "<li><strong>App móvil:</strong> Shelly Smart Control (podría mostrar credenciales)</li>";
echo "</ul>";
echo "</div>";

echo "<style>";
echo "table { margin: 20px 0; }";
echo "th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }";
echo "th { background-color: #f2f2f2; }";
echo "</style>";
?>