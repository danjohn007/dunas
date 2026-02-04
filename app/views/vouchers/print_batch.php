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
                width: 5.5in; /* 1/2 of letter width */
                height: 4.25in; /* 1/2 of letter height */
                page-break-inside: avoid;
                margin: 0;
                padding: 0.25in;
            }
        }
        
        @page {
            size: letter;
            margin: 0.25in;
        }
        
        .voucher-card {
            width: 5.5in;
            height: 4.25in;
            border: 2px solid #2c5f3b;
            border-radius: 8px;
            padding: 0.5in;
            margin: 0.25in;
            display: inline-block;
            vertical-align: top;
            background: white;
            box-sizing: border-box;
        }
        
        .voucher-title {
            color: #2c5f3b;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 12px;
            letter-spacing: 1px;
        }
        
        .voucher-field {
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .voucher-label {
            color: #2c5f3b;
            font-weight: 600;
            display: inline-block;
            min-width: 120px;
        }
        
        .voucher-value {
            border-bottom: 1px solid #cbd5e0;
            display: inline-block;
            min-width: 200px;
            padding-bottom: 2px;
        }
        
        .voucher-footer {
            color: #2c5f3b;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin-top: 12px;
            padding-top: 8px;
            border-top: 2px solid #2c5f3b;
        }
        
        .folio-badge {
            background: #e74c3c;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            display: inline-block;
        }
        
        .qr-container {
            text-align: center;
            margin: 12px 0;
        }
        
        .qr-code canvas {
            max-width: 140px !important;
            max-height: 140px !important;
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
            Los vales se imprimirán en formato 1/2 carta (5.5" x 4.25"). Se imprimen 2 vales por página.
        </p>
    </div>

    <!-- Vouchers Grid -->
    <div class="vouchers-container">
        <?php foreach ($vouchers as $index => $voucher): ?>
        <div class="voucher-card">
            <!-- Header -->
            <div class="voucher-title">SUMINISTRO DE AGUA</div>
            
            <!-- Main Content -->
            <div class="grid grid-cols-2 gap-4" style="margin-top: 16px;">
                <!-- Left Column -->
                <div>
                    <div class="voucher-field">
                        <span class="voucher-label">EMPRESA:</span>
                        <span class="voucher-value">_________________</span>
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
                        <span class="voucher-label">TELÉFONO:</span>
                        <span class="voucher-value">_________________</span>
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
                
                <!-- Right Column - QR Code and Folio -->
                <div class="text-right">
                    <div class="qr-container">
                        <div id="qrcode-<?php echo $voucher['id']; ?>" class="qr-code inline-block"></div>
                    </div>
                    <div class="text-center mt-2">
                        <div class="text-sm" style="color: #e74c3c; font-weight: bold;">FOLIO</div>
                        <div class="text-sm" style="color: #e74c3c; font-weight: bold;">SERIE "<?php echo htmlspecialchars($voucher['serie']); ?>"</div>
                        <div class="folio-badge" style="margin-top: 4px;">
                            N° <?php echo str_pad($voucher['folio'], 4, '0', STR_PAD_LEFT); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="voucher-footer">
                AGUA DE SERVICIOS
            </div>
        </div>
        <?php 
        // Add page break after every 2 vouchers
        if (($index + 1) % 2 === 0 && $index < count($vouchers) - 1): 
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
                width: 140,
                height: 140,
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
