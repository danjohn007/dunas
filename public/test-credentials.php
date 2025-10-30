<?php
/**
 * Test automático para encontrar credenciales del Shelly Pro 4PM
 */

require_once __DIR__ . '/../config/config.php';

echo "<h1>🔍 Test Automático de Credenciales Shelly Pro 4PM</h1>";
echo "<p><strong>Probando automáticamente las combinaciones más comunes...</strong></p>";

// Obtener IP del Shelly
$shellyIP = str_replace(['http://', 'https://', '/'], '', SHELLY_API_URL);
$shellyIP = rtrim($shellyIP, '/');

echo "<h2>📋 Información del dispositivo:</h2>";
echo "<p><strong>IP:</strong> $shellyIP</p>";
echo "<p><strong>Device ID detectado:</strong> shellypro4pm-34987a67da6c</p>";

// CSS para la tabla
echo "<style>
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
th { background-color: #f2f2f2; font-weight: bold; }
.success { background-color: #d4edda; color: #155724; }
.error { background-color: #f8d7da; color: #721c24; }
.testing { background-color: #fff3cd; color: #856404; }
.attempt { margin: 5px 0; padding: 5px; border-radius: 3px; font-size: 12px; }
</style>";

// Función para probar credenciales
function testCredentials($ip, $username, $password, $description) {
    echo "<tr class='testing'>";
    echo "<td>$username</td>";
    echo "<td>" . ($password ? $password : '(vacía)') . "</td>";
    echo "<td>$description</td>";
    echo "<td>🧪 Probando...</td>";
    echo "</tr>";
    echo "<script>document.body.scrollTop = document.documentElement.scrollTop = document.body.scrollHeight;</script>";
    flush();
    
    // URLs de prueba
    $testUrls = [
        'basic' => "http://$ip/",
        'shelly' => "http://$ip/shelly",
        'status' => "http://$ip/status",
        'switch' => "http://$ip/rpc/Switch.GetStatus?id=0"
    ];
    
    $results = [];
    $anySuccess = false;
    
    foreach ($testUrls as $type => $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyCredentialTest/1.0');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        $results[$type] = [
            'success' => !$error && $httpCode == 200,
            'http_code' => $httpCode,
            'error' => $error,
            'response_size' => strlen($response)
        ];
        
        if ($results[$type]['success']) {
            $anySuccess = true;
        }
    }
    
    // Actualizar la fila con el resultado
    echo "<script>";
    echo "var rows = document.querySelectorAll('tr.testing');";
    echo "var lastRow = rows[rows.length - 1];";
    
    if ($anySuccess) {
        echo "lastRow.className = 'success';";
        echo "lastRow.cells[3].innerHTML = '✅ ¡CREDENCIALES CORRECTAS!';";
        
        // Mostrar detalles de qué funciona
        $workingEndpoints = [];
        foreach ($results as $type => $result) {
            if ($result['success']) {
                $workingEndpoints[] = "$type (HTTP 200)";
            }
        }
        echo "lastRow.cells[3].innerHTML += '<br><small>Funciona: " . implode(', ', $workingEndpoints) . "</small>';";
    } else {
        echo "lastRow.className = 'error';";
        
        // Mostrar códigos HTTP para diagnóstico
        $httpCodes = [];
        foreach ($results as $type => $result) {
            $httpCodes[] = "$type: HTTP " . $result['http_code'];
        }
        echo "lastRow.cells[3].innerHTML = '❌ Falló (" . implode(', ', $httpCodes) . ")';";
    }
    
    echo "</script>";
    flush();
    
    return [
        'success' => $anySuccess,
        'results' => $results,
        'username' => $username,
        'password' => $password,
        'description' => $description
    ];
}

// Lista de credenciales comunes para probar
$credentialsList = [
    // Más comunes
    ['admin', '', 'Sin contraseña (muy común)'],
    ['admin', 'admin', 'Credenciales por defecto'],
    
    // Basado en Device ID: shellypro4pm-34987a67da6c
    ['admin', '67da6c', 'Últimos 6 dígitos del Device ID'],
    ['admin', 'a67da6c', 'Últimos 7 dígitos del Device ID'],
    ['admin', '7a67da6c', 'Últimos 8 dígitos del Device ID'],
    ['admin', '34987a67da6c', 'Device ID sin prefijo'],
    ['admin', '34987a67', 'Primeros 8 dígitos del Device ID'],
    ['admin', '349876', 'Primeros 6 dígitos del Device ID'],
    
    // Combinaciones adicionales
    ['admin', '123456', 'PIN común 1'],
    ['admin', '000000', 'PIN común 2'],
    ['admin', '1234', 'PIN corto común'],
    ['root', '', 'Usuario root sin contraseña'],
    ['root', 'admin', 'Usuario root con admin'],
    ['user', '', 'Usuario user sin contraseña'],
    ['shelly', '', 'Usuario shelly sin contraseña'],
    ['shelly', 'shelly', 'Usuario y contraseña shelly'],
    
    // Variaciones del Device ID
    ['admin', 'SHELLYPRO4PM', 'Nombre del modelo'],
    ['admin', 'shellypro4pm', 'Nombre del modelo minúsculas'],
    ['admin', 'Pro4PM', 'Modelo corto'],
    ['admin', '4PM', 'Solo modelo'],
    
    // Números que podrían estar en la etiqueta
    ['admin', '2024', 'Año'],
    ['admin', '2023', 'Año anterior'],
    ['admin', '0000', 'Ceros'],
    ['admin', '1111', 'Unos'],
    ['admin', '9999', 'Nueves'],
];

echo "<h2>🧪 Iniciando pruebas automáticas...</h2>";
echo "<p><strong>Total de combinaciones a probar:</strong> " . count($credentialsList) . "</p>";
echo "<p><em>Esta prueba puede tomar 2-3 minutos...</em></p>";

echo "<table>";
echo "<tr><th>Usuario</th><th>Contraseña</th><th>Descripción</th><th>Resultado</th></tr>";

$successfulCredentials = [];
$attemptCount = 0;

foreach ($credentialsList as $credentials) {
    $attemptCount++;
    $username = $credentials[0];
    $password = $credentials[1];
    $description = $credentials[2];
    
    echo "<!-- Intento $attemptCount de " . count($credentialsList) . " -->";
    
    $result = testCredentials($shellyIP, $username, $password, $description);
    
    if ($result['success']) {
        $successfulCredentials[] = $result;
        
        // Si encontramos credenciales que funcionan, podemos parar o continuar
        // Por ahora continuamos para encontrar todas las que funcionan
    }
    
    // Pequeña pausa entre intentos para no sobrecargar el dispositivo
    usleep(500000); // 0.5 segundos
}

echo "</table>";

echo "<h2>📊 Resultados de la prueba</h2>";

if (!empty($successfulCredentials)) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 ¡Credenciales encontradas!</h3>";
    echo "<p><strong>Combinaciones que funcionan:</strong></p>";
    
    echo "<table>";
    echo "<tr><th>Usuario</th><th>Contraseña</th><th>Descripción</th><th>Acción</th></tr>";
    
    foreach ($successfulCredentials as $cred) {
        echo "<tr class='success'>";
        echo "<td><strong>{$cred['username']}</strong></td>";
        echo "<td><strong>" . ($cred['password'] ? $cred['password'] : '(vacía)') . "</strong></td>";
        echo "<td>{$cred['description']}</td>";
        echo "<td>";
        echo "<form method='post' action='setup-auth.php' style='margin: 0;'>";
        echo "<input type='hidden' name='username' value='{$cred['username']}'>";
        echo "<input type='hidden' name='password' value='{$cred['password']}'>";
        echo "<button type='submit' style='background: #007bff; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;'>🔧 Configurar sistema</button>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h4>🚀 Siguiente paso:</h4>";
    echo "<p>Haz clic en <strong>'Configurar sistema'</strong> con las credenciales que prefieras para continuar con la configuración completa del Shelly.</p>";
    
    echo "</div>";
    
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f1aeb5; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ No se encontraron credenciales válidas</h3>";
    echo "<p><strong>Todas las combinaciones comunes fallaron.</strong></p>";
    
    echo "<h4>📋 Próximos pasos:</h4>";
    echo "<ol>";
    echo "<li><strong>Verificar físicamente el dispositivo:</strong>";
    echo "<ul>";
    echo "<li>Busca una etiqueta con código QR en el Shelly</li>";
    echo "<li>Anota cualquier número o código que veas</li>";
    echo "<li>Busca el número de serie o MAC address</li>";
    echo "</ul>";
    echo "</li>";
    
    echo "<li><strong>Acceso desde red local:</strong>";
    echo "<ul>";
    echo "<li>Conecta una computadora a la misma red WiFi</li>";
    echo "<li>Ve a <a href='http://$shellyIP' target='_blank'>http://$shellyIP</a></li>";
    echo "<li>Podría no pedir credenciales desde la red local</li>";
    echo "</ul>";
    echo "</li>";
    
    echo "<li><strong>Reset del dispositivo:</strong>";
    echo "<ul>";
    echo "<li>Busca el botón RESET en el Shelly Pro 4PM</li>";
    echo "<li>Mantén presionado 10 segundos</li>";
    echo "<li>Credenciales serán: admin/(vacía)</li>";
    echo "</ul>";
    echo "</li>";
    echo "</ol>";
    
    echo "<p><a href='?' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Ejecutar prueba nuevamente</a></p>";
    echo "</div>";
}

echo "<h2>📝 Información adicional</h2>";
echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px;'>";
echo "<h4>🔍 Análisis de códigos HTTP encontrados:</h4>";
echo "<ul>";
echo "<li><strong>HTTP 200:</strong> ✅ Credenciales correctas</li>";
echo "<li><strong>HTTP 401:</strong> ❌ Credenciales incorrectas</li>";
echo "<li><strong>HTTP 302:</strong> 🔄 Redirección (requiere autenticación)</li>";
echo "<li><strong>HTTP 404:</strong> ⚠️ Endpoint no encontrado</li>";
echo "<li><strong>HTTP 0:</strong> 🔌 Error de conexión</li>";
echo "</ul>";

echo "<h4>⏱️ Estadísticas de la prueba:</h4>";
echo "<ul>";
echo "<li><strong>Total de intentos:</strong> " . count($credentialsList) . "</li>";
echo "<li><strong>Credenciales exitosas:</strong> " . count($successfulCredentials) . "</li>";
echo "<li><strong>Tiempo estimado:</strong> " . round(count($credentialsList) * 0.5) . " segundos</li>";
echo "</ul>";
echo "</div>";

echo "<script>";
echo "console.log('Test de credenciales completado');";
echo "console.log('Credenciales encontradas: " . count($successfulCredentials) . "');";
echo "</script>";
?>