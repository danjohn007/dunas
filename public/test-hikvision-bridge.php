<?php
/**
 * Página de prueba para el PC Puente Hikvision
 * Permite verificar la conexión y probar la creación de usuarios
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/services/HikvisionBridgeService.php';

$message = '';
$messageType = '';

// Procesar acción si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    $bridge = new HikvisionBridgeService();
    
    switch ($action) {
        case 'test_connection':
            if ($bridge->testConnection()) {
                $message = "✅ Conexión exitosa con el PC puente: " . $bridge->getBridgeUrl();
                $messageType = 'success';
            } else {
                $message = "❌ No se pudo conectar con el PC puente: " . $bridge->getBridgeUrl();
                $messageType = 'error';
            }
            break;
            
        case 'create_test_user':
            $testUserId = "TEST-" . time();
            $testName = $_POST['test_name'] ?? "Usuario Test";
            $testPin = $_POST['test_pin'] ?? rand(1000, 9999);
            $testHours = intval($_POST['test_hours'] ?? 1);
            
            $result = $bridge->createTicketUser($testUserId, $testName, $testPin, $testHours);
            
            if ($result['success']) {
                $message = "✅ Usuario de prueba creado exitosamente<br>";
                $message .= "ID: {$testUserId}<br>";
                $message .= "Nombre: {$testName}<br>";
                $message .= "PIN: <strong>{$testPin}</strong><br>";
                $message .= "Válido: {$testHours} hora(s)";
                $messageType = 'success';
            } else {
                $message = "❌ Error al crear usuario: " . $result['message'];
                $messageType = 'error';
            }
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - PC Puente Hikvision</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 20px;
        }
        
        .config-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        
        .config-info div {
            margin-bottom: 5px;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .test-section {
            border-top: 2px solid #e9ecef;
            padding-top: 20px;
            margin-top: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-enabled {
            background: #d4edda;
            color: #155724;
        }
        
        .status-disabled {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🔧 Test PC Puente Hikvision</h1>
            <p class="subtitle">Prueba la conexión y funcionalidad del puente de comunicación</p>
            
            <!-- Configuración actual -->
            <div class="config-info">
                <strong>📋 Configuración actual:</strong><br>
                <div>Bridge URL: <?php echo defined('HIKVISION_BRIDGE_URL') ? HIKVISION_BRIDGE_URL : 'No configurada'; ?></div>
                <div>Timeout: <?php echo defined('HIKVISION_BRIDGE_TIMEOUT') ? HIKVISION_BRIDGE_TIMEOUT . ' segundos' : 'No configurado'; ?></div>
                <div>Validez PIN: <?php echo defined('HIKVISION_USER_VALIDITY_HOURS') ? HIKVISION_USER_VALIDITY_HOURS . ' hora(s)' : 'No configurado'; ?></div>
                <div>Estado: 
                    <?php if (defined('HIKVISION_ENABLED') && HIKVISION_ENABLED): ?>
                        <span class="status-badge status-enabled">HABILITADO</span>
                    <?php else: ?>
                        <span class="status-badge status-disabled">DESHABILITADO</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Mensaje de resultado -->
            <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <!-- Test de conexión -->
            <form method="POST">
                <input type="hidden" name="action" value="test_connection">
                <button type="submit" class="btn btn-primary">🔌 Probar Conexión</button>
            </form>
            
            <!-- Crear usuario de prueba -->
            <div class="test-section">
                <h3 style="margin-bottom: 15px;">👤 Crear Usuario de Prueba</h3>
                
                <form method="POST">
                    <input type="hidden" name="action" value="create_test_user">
                    
                    <div class="form-group">
                        <label>Nombre del Usuario</label>
                        <input type="text" name="test_name" value="Usuario Test" placeholder="Nombre del usuario">
                    </div>
                    
                    <div class="form-group">
                        <label>PIN (4 dígitos)</label>
                        <input type="text" name="test_pin" value="<?php echo rand(1000, 9999); ?>" 
                               pattern="\d{4}" maxlength="4" placeholder="1234">
                    </div>
                    
                    <div class="form-group">
                        <label>Horas de Validez</label>
                        <input type="number" name="test_hours" value="1" min="1" max="24" placeholder="1">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">➕ Crear Usuario de Prueba</button>
                </form>
            </div>
            
            <!-- Información adicional -->
            <div class="test-section">
                <h3 style="margin-bottom: 10px;">ℹ️ Información</h3>
                <p style="color: #666; line-height: 1.6;">
                    Este test verifica la comunicación con el PC puente que gestiona el dispositivo Hikvision.<br>
                    Si la conexión falla, verifica:
                </p>
                <ul style="color: #666; margin: 10px 0 10px 20px; line-height: 1.8;">
                    <li>El PC puente está encendido y conectado a la red</li>
                    <li>El servicio en el puerto 8080 está activo</li>
                    <li>La URL en <code>config/config.php</code> es correcta</li>
                    <li>No hay firewall bloqueando la conexión</li>
                </ul>
            </div>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="../app/views/dashboard/index.php" class="btn btn-secondary">← Volver al Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
