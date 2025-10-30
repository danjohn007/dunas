<?php
/**
 * Test de Configuración con IP Local del Shelly
 */
require_once '../config/config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Shelly IP Local - 192.168.1.95</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        .info { color: #3498db; font-weight: bold; }
        .section { margin: 20px 0; padding: 15px; border-left: 4px solid #3498db; background: #f8f9fa; }
        .test-result { padding: 15px; margin: 10px 0; border-radius: 4px; border: 1px solid #ddd; }
        .test-success { background: #d4edda; border-color: #c3e6cb; }
        .test-error { background: #f8d7da; border-color: #f5c6cb; }
        .code { background: #2c3e50; color: #ecf0f1; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background: #f2f2f2; }
        h1 { color: #2c3e50; }
        h2 { color: #34495e; }
        h3 { color: #7f8c8d; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏠 Test Shelly con IP Local</h1>
        <p><strong>IP del Shelly:</strong> 192.168.1.95</p>
        
        <div class="section">
            <h2>📋 Configuración Actualizada</h2>
            <table class="table">
                <tr><th>Parámetro</th><th>Valor Anterior</th><th>Valor Nuevo</th></tr>
                <tr>
                    <td><strong>SHELLY_API_URL</strong></td>
                    <td>http://162.215.121.70</td>
                    <td class="success">http://192.168.1.95</td>
                </tr>
                <tr>
                    <td><strong>SHELLY_OPEN_URL</strong></td>
                    <td>http://162.215.121.70/rpc/Switch.Set?id=0&on=false</td>
                    <td class="success">http://admin:67da6c@192.168.1.95/rpc/Switch.Set?id=0&on=false</td>
                </tr>
                <tr>
                    <td><strong>SHELLY_CLOSE_URL</strong></td>
                    <td>http://162.215.121.70/rpc/Switch.Set?id=0&on=true</td>
                    <td class="success">http://admin:67da6c@192.168.1.95/rpc/Switch.Set?id=0&on=true</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>🧪 Tests de Conectividad</h2>
            
            <?php
            function testShellyCommand($url, $description) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyLocalTest/1.0');
                
                $start_time = microtime(true);
                $response = curl_exec($ch);
                $end_time = microtime(true);
                
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                $response_time = round(($end_time - $start_time) * 1000, 2);
                
                curl_close($ch);
                
                return [
                    'success' => !$error && $http_code === 200,
                    'http_code' => $http_code,
                    'error' => $error,
                    'response_time' => $response_time,
                    'response' => $response
                ];
            }
            
            // Test 1: Conectividad básica
            echo "<h3>🔍 Test 1: Conectividad Básica</h3>";
            $basic_result = testShellyCommand('http://admin:67da6c@192.168.1.95/shelly', 'Conectividad básica');
            
            echo "<div class='test-result " . ($basic_result['success'] ? 'test-success' : 'test-error') . "'>";
            echo "<strong>URL:</strong> http://admin:67da6c@192.168.1.95/shelly<br>";
            echo "<strong>Código HTTP:</strong> " . $basic_result['http_code'] . "<br>";
            echo "<strong>Tiempo:</strong> " . $basic_result['response_time'] . " ms<br>";
            if ($basic_result['error']) {
                echo "<strong>Error:</strong> " . $basic_result['error'] . "<br>";
            }
            echo "<strong>Estado:</strong> " . ($basic_result['success'] ? "✅ Conectado" : "❌ Sin conexión") . "<br>";
            echo "</div>";
            
            // Test 2: Información del dispositivo
            echo "<h3>📱 Test 2: Información del Dispositivo</h3>";
            $info_result = testShellyCommand('http://admin:67da6c@192.168.1.95/rpc/Shelly.GetInfo', 'Información del dispositivo');
            
            echo "<div class='test-result " . ($info_result['success'] ? 'test-success' : 'test-error') . "'>";
            echo "<strong>URL:</strong> http://admin:67da6c@192.168.1.95/rpc/Shelly.GetInfo<br>";
            echo "<strong>Código HTTP:</strong> " . $info_result['http_code'] . "<br>";
            echo "<strong>Tiempo:</strong> " . $info_result['response_time'] . " ms<br>";
            if ($info_result['error']) {
                echo "<strong>Error:</strong> " . $info_result['error'] . "<br>";
            }
            echo "<strong>Estado:</strong> " . ($info_result['success'] ? "✅ Información obtenida" : "❌ Sin información") . "<br>";
            
            if ($info_result['success'] && $info_result['response']) {
                $device_info = json_decode($info_result['response'], true);
                if ($device_info) {
                    echo "<div class='code'>";
                    echo "<strong>Información del dispositivo:</strong><br>";
                    echo "ID: " . ($device_info['id'] ?? 'N/A') . "<br>";
                    echo "Modelo: " . ($device_info['model'] ?? 'N/A') . "<br>";
                    echo "Generación: " . ($device_info['gen'] ?? 'N/A') . "<br>";
                    echo "Firmware: " . ($device_info['fw_id'] ?? 'N/A') . "<br>";
                    echo "MAC: " . ($device_info['mac'] ?? 'N/A') . "<br>";
                    echo "</div>";
                }
            }
            echo "</div>";
            
            // Test 3: Estado del switch
            echo "<h3>🔧 Test 3: Estado del Switch</h3>";
            $switch_result = testShellyCommand('http://admin:67da6c@192.168.1.95/rpc/Switch.GetStatus?id=0', 'Estado del switch');
            
            echo "<div class='test-result " . ($switch_result['success'] ? 'test-success' : 'test-error') . "'>";
            echo "<strong>URL:</strong> http://admin:67da6c@192.168.1.95/rpc/Switch.GetStatus?id=0<br>";
            echo "<strong>Código HTTP:</strong> " . $switch_result['http_code'] . "<br>";
            echo "<strong>Tiempo:</strong> " . $switch_result['response_time'] . " ms<br>";
            if ($switch_result['error']) {
                echo "<strong>Error:</strong> " . $switch_result['error'] . "<br>";
            }
            echo "<strong>Estado:</strong> " . ($switch_result['success'] ? "✅ Estado obtenido" : "❌ Sin estado") . "<br>";
            
            if ($switch_result['success'] && $switch_result['response']) {
                $switch_info = json_decode($switch_result['response'], true);
                if ($switch_info) {
                    echo "<div class='code'>";
                    echo "<strong>Estado del switch:</strong><br>";
                    echo "Encendido: " . ($switch_info['output'] ? 'SÍ' : 'NO') . "<br>";
                    echo "Voltaje: " . ($switch_info['voltage'] ?? 'N/A') . " V<br>";
                    echo "Corriente: " . ($switch_info['current'] ?? 'N/A') . " A<br>";
                    echo "Potencia: " . ($switch_info['apower'] ?? 'N/A') . " W<br>";
                    echo "</div>";
                }
            }
            echo "</div>";
            ?>
        </div>

        <div class="section">
            <h2>🎮 Tests de Control</h2>
            
            <?php
            // Test 4: Comando ABRIR
            echo "<h3>🔓 Test 4: Comando ABRIR (Switch OFF)</h3>";
            $open_result = testShellyCommand(SHELLY_OPEN_URL, 'Comando abrir barrera');
            
            echo "<div class='test-result " . ($open_result['success'] ? 'test-success' : 'test-error') . "'>";
            echo "<strong>URL:</strong> " . SHELLY_OPEN_URL . "<br>";
            echo "<strong>Código HTTP:</strong> " . $open_result['http_code'] . "<br>";
            echo "<strong>Tiempo:</strong> " . $open_result['response_time'] . " ms<br>";
            if ($open_result['error']) {
                echo "<strong>Error:</strong> " . $open_result['error'] . "<br>";
            }
            echo "<strong>Estado:</strong> " . ($open_result['success'] ? "✅ Comando exitoso" : "❌ Comando falló") . "<br>";
            
            if ($open_result['success'] && $open_result['response']) {
                $cmd_response = json_decode($open_result['response'], true);
                if ($cmd_response) {
                    echo "<div class='code'>";
                    echo "<strong>Respuesta del comando:</strong><br>";
                    echo "Switch activado: " . ($cmd_response['was_on'] ? 'SÍ' : 'NO') . "<br>";
                    echo "</div>";
                }
            }
            echo "</div>";
            
            // Esperar un poco antes del siguiente comando
            sleep(2);
            
            // Test 5: Comando CERRAR
            echo "<h3>🔒 Test 5: Comando CERRAR (Switch ON)</h3>";
            $close_result = testShellyCommand(SHELLY_CLOSE_URL, 'Comando cerrar barrera');
            
            echo "<div class='test-result " . ($close_result['success'] ? 'test-success' : 'test-error') . "'>";
            echo "<strong>URL:</strong> " . SHELLY_CLOSE_URL . "<br>";
            echo "<strong>Código HTTP:</strong> " . $close_result['http_code'] . "<br>";
            echo "<strong>Tiempo:</strong> " . $close_result['response_time'] . " ms<br>";
            if ($close_result['error']) {
                echo "<strong>Error:</strong> " . $close_result['error'] . "<br>";
            }
            echo "<strong>Estado:</strong> " . ($close_result['success'] ? "✅ Comando exitoso" : "❌ Comando falló") . "<br>";
            
            if ($close_result['success'] && $close_result['response']) {
                $cmd_response = json_decode($close_result['response'], true);
                if ($cmd_response) {
                    echo "<div class='code'>";
                    echo "<strong>Respuesta del comando:</strong><br>";
                    echo "Switch activado: " . ($cmd_response['was_on'] ? 'SÍ' : 'NO') . "<br>";
                    echo "</div>";
                }
            }
            echo "</div>";
            ?>
        </div>

        <div class="section">
            <h2>📊 Resumen de Resultados</h2>
            
            <?php
            $tests = [
                'Conectividad básica' => $basic_result['success'],
                'Información del dispositivo' => $info_result['success'],
                'Estado del switch' => $switch_result['success'],
                'Comando ABRIR' => $open_result['success'],
                'Comando CERRAR' => $close_result['success']
            ];
            
            $total_tests = count($tests);
            $successful_tests = count(array_filter($tests));
            $success_rate = round(($successful_tests / $total_tests) * 100, 1);
            
            echo "<table class='table'>";
            echo "<tr><th>Test</th><th>Resultado</th></tr>";
            foreach ($tests as $test_name => $result) {
                echo "<tr>";
                echo "<td>$test_name</td>";
                echo "<td>" . ($result ? "✅ Exitoso" : "❌ Falló") . "</td>";
                echo "</tr>";
            }
            echo "<tr style='font-weight: bold; background: #f8f9fa;'>";
            echo "<td>Tasa de éxito</td>";
            echo "<td>$success_rate% ($successful_tests/$total_tests)</td>";
            echo "</tr>";
            echo "</table>";
            
            if ($success_rate >= 80) {
                echo "<div class='test-success'>";
                echo "<h3>🎉 ¡Configuración Exitosa!</h3>";
                echo "<p>La mayoría de los tests fueron exitosos. El sistema debería funcionar correctamente con la IP local del Shelly.</p>";
                echo "</div>";
            } elseif ($success_rate >= 50) {
                echo "<div class='test-result' style='background: #fff3cd; border-color: #ffeaa7;'>";
                echo "<h3>⚠️ Configuración Parcial</h3>";
                echo "<p>Algunos tests funcionan, pero hay problemas que necesitan atención.</p>";
                echo "</div>";
            } else {
                echo "<div class='test-error'>";
                echo "<h3>❌ Problemas de Configuración</h3>";
                echo "<p>La mayoría de los tests fallaron. Revisar conectividad de red y configuración.</p>";
                echo "</div>";
            }
            ?>
        </div>

        <div class="section">
            <h2>📝 Próximos Pasos</h2>
            
            <?php if ($success_rate >= 80): ?>
            <div class="test-success">
                <h3>✅ Sistema Listo</h3>
                <ul>
                    <li>Probar el sistema completo de control de acceso</li>
                    <li>Verificar la funcionalidad desde la interfaz web</li>
                    <li>Monitorear logs para detectar problemas</li>
                    <li>Configurar alertas de fallos</li>
                </ul>
            </div>
            <?php else: ?>
            <div class="test-error">
                <h3>🔧 Acciones Requeridas</h3>
                <ul>
                    <li>Verificar que el dispositivo Shelly esté encendido</li>
                    <li>Comprobar conectividad de red local</li>
                    <li>Revisar configuración de firewall</li>
                    <li>Validar credenciales del dispositivo</li>
                </ul>
            </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>🔗 Enlaces Útiles</h2>
            <ul>
                <li><a href="/dunas/dunasshelly/public/test-web-integration.php">Test Web Integration Original</a></li>
                <li><a href="/dunas/dunasshelly/public/test-config-simple.php">Test Config Simple</a></li>
                <li><a href="/dunas/dunasshelly/public/access/create">Registro de Entrada</a></li>
            </ul>
        </div>
    </div>
</body>
</html>