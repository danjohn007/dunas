<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Vales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
            body { margin: 0; }
            .voucher-card {
                width: 3.75in; /* Fits 2 per row with margins */
                height: 3.25in; /* Fits 3 per column with margins */
                page-break-inside: avoid;
                margin: 0.1in;
                padding: 0.15in;
            }
        }
        
        @page {
            size: letter;
            margin: 0.25in;
        }
        
        .voucher-card {
            width: 3.75in; /* Fits 2 per row with margins */
            height: 3.25in; /* Fits 3 per column with margins */
            border: 2px solid #2c5f3b;
            border-radius: 5px;
            padding: 0.25in;
            margin: 0.1in;
            display: inline-block;
            vertical-align: top;
            background: white;
            box-sizing: border-box;
        }
        
        .voucher-title {
            color: #2c5f3b;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        
        .voucher-field {
            margin-bottom: 4px;
            font-size: 9px;
        }
        
        .voucher-label {
            color: #2c5f3b;
            font-weight: 600;
            display: inline-block;
            min-width: 75px;
        }
        
        .voucher-value {
            border-bottom: 1px solid #cbd5e0;
            display: inline-block;
            min-width: 120px;
            padding-bottom: 1px;
        }
        
        .voucher-footer {
            color: #2c5f3b;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin-top: 6px;
            padding-top: 4px;
            border-top: 2px solid #2c5f3b;
        }
        
        .qr-container {
            text-align: center;
            margin: 0;
        }
        
        .qr-code canvas {
            max-width: 120px !important;
            max-height: 120px !important;
        }
    </style>
</head>
<body class="bg-gray-100">
    
    <!-- Print Controls -->
    <div class="no-print fixed top-4 right-4 z-50 space-x-2">
        <button onclick="window.print()" 
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-lg">
            <i class="fas fa-print mr-2"></i>Imprimir
        </button>
        <a href="<?php echo BASE_URL; ?>/vouchers" 
           class="inline-block px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg shadow-lg">
            <i class="fas fa-times mr-2"></i>Cerrar
        </a>
    </div>
    
    <div class="no-print bg-white shadow-md p-4 mb-4">
        <h1 class="text-2xl font-bold text-gray-900">Vista Previa de Impresión</h1>
        <p class="text-gray-600">Total de vales: <?php echo count($vouchers); ?></p>
        <p class="text-sm text-gray-500 mt-2">
            <i class="fas fa-info-circle"></i>
            Los vales se imprimirán en formato carta (8.5" x 11"). Se imprimen 6 vales por página (2 columnas x 3 filas).
        </p>
    </div>

    <!-- Vouchers Grid -->
    <div class="vouchers-container">
        <?php foreach ($vouchers as $index => $voucher): ?>
        <div class="voucher-card">
            <!-- Header -->
            <div class="voucher-title">SUMINISTRO DE AGUA</div>
            
            <!-- Main Content -->
            <div class="grid grid-cols-2 gap-3" style="margin-top: 12px;">
                <!-- Left Column -->
                <div>
                    <div class="voucher-field">
                        <span class="voucher-label">EMPRESA:</span>
                        <span class="voucher-value"><?php echo !empty($voucher['client_name']) ? htmlspecialchars($voucher['client_name']) : '_________________'; ?></span>
                    </div>
                    <div class="voucher-field">
                        <span class="voucher-label">OPERADOR:</span>
                        <span class="voucher-value">_________________</span>
                    </div>
                    <div class="voucher-field">
                        <span class="voucher-label">PLACAS:</span>
                        <span class="voucher-value">_________________</span>
                    </div>
                    <div class="voucher-field">
                        <span class="voucher-label">CAPACIDAD:</span>
                        <span class="voucher-value"><?php echo number_format($voucher['capacity']); ?> L</span>
                    </div>
                    <div class="voucher-field">
                        <span class="voucher-label">FECHA:</span>
                        <span class="voucher-value">_________________</span>
                    </div>
                    <div class="voucher-field">
                        <span class="voucher-label">HORA DE CARGA:</span>
                        <span class="voucher-value">_________________</span>
                    </div>
                </div>
                
                <!-- Right Column - QR Code Only -->
                <div class="flex items-center justify-center">
                    <div class="qr-container">
                        <div id="qrcode-<?php echo $voucher['id']; ?>" class="qr-code inline-block"></div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="voucher-footer">
                AGUA DE SERVICIOS
            </div>
        </div>
        <?php 
        // Add page break after every 6 vouchers (2 columns x 3 rows)
        if (($index + 1) % 6 === 0 && $index < count($vouchers) - 1): 
        ?>
        <div class="page-break"></div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <script>
    // Generate QR codes for all vouchers
    document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($vouchers as $voucher): ?>
        try {
            new QRCode(document.getElementById('qrcode-<?php echo $voucher['id']; ?>'), {
                text: '<?php echo htmlspecialchars($voucher['qr_code']); ?>',
                width: 120,
                height: 120,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        } catch (error) {
            console.error('Error generating QR code for voucher <?php echo $voucher['id']; ?>:', error);
            document.getElementById('qrcode-<?php echo $voucher['id']; ?>').innerHTML = 
                '<p style="color: red; font-size: 12px;">Error QR</p>';
        }
        <?php endforeach; ?>
        
        // Auto-print after QR codes are generated
        setTimeout(function() {
            // Optional: Uncomment to auto-print
            // window.print();
        }, 1000);
    });
    </script>
</body>
</html>
