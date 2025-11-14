<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Entrada - <?php echo $access['ticket_code']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 20px;
            }
        }
        
        .ticket {
            width: 80mm;
            margin: 0 auto;
            background: white;
            padding: 10mm;
            border: 2px dashed #ccc;
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
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-print mr-2"></i>Imprimir Ticket
            </button>
        </div>
        
        <!-- Ticket -->
        <div class="ticket">
            <!-- Encabezado -->
            <div class="text-center mb-4 border-b-2 border-gray-300 pb-4">
                <h1 class="text-2xl font-bold text-gray-900">DUNAS</h1>
                <p class="text-sm text-gray-600">Control de Acceso</p>
                <p class="text-xs text-gray-500 mt-1">Ticket de Entrada</p>
            </div>
            
            <!-- Código de Barras -->
            <div class="text-center mb-4">
                <svg id="barcode"></svg>
                <p class="text-2xl font-mono font-bold text-gray-900 mt-2"><?php echo $access['ticket_code']; ?></p>
            </div>
            
            <!-- Información -->
            <div class="space-y-2 text-sm border-t-2 border-gray-300 pt-4">
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
                    <span class="text-gray-900"><?php echo number_format($access['capacity_liters']); ?> L</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold text-gray-700">Cliente:</span>
                    <span class="text-gray-900 text-right"><?php echo htmlspecialchars($access['client_name']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold text-gray-700">Chofer:</span>
                    <span class="text-gray-900 text-right"><?php echo htmlspecialchars($access['driver_name']); ?></span>
                </div>
            </div>
            
            <!-- Instrucciones -->
            <div class="mt-4 pt-4 border-t-2 border-gray-300">
                <p class="text-xs text-gray-600 text-center">
                    <i class="fas fa-info-circle mr-1"></i>
                    Presente este ticket al salir
                </p>
                <p class="text-xs text-gray-600 text-center mt-1">
                    El código de barras será escaneado automáticamente
                </p>
            </div>
            
            <!-- Footer -->
            <div class="mt-4 text-center border-t border-gray-300 pt-3">
                <p class="text-xs text-gray-500">Gracias por su preferencia</p>
                <p class="text-xs text-gray-400 mt-1">Sistema DUNAS v1.2</p>
            </div>
        </div>
        
        <!-- Información adicional (No se imprime) -->
        <div class="no-print mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-blue-900 mb-2">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información
            </h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Entrada registrada exitosamente</li>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Código de barras: <strong><?php echo $access['ticket_code']; ?></strong></li>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Al salir, escanee el código para registrar automáticamente</li>
                <li><i class="fas fa-check text-blue-600 mr-2"></i>Se registrará con la capacidad máxima de <?php echo number_format($access['capacity_liters']); ?> litros</li>
            </ul>
        </div>
    </div>
    
    <script>
        // Generar código de barras
        JsBarcode("#barcode", "<?php echo $access['ticket_code']; ?>", {
            format: "CODE128",
            width: 2,
            height: 60,
            displayValue: false,
            margin: 0
        });
        
        // Redirigir a la vista de registro de salida después de imprimir (donde está el botón de permitir acceso)
        let hasRedirected = false;
        window.addEventListener('afterprint', function() {
            if (!hasRedirected) {
                hasRedirected = true;
                window.location.href = '<?php echo BASE_URL; ?>/access/registerExit/<?php echo $access['id']; ?>';
            }
        });
    </script>
</body>
</html>
