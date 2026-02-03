<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Vales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .voucher-page {
                page-break-after: always;
            }
            
            .voucher-page:last-child {
                page-break-after: auto;
            }
        }
        
        /* Tamaño 1/4 carta = 5.5" x 4.25" = 13.97cm x 10.795cm */
        .voucher-card {
            width: 5.5in;
            height: 4.25in;
            border: 1px solid #ccc;
            padding: 0.3in;
            box-sizing: border-box;
            position: relative;
            background: white;
        }
        
        .voucher-title {
            font-size: 20px;
            font-weight: bold;
            color: #059669;
            text-align: center;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        
        .voucher-field {
            font-size: 11px;
            margin-bottom: 6px;
            color: #059669;
            font-weight: 600;
        }
        
        .voucher-field-line {
            border-bottom: 1px solid #666;
            display: inline-block;
            min-width: 200px;
            height: 14px;
        }
        
        .voucher-footer {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #059669;
            margin-top: 8px;
        }
        
        .voucher-folio {
            position: absolute;
            right: 0.4in;
            top: 1.5in;
            text-align: center;
        }
        
        .voucher-folio-label {
            font-size: 11px;
            color: #059669;
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .voucher-folio-serie {
            font-size: 10px;
            color: #059669;
            font-weight: 600;
        }
        
        .voucher-folio-number {
            font-size: 20px;
            color: #dc2626;
            font-weight: bold;
            margin-top: -2px;
        }
        
        .qr-container {
            position: absolute;
            right: 0.4in;
            top: 2.3in;
            width: 1.2in;
            height: 1.2in;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .qr-container canvas,
        .qr-container img {
            max-width: 100%;
            max-height: 100%;
        }
    </style>
</head>
<body class="bg-gray-100">
    
    <!-- Botones de control -->
    <div class="no-print fixed top-4 right-4 space-x-2 z-50">
        <button onclick="window.print()" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg">
            <i class="fas fa-print mr-2"></i>Imprimir
        </button>
        <button onclick="window.close()" 
                class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg">
            <i class="fas fa-times mr-2"></i>Cerrar
        </button>
    </div>
    
    <!-- Container de vales -->
    <div class="no-print p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Vista Previa de Vales</h1>
        <p class="text-gray-600 mb-6">Se imprimirán <?php echo count($vouchers); ?> vale(s)</p>
    </div>
    
    <div class="flex flex-wrap justify-center gap-4 p-4">
        <?php foreach ($vouchers as $index => $voucher): ?>
            <div class="voucher-page">
                <div class="voucher-card">
                    
                    <!-- Título -->
                    <div class="voucher-title">SUMINISTRO DE AGUA</div>
                    
                    <!-- Campos del vale -->
                    <div style="width: 60%;">
                        <div class="voucher-field">
                            EMPRESA: <span class="voucher-field-line"></span>
                        </div>
                        
                        <div class="voucher-field">
                            OPERADOR: <span class="voucher-field-line"></span>
                        </div>
                        
                        <div class="voucher-field">
                            PLACAS: <span class="voucher-field-line"></span>
                        </div>
                        
                        <div class="voucher-field">
                            CAPACIDAD: <span class="voucher-field-line"></span>
                        </div>
                        
                        <div class="voucher-field">
                            TELEFONO: <span class="voucher-field-line"></span>
                        </div>
                        
                        <div class="voucher-field">
                            FECHA: <span class="voucher-field-line"></span>
                        </div>
                        
                        <div class="voucher-field">
                            HORA DE CARGA: <span class="voucher-field-line"></span>
                        </div>
                    </div>
                    
                    <!-- Folio y Serie -->
                    <div class="voucher-folio">
                        <div class="voucher-folio-label">FOLIO</div>
                        <div class="voucher-folio-serie">SERIE "<?php echo htmlspecialchars($voucher['serie']); ?>"</div>
                        <div class="voucher-folio-number">
                            N° <?php echo str_pad($voucher['folio'], 4, '0', STR_PAD_LEFT); ?>
                        </div>
                    </div>
                    
                    <!-- Código QR -->
                    <div class="qr-container">
                        <div id="qr-<?php echo $voucher['id']; ?>" class="qr-code"></div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="voucher-footer">AGUA DE SERVICIOS</div>
                    
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        // Generar códigos QR para cada vale
        document.addEventListener('DOMContentLoaded', function() {
            <?php foreach ($vouchers as $voucher): ?>
                // Generar QR para vale <?php echo $voucher['id']; ?>
                new QRCode(document.getElementById('qr-<?php echo $voucher['id']; ?>'), {
                    text: '<?php echo htmlspecialchars($voucher['qr_code']); ?>',
                    width: 115,
                    height: 115,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
            <?php endforeach; ?>
            
            // Auto-imprimir después de generar todos los QR (opcional)
            // setTimeout(() => window.print(), 1000);
        });
    </script>
</body>
</html>
