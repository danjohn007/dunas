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
        <?php $isImprentaMode = isset($printMode) && $printMode === 'imprenta'; ?>
        @media print {
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
            body { margin: 0; }
            .voucher-card {
                <?php if ($isImprentaMode): ?>
                width: 9.2cm;
                height: 12.5cm;
                <?php else: ?>
                width: 3.75in; /* Fits 2 per row with margins */
                height: 3.25in; /* Fits 3 per column with margins */
                <?php endif; ?>
                page-break-inside: avoid;
                margin: <?php echo $isImprentaMode ? '0.2cm' : '0.1in'; ?>;
                padding: <?php echo $isImprentaMode ? '0.35cm' : '0.15in'; ?>;
            }
        }
        
        @page {
            <?php if ($isImprentaMode): ?>
            size: auto;
            margin: 1cm;
            <?php else: ?>
            size: letter;
            margin: 0.25in;
            <?php endif; ?>
        }

        .vouchers-container {
            <?php if ($isImprentaMode): ?>
            display: grid;
            grid-template-columns: repeat(4, 9.2cm);
            gap: 0.6cm;
            justify-content: center;
            padding: 1cm;
            <?php endif; ?>
        }
        
        .voucher-card {
            <?php if ($isImprentaMode): ?>
            width: 9.2cm;
            height: 12.5cm;
            <?php else: ?>
            width: 3.75in; /* Fits 2 per row with margins */
            height: 3.25in; /* Fits 3 per column with margins */
            <?php endif; ?>
            border: 2px solid #2c5f3b;
            border-radius: 5px;
            padding: <?php echo $isImprentaMode ? '0.35cm' : '0.25in'; ?>;
            margin: <?php echo $isImprentaMode ? '0.2cm' : '0.1in'; ?>;
            display: <?php echo $isImprentaMode ? 'block' : 'inline-block'; ?>;
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
            <?php if ($isImprentaMode): ?>
            Formato imprenta: 9.2 x 12.5 cm con margen adicional de 1 cm y distribución de 4 columnas.
            <?php else: ?>
            Los vales se imprimirán en formato carta (8.5" x 11"). Se imprimen 6 vales por página (2 columnas x 3 filas).
            <?php endif; ?>
        </p>
    </div>

    <!-- Vouchers Grid -->
    <div class="vouchers-container">
        <?php foreach ($vouchers as $index => $voucher): ?>
        <div class="voucher-card">
            <!-- Header -->
            <?php if ($isImprentaMode): ?>
            <div style="height: 12px;"></div>
            <?php else: ?>
            <div class="voucher-title">SUMINISTRO DE AGUA</div>
            <?php endif; ?>
            
            <!-- Main Content -->
            <div class="grid grid-cols-2 gap-3" style="margin-top: 12px;">
                <!-- Left Column -->
                <div>
                    <div class="voucher-field">
                        <span class="voucher-label">EMPRESA:</span>
                        <span class="voucher-value">SIN ASIGNAR</span>
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
            <?php if (!$isImprentaMode): ?>
            <div class="voucher-footer">
                AGUA DE SERVICIOS
            </div>
            <?php endif; ?>
        </div>
        <?php 
        $pageBreakEvery = $isImprentaMode ? 12 : 6;
        if (($index + 1) % $pageBreakEvery === 0 && $index < count($vouchers) - 1): 
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
