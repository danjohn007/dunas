<?php
/**
 * Test directo del relay contra IP local del Shelly
 * IP Local: 192.168.1.95
 */

// Obtener parámetros
$action = $_GET['action'] ?? '';
$username = $_GET['username'] ?? 'admin';
$password = $_GET['password'] ?? '';

if (!in_array($action, ['on', 'off'])) {
    echo "❌ Acción inválida. Use 'on' o 'off'.";
    exit;
}

// IP local del Shelly
$shellyLocalIP = "192.168.1.95";

// URL del comando
$relayState = ($action === 'on') ? 'true' : 'false';
$url = "http://$shellyLocalIP/rpc/Switch.Set?id=0&on=$relayState";

echo "🧪 Probando: " . ($action === 'on' ? 'CERRAR barrera (relay ON)' : 'ABRIR barrera (relay OFF)') . "<br>";
echo "📡 URL: $url<br>";
echo "🔑 Credenciales: $username / " . ($password ? $password : '(vacía)') . "<br><br>";

// Hacer la petición
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
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

if (!$error && $httpCode == 200) {
    $responseData = json_decode($response, true);
    if ($responseData && isset($responseData['was_on'])) {
        echo "✅ ¡Comando ejecutado exitosamente!<br>";
        echo "📊 Respuesta del Shelly: " . json_encode($responseData) . "<br>";
        echo "🔄 Estado anterior: " . ($responseData['was_on'] ? 'ON' : 'OFF') . "<br>";
        echo "🎯 Estado actual: " . ($action === 'on' ? 'ON (CERRADO)' : 'OFF (ABIERTO)');
    } else {
        echo "⚠️ Comando enviado pero respuesta inesperada:<br>";
        echo "📄 Respuesta: " . htmlspecialchars($response);
    }
} else {
    if ($error) {
        echo "❌ Error de conectividad:<br>";
        echo "⚠️ Error cURL: $error<br>";
        echo "<br><strong>Esto es normal:</strong> El servidor web no puede acceder a tu red local (192.168.1.95)<br>";
        echo "<strong>Solución:</strong> Probar desde una computadora en tu misma red WiFi.";
    } else {
        echo "❌ Error HTTP:<br>";
        echo "🔢 Código HTTP: $httpCode<br>";
        echo "📄 Respuesta: " . htmlspecialchars($response);
    }
}
?>