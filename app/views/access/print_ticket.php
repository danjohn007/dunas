<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Entrada - <?php echo $access['ticket_code']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- QRCode library with fallback -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <?php
    // Get theme colors from settings
    require_once APP_PATH . '/models/Settings.php';
    $settingsModel = new Settings();
    $themeSettings = $settingsModel->getAll();
    $primaryColor = $themeSettings['theme_primary_color'] ?? '#2563eb';
    $secondaryColor = $themeSettings['theme_secondary_color'] ?? '#1e40af';
    ?>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .ticket {
                border: none;
                padding: 5mm;
            }
        }
        
        .ticket {
            width: 58mm;
            margin: 0 auto;
            background: white;
            padding: 5mm;
            border: 2px dashed #ccc;
        }
        
        /* Theme button styles */
        .btn-primary {
            background-color: <?php echo $primaryColor; ?>;
        }
        .btn-primary:hover {
            background-color: <?php echo $secondaryColor; ?>;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-md mx-auto p-4">
        <!-- Botones de Acción (No se imprimen) -->
        <div class="no-print mb-4 flex justify-between">
            <a href="<?php echo BASE_URL; ?>/access/detail/<?php echo $access['id']; ?>" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
            <button onclick="window.print()" 
                    class="btn-primary text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-print mr-2"></i>Imprimir Ticket
            </button>
        </div>
        
        <!-- Ticket -->
        <div class="ticket">
            <!-- Código QR -->
            <div class="text-center mb-3">
                <div id="qrcode" class="mx-auto inline-block"></div>
            </div>
            
            <!-- Código del Ticket -->
            <div class="text-center mb-3">
                <p class="text-3xl font-mono font-bold text-gray-900"><?php echo $access['ticket_code']; ?>#</p>
            </div>
            
            <!-- Separador -->
            <div class="border-t border-gray-400 mb-3"></div>
            
            <!-- Información -->
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="font-semibold text-gray-700">Fecha:</span>
                    <span class="text-gray-900"><?php echo date('d/m/Y', strtotime($access['entry_datetime'])); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold text-gray-700">Hora:</span>
                    <span class="text-gray-900"><?php echo date('H:i', strtotime($access['entry_datetime'])); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold text-gray-700">Unidad:</span>
                    <span class="text-gray-900"><?php echo htmlspecialchars($access['plate_number']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold text-gray-700">Capacidad:</span>
                    <span class="text-gray-900"><?php echo number_format($access['capacity_liters'], 0); ?> L</span>
                </div>
                <div class="flex justify-between items-start">
                    <span class="font-semibold text-gray-700">Cliente:</span>
                    <span class="text-gray-900 text-right text-xs break-words max-w-[60%]"><?php echo htmlspecialchars($access['client_name']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold text-gray-700">Chofer:</span>
                    <span class="text-gray-900 text-right text-xs"><?php echo htmlspecialchars($access['driver_name']); ?></span>
                </div>
                <?php if (!empty($access['cost'])): ?>
                <div class="flex justify-between">
                    <span class="font-semibold text-gray-700">Costo:</span>
                    <span class="text-gray-900 font-bold">$<?php echo number_format($access['cost'], 2); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($access['payment_method'])): ?>
                <div class="flex justify-between">
                    <span class="font-semibold text-gray-700">Método de Pago:</span>
                    <span class="text-gray-900 text-xs">
                        <?php 
                        $methodLabels = [
                            'cash' => 'Efectivo',
                            'voucher' => 'Vales',
                            'bank_transfer' => 'Transferencia'
                        ];
                        echo $methodLabels[$access['payment_method']] ?? 'Efectivo';
                        ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Información adicional (No se imprime) -->
        <div class="no-print mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-blue-900 mb-2">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información
            </h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Entrada registrada exitosamente</li>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Código QR: <strong><?php echo $access['ticket_code']; ?></strong></li>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Al salir, escanee el código para registrar automáticamente</li>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Se registrará con la capacidad máxima de <?php echo number_format($access['capacity_liters']); ?> litros</li>
                <?php if (!empty($access['cost'])): ?>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Costo: <strong>$<?php echo number_format($access['cost'], 2); ?></strong></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <?php
    // Get QR settings
    require_once APP_PATH . '/models/Settings.php';
    $settingsModel = new Settings();
    $qrSize = (int)$settingsModel->get('qr_size', '120');
    if ($qrSize < 100) $qrSize = 120;
    if ($qrSize > 500) $qrSize = 500;
    ?>
    
    <script>
        // Generar código QR with settings size using QRCode.js library
        try {
            if (typeof QRCode !== 'undefined') {
                new QRCode(document.getElementById('qrcode'), {
                    text: "<?php echo $access['ticket_code']; ?>",
                    width: <?php echo $qrSize; ?>,
                    height: <?php echo $qrSize; ?>,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.L
                });
            } else {
                console.error('QRCode library not loaded');
                document.getElementById('qrcode').innerHTML = '<p class="text-red-500">Error: No se pudo cargar el código QR</p>';
            }
        } catch (error) {
            console.error('Error generating QR code:', error);
            document.getElementById('qrcode').innerHTML = '<p class="text-red-500">Error: ' + error.message + '</p>';
        }
        
        // Redirigir a la vista de registro de salida después de imprimir (donde está el botón de permitir acceso)
        let hasRedirected = false;
        window.addEventListener('afterprint', function() {
            if (!hasRedirected) {
                hasRedirected = true;
                window.location.href = '<?php echo BASE_URL; ?>/access/registerExit/<?php echo $access['id']; ?>';
            }
        });
    </script>
    
    <?php 
    // Si está habilitado el modo local del bridge Hikvision, ejecutar petición client-side
    if (defined('HIKVISION_BRIDGE_LOCAL_MODE') && HIKVISION_BRIDGE_LOCAL_MODE && 
        defined('HIKVISION_ENABLED') && HIKVISION_ENABLED): 
    ?>
    <script>
        // Configuración del bridge local
        const BRIDGE_LOCAL_URL = '<?php echo defined('HIKVISION_BRIDGE_LOCAL_URL') ? HIKVISION_BRIDGE_LOCAL_URL : 'http://127.0.0.1:8080'; ?>';
        
        <?php if (isset($_SESSION['hikvision_pending_users']) && !empty($_SESSION['hikvision_pending_users'])): ?>
        // Usuarios pendientes para enviar al bridge
        const pendingUsers = <?php echo json_encode($_SESSION['hikvision_pending_users']); ?>;
        
        console.log('🏠 Modo Local Hikvision:', pendingUsers.length, 'usuario(s) pendientes');
        
        // Enviar cada usuario al bridge local
        pendingUsers.forEach(userData => {
            const endpoint = BRIDGE_LOCAL_URL + '/create-ticket-user';
            
            console.log('📤 Enviando a:', endpoint, userData);
            
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    device_user_id: userData.device_user_id,
                    name: userData.name,
                    pin: userData.pin,
                    card_number: userData.card_number,
                    hours_valid: userData.hours_valid
                }),
                mode: 'cors',
                cache: 'no-cache'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.ok === true || data.success === true) {
                    console.log('✅ Usuario creado:', userData.device_user_id, 'PIN:', userData.pin);
                    
                    // Mostrar notificación
                    const msg = document.createElement('div');
                    msg.className = 'no-print fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg z-50';
                    msg.innerHTML = `
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span><strong>Usuario creado:</strong> ${userData.device_user_id}<br>
                            <small>PIN: ${userData.pin} | Válido: ${userData.hours_valid}h</small></span>
                        </div>
                    `;
                    document.body.appendChild(msg);
                    setTimeout(() => msg.remove(), 8000);
                } else {
                    console.warn('⚠️ Respuesta inesperada:', data);
                }
            })
            .catch(error => {
                console.error('❌ Error al enviar usuario:', error);
                
                // Mostrar notificación de error
                const msg = document.createElement('div');
                msg.className = 'no-print fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg z-50';
                msg.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-times-circle mr-2"></i>
                        <span><strong>Error al crear usuario</strong><br>
                        <small>${error.message}</small><br>
                        <small class="text-xs">Verifica que el bridge esté corriendo en la PC</small></span>
                    </div>
                `;
                document.body.appendChild(msg);
                setTimeout(() => msg.remove(), 10000);
            });
        });
        
        <?php 
        // Limpiar la sesión después de cargarlos en JavaScript
        unset($_SESSION['hikvision_pending_users']);
        ?>
        <?php else: ?>
        console.log('ℹ️ Modo Local Hikvision: No hay usuarios pendientes');
        <?php endif; ?>
    </script>
    <?php endif; ?>
</body>
</html>
