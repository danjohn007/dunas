<?php
/**
 * Configurador de autenticación para Shelly
 */

require_once __DIR__ . '/../config/config.php';

echo "<h1>🔐 Configurador de Autenticación Shelly</h1>";
echo "<p><strong>Problema detectado:</strong> HTTP 302 indica que tu Shelly requiere autenticación</p>";

// Obtener IP del Shelly
$shellyIP = str_replace(['http://', 'https://', '/'], '', SHELLY_API_URL);
$shellyIP = rtrim($shellyIP, '/');

echo "<h2>📋 Información:</h2>";
echo "<p><strong>IP del Shelly:</strong> $shellyIP</p>";
echo "<p><strong>Interfaz web:</strong> <a href='http://$shellyIP' target='_blank'>http://$shellyIP</a></p>";

// Formulario para ingresar credenciales
if (!isset($_POST['username'])) {
    echo "<h2>🔑 Configurar Credenciales</h2>";
    echo "<p>Para usar tu Shelly, necesitas las credenciales de acceso.</p>";
    
    echo "<div style='background: #e7f3ff; border: 1px solid #bee5eb; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>📝 Pasos para obtener las credenciales:</h3>";
    echo "<ol>";
    echo "<li><strong>Accede a la interfaz web:</strong> <a href='http://$shellyIP' target='_blank'>http://$shellyIP</a></li>";
    echo "<li><strong>Busca las credenciales por defecto:</strong>";
    echo "<ul>";
    echo "<li>Usuario: <code>admin</code> | Contraseña: <code>admin</code></li>";
    echo "<li>Usuario: <code>admin</code> | Contraseña: (vacía)</li>";
    echo "<li>Usuario: <code>admin</code> | Contraseña: número de serie del dispositivo</li>";
    echo "</ul>";
    echo "</li>";
    echo "<li><strong>O crea nuevas credenciales</strong> si es la primera configuración</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<form method='post' style='background: #f8f9fa; padding: 20px; border-radius: 5px; border: 1px solid #dee2e6;'>";
    echo "<h3>🔐 Ingresar Credenciales</h3>";
    echo "<div style='margin: 15px 0;'>";
    echo "<label style='display: block; margin-bottom: 5px;'><strong>Usuario:</strong></label>";
    echo "<input type='text' name='username' value='admin' style='width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;' required>";
    echo "</div>";
    echo "<div style='margin: 15px 0;'>";
    echo "<label style='display: block; margin-bottom: 5px;'><strong>Contraseña:</strong></label>";
    echo "<input type='password' name='password' style='width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;' placeholder='Deja vacío si no tiene contraseña'>";
    echo "</div>";
    echo "<button type='submit' style='background: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>🧪 Probar Credenciales</button>";
    echo "</form>";
    
} else {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    echo "<h2>🧪 Probando credenciales...</h2>";
    echo "<p><strong>Usuario:</strong> $username</p>";
    echo "<p><strong>Contraseña:</strong> " . (empty($password) ? '(vacía)' : '***') . "</p>";
    
    // Función para probar con autenticación
    function testWithAuth($url, $username, $password) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // No seguir redirecciones
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'success' => !$error && $httpCode == 200,
            'http_code' => $httpCode,
            'error' => $error,
            'response' => $response
        ];
    }
    
    // Probar diferentes endpoints
    $tests = [
        'Página principal' => "http://$shellyIP/",
        'Info del dispositivo' => "http://$shellyIP/shelly",
        'Estado general' => "http://$shellyIP/status",
        'Switch 0 status (RPC)' => "http://$shellyIP/rpc/Switch.GetStatus?id=0",
        'Switch 0 status (clásico)' => "http://$shellyIP/relay/0"
    ];
    
    echo "<h3>📊 Resultados de las pruebas:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Prueba</th><th>HTTP</th><th>Estado</th><th>Detalles</th></tr>";
    
    $authWorks = false;
    
    foreach ($tests as $testName => $testUrl) {
        $result = testWithAuth($testUrl, $username, $password);
        
        echo "<tr>";
        echo "<td>$testName</td>";
        echo "<td>" . $result['http_code'] . "</td>";
        
        if ($result['success']) {
            echo "<td style='color: green; background: #d4edda;'>✅ OK</td>";
            echo "<td>Autenticación exitosa</td>";
            $authWorks = true;
        } elseif ($result['http_code'] == 401) {
            echo "<td style='color: red; background: #f8d7da;'>❌ Auth</td>";
            echo "<td>Credenciales incorrectas</td>";
        } elseif ($result['http_code'] == 302) {
            echo "<td style='color: orange; background: #fff3cd;'>🔄 Redirect</td>";
            echo "<td>Aún requiere autenticación</td>";
        } else {
            echo "<td style='color: red; background: #f8d7da;'>❌ Error</td>";
            echo "<td>" . ($result['error'] ?: 'HTTP ' . $result['http_code']) . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
    
    if ($authWorks) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>✅ ¡Credenciales correctas!</h3>";
        echo "<p>Las credenciales funcionan correctamente.</p>";
        
        // Ahora probar comandos de switch con autenticación
        echo "<h4>🧪 Probando comandos de control:</h4>";
        
        $switchTests = [];
        for ($i = 0; $i <= 3; $i++) {
            // Probar formato RPC
            $rpcUrl = "http://$shellyIP/rpc/Switch.Set?id=$i&on=true";
            $rpcResult = testWithAuth($rpcUrl, $username, $password);
            
            // Probar formato clásico
            $classicUrl = "http://$shellyIP/relay/$i?turn=on";
            $classicResult = testWithAuth($classicUrl, $username, $password);
            
            echo "<p><strong>Switch $i:</strong> ";
            if ($rpcResult['success']) {
                echo "RPC ✅ ";
                $switchTests[$i] = 'rpc';
            } else {
                echo "RPC ❌ ";
            }
            
            if ($classicResult['success']) {
                echo "Clásico ✅";
                if (!isset($switchTests[$i])) $switchTests[$i] = 'classic';
            } else {
                echo "Clásico ❌";
            }
            echo "</p>";
        }
        
        if (!empty($switchTests)) {
            echo "<h4>🔧 Generar configuración con autenticación:</h4>";
            echo "<form method='post'>";
            echo "<input type='hidden' name='username' value='$username'>";
            echo "<input type='hidden' name='password' value='$password'>";
            echo "<input type='hidden' name='generate_config' value='1'>";
            
            echo "<div style='margin: 15px 0;'>";
            echo "<label><strong>Switch que controla la barrera:</strong></label><br>";
            foreach ($switchTests as $switchId => $format) {
                echo "<input type='radio' name='barrier_switch' value='$switchId:$format' id='switch_$switchId'>";
                echo "<label for='switch_$switchId'>Switch $switchId ($format)</label><br>";
            }
            echo "</div>";
            
            echo "<button type='submit' style='background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>";
            echo "🔧 Generar configuración con autenticación";
            echo "</button>";
            echo "</form>";
        }
        
        echo "</div>";
        
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f1aeb5; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>❌ Credenciales incorrectas</h3>";
        echo "<p><strong>Intenta con:</strong></p>";
        echo "<ul>";
        echo "<li>Usuario: <code>admin</code> | Contraseña: (vacía)</li>";
        echo "<li>Usuario: <code>admin</code> | Contraseña: <code>admin</code></li>";
        echo "<li>Busca la etiqueta en el dispositivo físico para obtener credenciales por defecto</li>";
        echo "<li>Resetea el dispositivo a configuración de fábrica</li>";
        echo "</ul>";
        echo "<p><a href='?' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Intentar con otras credenciales</a></p>";
        echo "</div>";
    }
}

// Generar configuración con autenticación
if (isset($_POST['generate_config']) && isset($_POST['barrier_switch'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $switchData = explode(':', $_POST['barrier_switch']);
    $switchId = $switchData[0];
    $apiFormat = $switchData[1];
    
    echo "<hr>";
    echo "<h2>🔧 Generando configuración con autenticación...</h2>";
    
    // Crear URLs con autenticación
    $authString = "$username:" . urlencode($password) . "@";
    
    if ($apiFormat === 'rpc') {
        $newOpenUrl = "http://$authString$shellyIP/rpc/Switch.Set?id=$switchId&on=false";
        $newCloseUrl = "http://$authString$shellyIP/rpc/Switch.Set?id=$switchId&on=true";
    } else {
        $newOpenUrl = "http://$authString$shellyIP/relay/$switchId?turn=off";
        $newCloseUrl = "http://$authString$shellyIP/relay/$switchId?turn=on";
    }
    
    // Leer y actualizar configuración
    $configFile = '../config/config.php';
    $configContent = file_get_contents($configFile);
    
    if ($configContent) {
        // Reemplazar URLs
        $newContent = preg_replace(
            '/define\(\'SHELLY_OPEN_URL\',.*?\);/',
            "define('SHELLY_OPEN_URL', '$newOpenUrl');  // Switch $switchId con auth",
            $configContent
        );
        
        $newContent = preg_replace(
            '/define\(\'SHELLY_CLOSE_URL\',.*?\);/',
            "define('SHELLY_CLOSE_URL', '$newCloseUrl');  // Switch $switchId con auth",
            $newContent
        );
        
        // También actualizar la URL base
        $newApiUrl = "http://$authString$shellyIP/";
        $newContent = preg_replace(
            '/\$SHELLY_PUBLIC_IP = getShellyPublicIP\(\);.*?define\(\'SHELLY_API_URL\',.*?\);/s',
            "define('SHELLY_API_URL', '$newApiUrl'); // Con autenticación",
            $newContent
        );
        
        // Hacer backup
        $backupFile = '../config/config.php.backup-auth-' . date('Y-m-d-H-i-s');
        file_put_contents($backupFile, $configContent);
        
        // Escribir nueva configuración
        if (file_put_contents($configFile, $newContent)) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px;'>";
            echo "<h3>✅ ¡Configuración con autenticación aplicada!</h3>";
            echo "<p><strong>Switch configurado:</strong> $switchId</p>";
            echo "<p><strong>Formato API:</strong> $apiFormat</p>";
            echo "<p><strong>Usuario:</strong> $username</p>";
            echo "<p><strong>Contraseña:</strong> " . (empty($password) ? '(vacía)' : '***') . "</p>";
            echo "<p><strong>Backup:</strong> $backupFile</p>";
            echo "<h4>🧪 URLs configuradas:</h4>";
            echo "<ul>";
            echo "<li><strong>Abrir:</strong> <code>" . str_replace($authString, '[usuario:contraseña]@', $newOpenUrl) . "</code></li>";
            echo "<li><strong>Cerrar:</strong> <code>" . str_replace($authString, '[usuario:contraseña]@', $newCloseUrl) . "</code></li>";
            echo "</ul>";
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
echo "<h4>🔐 Sobre la autenticación HTTP 302:</h4>";
echo "<p>El código HTTP 302 significa que tu Shelly está redirigiendo a una página de login porque requiere autenticación.</p>";
echo "<p><strong>Credenciales comunes:</strong></p>";
echo "<ul>";
echo "<li><code>admin</code> / (sin contraseña)</li>";
echo "<li><code>admin</code> / <code>admin</code></li>";
echo "<li><code>admin</code> / [número de serie del dispositivo]</li>";
echo "</ul>";
echo "</div>";
?>