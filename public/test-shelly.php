<?php
/**
 * Script de prueba para verificar conectividad con Shelly
 */

// URLs de prueba - usando diferentes IPs para diagnosticar
$ips_to_test = ['192.168.1.95', '192.168.1.159']; // Agregar más IPs si es necesario

echo "<h1>Prueba de conectividad con Shelly</h1>";
echo "<p>Fecha y hora: " . date('Y-m-d H:i:s') . "</p>";

foreach ($ips_to_test as $ip) {
    echo "<hr><h2>Probando IP: $ip</h2>";
    
    $urls = [
        'ping_basic' => "http://$ip/",
        'status' => "http://$ip/status",
        'shelly_info' => "http://$ip/shelly",
        'rpc_info' => "http://$ip/rpc/Sys.GetStatus", 
        'switch_info' => "http://$ip/rpc/Switch.GetStatus?id=0",
        'open_test' => "http://$ip/rpc/Switch.Set?id=0&on=false",
        'close_test' => "http://$ip/rpc/Switch.Set?id=0&on=true"
    ];

    foreach ($urls as $test => $url) {
        echo "<h3>Prueba: $test</h3>";
        echo "<strong>URL:</strong> $url<br>";
        
        $start_time = microtime(true);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Timeout más corto para diagnóstico rápido
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        curl_close($ch);
        
        $end_time = microtime(true);
        $execution_time = round(($end_time - $start_time) * 1000, 2);
        
        echo "<strong>Tiempo de respuesta:</strong> {$execution_time}ms<br>";
        echo "<strong>Código HTTP:</strong> $httpCode<br>";
        
        if ($error) {
            echo "<strong style='color: red;'>Error:</strong> $error<br>";
        } elseif ($httpCode == 200) {
            echo "<strong style='color: green;'>Estado:</strong> Conectado correctamente<br>";
            if ($response) {
                $decoded = json_decode($response, true);
                if ($decoded) {
                    echo "<strong>Respuesta JSON:</strong><br>";
                    echo "<pre>" . json_encode($decoded, JSON_PRETTY_PRINT) . "</pre>";
                } else {
                    echo "<strong>Respuesta (primeros 200 caracteres):</strong><br>";
                    echo "<pre>" . htmlspecialchars(substr($response, 0, 200)) . "</pre>";
                }
            }
        } else {
            echo "<strong style='color: orange;'>Código HTTP inesperado:</strong> $httpCode<br>";
        }
        
        echo "<br>";
        
        // Si encontramos conexión exitosa, no necesitamos probar más URLs para esta IP
        if ($httpCode == 200) {
            echo "<strong style='color: green;'>✅ IP $ip está accesible!</strong><br>";
            break;
        }
    }
}

// Información adicional de diagnóstico
echo "<hr>";
echo "<h2>Información adicional de diagnóstico:</h2>";
echo "<strong>Servidor:</strong> " . $_SERVER['SERVER_NAME'] . "<br>";
echo "<strong>IP del servidor:</strong> " . $_SERVER['SERVER_ADDR'] ?? 'No disponible' . "<br>";
echo "<strong>User Agent:</strong> " . $_SERVER['HTTP_USER_AGENT'] ?? 'No disponible' . "<br>";

// Verificar si cURL está habilitado
if (function_exists('curl_init')) {
    echo "<strong style='color: green;'>cURL:</strong> Habilitado<br>";
    $curl_version = curl_version();
    echo "<strong>Versión cURL:</strong> " . $curl_version['version'] . "<br>";
} else {
    echo "<strong style='color: red;'>cURL:</strong> NO habilitado<br>";
}

// Verificar si allow_url_fopen está habilitado  
echo "<strong>allow_url_fopen:</strong> " . (ini_get('allow_url_fopen') ? 'Habilitado' : 'Deshabilitado') . "<br>";
?>
