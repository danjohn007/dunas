<?php
/**
 * Test directo del relay del Shelly Pro 4PM V2
 */

require_once __DIR__ . '/../config/config.php';

// Obtener parámetros
$action = $_GET['action'] ?? '';
$username = $_GET['username'] ?? 'admin';
$password = $_GET['password'] ?? '';

if (!in_array($action, ['on', 'off'])) {
    echo "❌ Acción inválida. Use 'on' o 'off'.";
    exit;
}

// Obtener IP del Shelly
$shellyIP = str_replace(['http://', 'https://', '/'], '', SHELLY_API_URL);
$shellyIP = rtrim($shellyIP, '/');

// URL del comando
$relayState = ($action === 'on') ? 'true' : 'false';
$url = "http://$shellyIP/rpc/Switch.Set?id=0&on=$relayState";

echo "🧪 Probando: " . ($action === 'on' ? 'CERRAR barrera (relay ON)' : 'ABRIR barrera (relay OFF)') . "<br>";
echo "📡 URL: $url<br>";
echo "🔑 Credenciales: $username / " . ($password ? $password : '(vacía)') . "<br><br>";

// Hacer la petición
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
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
    echo "❌ Error en la comunicación:<br>";
    echo "🔢 Código HTTP: $httpCode<br>";
    if ($error) {
        echo "⚠️ Error cURL: $error<br>";
    }
    echo "📄 Respuesta: " . htmlspecialchars($response);
}
?>