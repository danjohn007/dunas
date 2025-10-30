<?php
/**
 * Corrector de Configuración Shelly - Solución para errores HTTP 302
 */
require_once '../config/config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corrector Shelly - Solución HTTP 302</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        .info { color: #3498db; font-weight: bold; }
        .section { margin: 20px 0; padding: 15px; border-left: 4px solid #3498db; background: #f8f9fa; }
        .fix-button { background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .fix-button:hover { background: #229954; }
        .code { background: #2c3e50; color: #ecf0f1; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
        .problem { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .solution { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Corrector de Configuración Shelly</h1>
        
        <div class="section">
            <h2>📊 Problema Identificado</h2>
            <div class="problem">
                <h3>❌ Problema de Configuración de Red</h3>
                <p><strong>Causa principal:</strong> El sistema estaba configurado para usar la IP pública del servidor (162.215.121.70) en lugar de la IP local del dispositivo Shelly (192.168.1.95).</p>
                
                <p><strong>URLs problemáticas anteriores:</strong></p>
                <div class="code">
                    SHELLY_OPEN_URL: http://162.215.121.70/rpc/Switch.Set?id=0&on=false<br>
                    SHELLY_CLOSE_URL: http://162.215.121.70/rpc/Switch.Set?id=0&on=true
                </div>
                
                <p><strong>Problema:</strong> El servidor web debe conectarse directamente a la IP local del Shelly (192.168.1.95) ya que están en la misma red local.</p>
            </div>
        </div>

        <div class="section">
            <h2>✅ Solución Recomendada</h2>
            <div class="solution">
                <h3>🔑 URLs Corregidas con IP Local</h3>
                <p>Para resolver el problema de conectividad, usamos la IP local del Shelly (192.168.1.95) con credenciales incluidas:</p>
                
                <div class="code">
                    <strong>URL ABRIR corregida:</strong><br>
                    http://admin:67da6c@192.168.1.95/rpc/Switch.Set?id=0&on=false<br><br>
                    
                    <strong>URL CERRAR corregida:</strong><br>
                    http://admin:67da6c@192.168.1.95/rpc/Switch.Set?id=0&on=true
                </div>
                
                <p><strong>Formato:</strong> <code>http://usuario:contraseña@ip/ruta</code></p>
            </div>
        </div>

        <div class="section">
            <h2>🧪 Test de Solución</h2>
            
            <?php
            // Probar las URLs corregidas
            echo "<h3>🔓 Test: URL ABRIR corregida (IP Local)</h3>";
            
            $corrected_open_url = "http://admin:67da6c@192.168.1.95/rpc/Switch.Set?id=0&on=false";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $corrected_open_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyFix/1.0');
            
            $start_time = microtime(true);
            $response = curl_exec($ch);
            $end_time = microtime(true);
            $response_time = round(($end_time - $start_time) * 1000, 2);
            
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            echo "<div class='code'>";
            echo "<strong>URL:</strong> $corrected_open_url<br>";
            echo "<strong>Código HTTP:</strong> $http_code<br>";
            echo "<strong>Tiempo:</strong> {$response_time} ms<br>";
            if ($error) {
                echo "<strong>Error:</strong> $error<br>";
            }
            if ($response) {
                echo "<strong>Respuesta:</strong> " . htmlspecialchars(substr($response, 0, 200)) . "<br>";
            }
            echo "</div>";
            
            if ($http_code === 200) {
                echo "<div class='solution'>✅ <strong>¡ÉXITO!</strong> La URL corregida funciona correctamente.</div>";
                $fix_works = true;
            } else {
                echo "<div class='problem'>❌ <strong>Aún hay problemas.</strong> Código HTTP: $http_code</div>";
                $fix_works = false;
            }
            
            echo "<h3>🔒 Test: URL CERRAR corregida (IP Local)</h3>";
            
            $corrected_close_url = "http://admin:67da6c@192.168.1.95/rpc/Switch.Set?id=0&on=true";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $corrected_close_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyFix/1.0');
            
            $start_time = microtime(true);
            $response = curl_exec($ch);
            $end_time = microtime(true);
            $response_time = round(($end_time - $start_time) * 1000, 2);
            
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            echo "<div class='code'>";
            echo "<strong>URL:</strong> $corrected_close_url<br>";
            echo "<strong>Código HTTP:</strong> $http_code<br>";
            echo "<strong>Tiempo:</strong> {$response_time} ms<br>";
            if ($error) {
                echo "<strong>Error:</strong> $error<br>";
            }
            if ($response) {
                echo "<strong>Respuesta:</strong> " . htmlspecialchars(substr($response, 0, 200)) . "<br>";
            }
            echo "</div>";
            
            if ($http_code === 200) {
                echo "<div class='solution'>✅ <strong>¡ÉXITO!</strong> La URL corregida funciona correctamente.</div>";
                $fix_works = $fix_works && true;
            } else {
                echo "<div class='problem'>❌ <strong>Aún hay problemas.</strong> Código HTTP: $http_code</div>";
                $fix_works = false;
            }
            ?>
        </div>

        <?php if (isset($fix_works) && $fix_works): ?>
        <div class="section">
            <h2>🚀 Aplicar Corrección</h2>
            <div class="solution">
                <p>Las URLs corregidas funcionan. <strong>¿Deseas aplicar esta corrección al archivo de configuración?</strong></p>
                
                <form method="post" action="">
                    <button type="submit" name="apply_fix" class="fix-button">
                        ✅ Aplicar Corrección al config.php
                    </button>
                </form>
                
                <p><small>Esto actualizará las constantes SHELLY_OPEN_URL y SHELLY_CLOSE_URL en config/config.php</small></p>
            </div>
        </div>
        <?php endif; ?>

        <?php
        if (isset($_POST['apply_fix'])) {
            echo "<div class='section'>";
            echo "<h2>⚙️ Aplicando Corrección...</h2>";
            
            $config_file = '../config/config.php';
            $config_content = file_get_contents($config_file);
            
            // Backup del archivo original
            file_put_contents($config_file . '.backup.' . date('Y-m-d_H-i-s'), $config_content);
            
            // Aplicar correcciones
            $new_open_url = 'http://admin:67da6c@192.168.1.95/rpc/Switch.Set?id=0&on=false';
            $new_close_url = 'http://admin:67da6c@192.168.1.95/rpc/Switch.Set?id=0&on=true';
            
            $config_content = preg_replace(
                '/define\(\'SHELLY_OPEN_URL\',\s*"[^"]+"\);/',
                'define(\'SHELLY_OPEN_URL\', "' . $new_open_url . '");',
                $config_content
            );
            
            $config_content = preg_replace(
                '/define\(\'SHELLY_CLOSE_URL\',\s*"[^"]+"\);/',
                'define(\'SHELLY_CLOSE_URL\', "' . $new_close_url . '");',
                $config_content
            );
            
            if (file_put_contents($config_file, $config_content)) {
                echo "<div class='solution'>";
                echo "<h3>✅ Corrección Aplicada Exitosamente</h3>";
                echo "<p><strong>Cambios realizados:</strong></p>";
                echo "<div class='code'>";
                echo "SHELLY_OPEN_URL: $new_open_url<br>";
                echo "SHELLY_CLOSE_URL: $new_close_url";
                echo "</div>";
                echo "<p><strong>Archivo de backup creado:</strong> " . basename($config_file . '.backup.' . date('Y-m-d_H-i-s')) . "</p>";
                echo "</div>";
                
                echo "<div class='solution'>";
                echo "<h3>🎯 Próximos Pasos</h3>";
                echo "<ul>";
                echo "<li>Probar el sistema de control de acceso</li>";
                echo "<li>Verificar que los comandos abrir/cerrar funcionen desde la aplicación</li>";
                echo "<li>Monitorear logs para asegurar estabilidad</li>";
                echo "</ul>";
                echo "</div>";
            } else {
                echo "<div class='problem'>";
                echo "<h3>❌ Error al Aplicar Corrección</h3>";
                echo "<p>No se pudo escribir el archivo de configuración. Verificar permisos.</p>";
                echo "</div>";
            }
            
            echo "</div>";
        }
        ?>

        <div class="section">
            <h2>📋 Información Adicional</h2>
            <div class="info">
                <h3>🔍 ¿Por qué funciona esta solución?</h3>
                <ul>
                    <li><strong>Autenticación HTTP Basic:</strong> Las credenciales se incluyen directamente en la URL</li>
                    <li><strong>Formato correcto:</strong> http://usuario:contraseña@servidor/ruta</li>
                    <li><strong>Sin redirección:</strong> Evita el error HTTP 302 proporcionando auth desde el inicio</li>
                    <li><strong>Compatible:</strong> Funciona con dispositivos Shelly Pro 4PM</li>
                </ul>
                
                <h3>🛡️ Consideraciones de Seguridad</h3>
                <ul>
                    <li>Las credenciales estarán visibles en logs de servidor web</li>
                    <li>Considerar usar HTTPS en producción</li>
                    <li>Rotar contraseñas periódicamente</li>
                    <li>Configurar firewall para restringir acceso</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>