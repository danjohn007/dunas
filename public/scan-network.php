<?php
/**
 * Scanner de red para encontrar dispositivos Shelly
 */

echo "<h1>Scanner de dispositivos Shelly en red local</h1>";
echo "<p>Escaneando IPs de 192.168.1.1 a 192.168.1.254...</p>";

// Función para probar una IP
function testIP($ip) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://$ip/shelly");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'response' => $response];
}

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>IP</th><th>Estado</th><th>Información</th></tr>";

// Escanear rango común
$base_ip = "192.168.1.";
$found_devices = [];

for ($i = 1; $i <= 254; $i++) {
    $ip = $base_ip . $i;
    $result = testIP($ip);
    
    if ($result['code'] == 200) {
        $found_devices[] = $ip;
        echo "<tr style='background-color: #90EE90;'>";
        echo "<td>$ip</td>";
        echo "<td>✅ Respondiendo</td>";
        
        $decoded = json_decode($result['response'], true);
        if ($decoded && isset($decoded['type'])) {
            echo "<td>Tipo: " . $decoded['type'] . "</td>";
        } else {
            echo "<td>" . substr($result['response'], 0, 100) . "</td>";
        }
        echo "</tr>";
    } elseif ($result['code'] > 0) {
        echo "<tr style='background-color: #FFE4B5;'>";
        echo "<td>$ip</td>";
        echo "<td>HTTP " . $result['code'] . "</td>";
        echo "<td>Dispositivo detectado pero no es Shelly</td>";
        echo "</tr>";
    }
    
    // Mostrar progreso cada 50 IPs
    if ($i % 50 == 0) {
        echo "<tr><td colspan='3'><em>Escaneado hasta $ip...</em></td></tr>";
        flush();
    }
}

echo "</table>";

echo "<h2>Resumen:</h2>";
if (count($found_devices) > 0) {
    echo "<strong style='color: green;'>Dispositivos Shelly encontrados:</strong><br>";
    foreach ($found_devices as $device_ip) {
        echo "• <a href='http://$device_ip/' target='_blank'>$device_ip</a><br>";
    }
} else {
    echo "<strong style='color: red;'>No se encontraron dispositivos Shelly en el rango 192.168.1.x</strong><br>";
    echo "Posibles causas:<br>";
    echo "• El dispositivo está en una subred diferente (ej: 192.168.0.x, 10.0.0.x)<br>";
    echo "• El dispositivo está apagado o desconectado<br>";
    echo "• Hay un firewall bloqueando las conexiones<br>";
}

echo "<p><strong>Nota:</strong> Este escaneo puede tomar varios minutos.</p>";
?>