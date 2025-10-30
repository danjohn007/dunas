<?php
/**
 * Test Rápido - Verificación de Solución HTTP 302
 */

echo "🔧 Test Rápido Shelly - IP Local vs IP Pública\n";
echo "===============================================\n\n";

// URLs con IP pública (problemáticas)
$public_open = "http://admin:67da6c@162.215.121.70/rpc/Switch.Set?id=0&on=false";
$public_close = "http://admin:67da6c@162.215.121.70/rpc/Switch.Set?id=0&on=true";

// URLs con IP local (solución)
$local_open = "http://admin:67da6c@192.168.1.95/rpc/Switch.Set?id=0&on=false";
$local_close = "http://admin:67da6c@192.168.1.95/rpc/Switch.Set?id=0&on=true";

function testUrl($url, $description) {
    echo "🧪 $description\n";
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyQuickTest/1.0');
    
    $start = microtime(true);
    $response = curl_exec($ch);
    $end = microtime(true);
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $time = round(($end - $start) * 1000, 2);
    
    curl_close($ch);
    
    echo "Código HTTP: $http_code\n";
    echo "Tiempo: {$time} ms\n";
    
    if ($error) {
        echo "❌ Error: $error\n";
    } elseif ($http_code === 200) {
        echo "✅ ÉXITO: Comando ejecutado correctamente\n";
        if ($response) {
            $json = json_decode($response, true);
            if ($json) {
                echo "Respuesta: " . json_encode($json, JSON_PRETTY_PRINT) . "\n";
            }
        }
    } elseif ($http_code === 302) {
        echo "❌ ERROR HTTP 302: Problema de autenticación\n";
    } else {
        echo "⚠️  Código HTTP: $http_code\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    return $http_code === 200;
}

echo "📋 FASE 1: Probando URLs con IP Pública (162.215.121.70)\n";
echo str_repeat("=", 60) . "\n\n";

$public_open_works = testUrl($public_open, "IP Pública ABRIR");
$public_close_works = testUrl($public_close, "IP Pública CERRAR");

echo "📋 FASE 2: Probando URLs con IP Local (192.168.1.95)\n";
echo str_repeat("=", 60) . "\n\n";

$local_open_works = testUrl($local_open, "IP Local ABRIR");
$local_close_works = testUrl($local_close, "IP Local CERRAR");

echo "📊 RESUMEN DE RESULTADOS\n";
echo str_repeat("=", 30) . "\n\n";

echo "URLs con IP Pública:\n";
echo "  ABRIR: " . ($public_open_works ? "✅ Funciona" : "❌ Falla") . "\n";
echo "  CERRAR: " . ($public_close_works ? "✅ Funciona" : "❌ Falla") . "\n\n";

echo "URLs con IP Local:\n";
echo "  ABRIR: " . ($local_open_works ? "✅ Funciona" : "❌ Falla") . "\n";
echo "  CERRAR: " . ($local_close_works ? "✅ Funciona" : "❌ Falla") . "\n\n";

if ($local_open_works && $local_close_works) {
    echo "🎉 ¡SOLUCIÓN CONFIRMADA!\n";
    echo "Las URLs con IP local funcionan correctamente.\n";
    echo "La configuración ha sido actualizada para usar 192.168.1.95\n\n";
} elseif ($public_open_works && $public_close_works) {
    echo "⚠️  IP PÚBLICA FUNCIONA\n";
    echo "Sorprendentemente, la IP pública también funciona.\n";
    echo "Revisar configuración de port forwarding.\n\n";
} else {
    echo "⚠️  PROBLEMA DE CONECTIVIDAD\n";
    echo "Ninguna URL funciona. Verificar:\n";
    echo "- Dispositivo Shelly encendido y conectado a la red\n";
    echo "- Servidor en la misma red local que el Shelly\n";
    echo "- Credenciales correctas (admin:67da6c)\n\n";
}

echo "🔗 Para más diagnósticos, usar:\n";
echo "https://fix360.app/dunas/dunasshelly/public/test-shelly-local-ip.php\n";
?>