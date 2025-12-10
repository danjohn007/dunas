<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pase de Visita - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 20px;
                background: white;
            }
            .pass-container {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }
        }
        
        .pass-header {
            background: linear-gradient(135deg, <?php echo $primaryColor; ?>, <?php echo $secondaryColor; ?>);
        }
        
        .btn-primary {
            background-color: <?php echo $primaryColor; ?>;
        }
        .btn-primary:hover {
            background-color: <?php echo $secondaryColor; ?>;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <!-- Navigation (No Print) -->
    <div class="no-print max-w-4xl mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
            <a href="<?php echo BASE_URL; ?>/visitors" 
               class="inline-flex items-center text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </div>
    
    <!-- Pass Container -->
    <div class="max-w-3xl mx-auto px-4 pb-8">
        <div class="pass-container bg-white rounded-2xl shadow-xl overflow-hidden">
            
            <!-- Header -->
            <div class="pass-header text-white p-6">
                <h1 class="text-2xl font-bold mb-1">Pase de Visita</h1>
                <p class="text-white/80">C¨®digo: <?php echo htmlspecialchars($visitor['pass_code'] ?? 'N/A'); ?></p>
            </div>
            
            <!-- Content -->
            <div class="p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    
                    <!-- QR Code Section -->
                    <div class="flex flex-col items-center">
                        <div id="qrcode" class="mb-4 p-4 bg-white border border-gray-200 rounded-lg"></div>
                        
                        <!-- Print Button -->
                        <button onclick="window.print()" 
                                class="no-print btn-primary text-white font-semibold py-3 px-6 rounded-lg flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
                            <i class="fas fa-print"></i>
                            Imprimir
                        </button>
                    </div>
                    
                    <!-- Visitor Info -->
                    <div class="flex-1 space-y-4">
                        <!-- Visitor Name -->
                        <div>
                            <p class="text-sm text-gray-500">Visitante</p>
                            <p class="text-lg font-semibold text-gray-900">
                                <?php echo htmlspecialchars($visitor['visitor_name'] ?: 'No proporcionado'); ?>
                            </p>
                        </div>
                        
                        <!-- Phone -->
                        <?php if (!empty($visitor['phone'])): ?>
                        <div>
                            <p class="text-sm text-gray-500">Tel¨¦fono</p>
                            <p class="text-lg font-semibold text-gray-900">
                                <?php echo htmlspecialchars($visitor['phone']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Vehicle -->
                        <?php if (!empty($visitor['plate_number'])): ?>
                        <div>
                            <p class="text-sm text-gray-500">Veh¨ªculo</p>
                            <p class="text-lg font-semibold text-gray-900 font-mono">
                                <?php echo htmlspecialchars($visitor['plate_number']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Visit Type -->
                        <?php if (!empty($visitor['visit_type'])): ?>
                        <div>
                            <p class="text-sm text-gray-500">Tipo de Visita</p>
                            <p class="text-lg font-semibold text-gray-900 capitalize">
                                <?php echo htmlspecialchars($visitor['visit_type']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Validity Period -->
                        <?php if (!empty($visitor['valid_from']) && !empty($visitor['valid_until'])): ?>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-sm text-blue-700 font-semibold mb-2">
                                <i class="fas fa-calendar-check mr-1"></i> Vigencia del Pase
                            </p>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-500">Desde:</span>
                                    <p class="font-semibold text-gray-900">
                                        <?php echo date('d/m/Y H:i', strtotime($visitor['valid_from'])); ?>
                                    </p>
                                </div>
                                <div>
                                    <span class="text-gray-500">Hasta:</span>
                                    <p class="font-semibold text-gray-900">
                                        <?php echo date('d/m/Y H:i', strtotime($visitor['valid_until'])); ?>
                                    </p>
                                </div>
                            </div>
                            <?php
                            $now = new DateTime();
                            $validUntil = new DateTime($visitor['valid_until']);
                            $isExpired = $now > $validUntil;
                            ?>
                            <?php if ($isExpired): ?>
                            <p class="mt-2 text-red-600 text-sm font-semibold">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Este pase ha expirado
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <!-- Entry Time (for visitors without validity) -->
                        <div>
                            <p class="text-sm text-gray-500">Entrada</p>
                            <p class="text-lg font-semibold text-gray-900">
                                <?php echo date('d/m/Y H:i', strtotime($visitor['entry_datetime'])); ?>
                            </p>
                        </div>
                        
                        <!-- Exit Time -->
                        <?php if (!empty($visitor['exit_datetime'])): ?>
                        <div>
                            <p class="text-sm text-gray-500">Salida</p>
                            <p class="text-lg font-semibold text-gray-900">
                                <?php echo date('d/m/Y H:i', strtotime($visitor['exit_datetime'])); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Status -->
                        <div>
                            <p class="text-sm text-gray-500">Estado</p>
                            <?php
                            $statusClasses = [
                                'in' => 'bg-yellow-100 text-yellow-800',
                                'out' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'pending' => 'bg-blue-100 text-blue-800'
                            ];
                            $statusLabels = [
                                'in' => 'Pendiente',
                                'out' => 'Completado',
                                'cancelled' => 'Cancelado',
                                'pending' => 'Por Ingresar'
                            ];
                            $statusClass = $statusClasses[$visitor['status']] ?? 'bg-gray-100 text-gray-800';
                            $statusLabel = $statusLabels[$visitor['status']] ?? $visitor['status'];
                            ?>
                            <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo $statusLabel; ?>
                            </span>
                        </div>
                        
                        <!-- Notes -->
                        <?php if (!empty($visitor['notes'])): ?>
                        <div>
                            <p class="text-sm text-gray-500">Notas</p>
                            <p class="text-gray-900">
                                <?php echo nl2br(htmlspecialchars($visitor['notes'])); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 border-t px-6 py-4">
                <p class="text-center text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Presente este pase al personal de seguridad al ingresar y salir
                </p>
            </div>
            
        </div>
    </div>
    
    <?php
    // QR Code size configuration
    // Min size: 100px, Max size: 500px, Default: 200px
    $qrMinSize = 100;
    $qrMaxSize = 500;
    $qrDefaultSize = 200;
    
    $qrSize = (int)($themeSettings['qr_size'] ?? $qrDefaultSize);
    // Clamp to boundary values for consistency
    if ($qrSize < $qrMinSize) $qrSize = $qrMinSize;
    if ($qrSize > $qrMaxSize) $qrSize = $qrMaxSize;
    ?>
    
    <script>
        // Generate QR Code
        // Using error correction level M (Medium ~15%) for better readability on printed passes
        try {
            if (typeof QRCode !== 'undefined') {
                // Pass code format is VIS-YYYYMMDD-XXXXXXXX (alphanumeric and dashes only)
                // No special characters that need escaping for JavaScript string
                new QRCode(document.getElementById('qrcode'), {
                    text: "<?php echo $visitor['pass_code'] ?? ''; ?>",
                    width: <?php echo $qrSize; ?>,
                    height: <?php echo $qrSize; ?>,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            } else {
                console.error('QRCode library not loaded');
                document.getElementById('qrcode').innerHTML = '<p class="text-red-500">Error: No se pudo cargar el c¨®digo QR</p>';
            }
        } catch (error) {
            console.error('Error generating QR code:', error);
            document.getElementById('qrcode').innerHTML = '<p class="text-red-500">Error: ' + error.message + '</p>';
        }
    </script>
    
</body>
</html>
