<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Código QR - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <?php
    $primaryColor = $systemSettings['theme_primary_color'] ?? '#2563eb';
    $secondaryColor = $systemSettings['theme_secondary_color'] ?? '#1e40af';
    ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        :root {
            --color-primary: <?php echo $primaryColor; ?>;
            --color-secondary: <?php echo $secondaryColor; ?>;
        }
        .bg-primary { background-color: var(--color-primary) !important; }
        .text-primary { color: var(--color-primary) !important; }
        .border-primary { border-color: var(--color-primary) !important; }
        
        #qr-reader {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }
        #qr-reader video {
            border-radius: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <!-- Header -->
    <header class="bg-primary shadow-lg">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="<?php echo BASE_URL; ?>/visitors/register" class="text-white/80 hover:text-white">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
                <div class="flex items-center">
                    <?php if (!empty($systemSettings['site_logo'])): ?>
                        <img src="<?php echo BASE_URL . $systemSettings['site_logo']; ?>" alt="Logo" class="h-10 mr-3">
                    <?php else: ?>
                        <i class="fas fa-qrcode text-white text-2xl mr-3"></i>
                    <?php endif; ?>
                    <h1 class="text-white text-xl font-bold"><?php echo $systemSettings['site_name'] ?? 'Control de Acceso'; ?></h1>
                </div>
                <div class="w-20"></div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Validar Código QR</h2>
                <p class="text-gray-600">Escanea o ingresa el código QR del visitante</p>
            </div>
            
            <!-- Tabs for Scanner/Manual -->
            <div class="flex justify-center mb-6">
                <button type="button" id="btnScanner" 
                        onclick="showScanner()"
                        class="bg-primary text-white font-semibold py-3 px-6 rounded-l-lg flex items-center">
                    <i class="fas fa-camera mr-2"></i>Escanear QR
                </button>
                <button type="button" id="btnManual"
                        onclick="showManual()"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-r-lg flex items-center">
                    <i class="fas fa-keyboard mr-2"></i>Ingresar Manual
                </button>
            </div>
            
            <!-- QR Scanner Section -->
            <div id="scannerSection" class="mb-6">
                <div id="qr-reader" class="mb-4"></div>
                <p class="text-center text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Apunta la cámara al código QR del pase
                </p>
            </div>
            
            <!-- Manual Input Section -->
            <div id="manualSection" class="hidden mb-6">
                <form method="POST" id="manualForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Código QR
                        </label>
                        <input type="text" name="pass_code" id="passCodeInput"
                               placeholder="VIS-XXXXXXXX-XXXXXXXX"
                               class="w-full rounded-lg border-2 border-primary focus:border-blue-500 focus:ring-blue-500 text-lg py-3 px-4">
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-primary hover:opacity-90 text-white font-semibold py-3 px-6 rounded-lg">
                        <i class="fas fa-search mr-2"></i>Validar Código
                    </button>
                </form>
            </div>
            
            <!-- Validation Result -->
            <?php if (isset($validationResult)): ?>
            <div class="mt-6">
                <?php if ($validationResult['valid']): ?>
                <!-- Valid Pass -->
                <div class="bg-green-50 border-2 border-green-500 rounded-xl p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-check text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-green-800">¡Pase Válido!</h3>
                            <p class="text-green-600"><?php echo htmlspecialchars($validationResult['message']); ?></p>
                        </div>
                    </div>
                    
                    <?php if (isset($validationResult['visitor'])): ?>
                    <div class="bg-white rounded-lg p-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nombre:</span>
                            <span class="font-semibold"><?php echo htmlspecialchars($validationResult['visitor']['visitor_name'] ?? 'No proporcionado'); ?></span>
                        </div>
                        <?php if (!empty($validationResult['visitor']['plate_number'])): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Placa:</span>
                            <span class="font-semibold font-mono"><?php echo htmlspecialchars($validationResult['visitor']['plate_number']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($validationResult['visitor']['phone'])): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Teléfono:</span>
                            <span class="font-semibold"><?php echo htmlspecialchars($validationResult['visitor']['phone']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($validationResult['visitor']['visit_type'])): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tipo de Visita:</span>
                            <span class="font-semibold capitalize"><?php echo htmlspecialchars($validationResult['visitor']['visit_type']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($validationResult['visitor']['valid_until'])): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Válido hasta:</span>
                            <span class="font-semibold"><?php echo date('d/m/Y H:i', strtotime($validationResult['visitor']['valid_until'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php else: ?>
                <!-- Invalid Pass -->
                <div class="bg-red-50 border-2 border-red-500 rounded-xl p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-times text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-red-800">Pase No Válido</h3>
                            <p class="text-red-600"><?php echo htmlspecialchars($validationResult['message']); ?></p>
                        </div>
                    </div>
                    
                    <?php if (isset($validationResult['visitor']) && $validationResult['status'] !== 'not_found'): ?>
                    <div class="bg-white rounded-lg p-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nombre:</span>
                            <span class="font-semibold"><?php echo htmlspecialchars($validationResult['visitor']['visitor_name'] ?? 'No proporcionado'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Estado:</span>
                            <span class="font-semibold text-red-600 capitalize"><?php echo htmlspecialchars($validationResult['status']); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- New Validation Button -->
            <div class="mt-6 text-center">
                <a href="<?php echo BASE_URL; ?>/visitors/validateQr" 
                   class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg">
                    <i class="fas fa-redo mr-2"></i>Nueva Validación
                </a>
            </div>
        </div>
    </main>
    
    <script>
        let html5QrCode = null;
        let scannerStarted = false;
        
        function showScanner() {
            document.getElementById('scannerSection').classList.remove('hidden');
            document.getElementById('manualSection').classList.add('hidden');
            document.getElementById('btnScanner').classList.remove('bg-gray-500');
            document.getElementById('btnScanner').classList.add('bg-primary');
            document.getElementById('btnManual').classList.remove('bg-primary');
            document.getElementById('btnManual').classList.add('bg-gray-500');
            
            if (!scannerStarted) {
                startScanner();
            }
        }
        
        function showManual() {
            document.getElementById('scannerSection').classList.add('hidden');
            document.getElementById('manualSection').classList.remove('hidden');
            document.getElementById('btnManual').classList.remove('bg-gray-500');
            document.getElementById('btnManual').classList.add('bg-primary');
            document.getElementById('btnScanner').classList.remove('bg-primary');
            document.getElementById('btnScanner').classList.add('bg-gray-500');
            
            if (scannerStarted && html5QrCode) {
                html5QrCode.stop().catch(err => console.log('Error stopping scanner:', err));
                scannerStarted = false;
            }
        }
        
        function startScanner() {
            html5QrCode = new Html5Qrcode("qr-reader");
            
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };
            
            html5QrCode.start(
                { facingMode: "environment" },
                config,
                (decodedText, decodedResult) => {
                    // QR code detected - submit form
                    html5QrCode.stop().then(() => {
                        scannerStarted = false;
                        submitQrCode(decodedText);
                    }).catch(err => {
                        console.log('Error stopping scanner:', err);
                        submitQrCode(decodedText);
                    });
                },
                (errorMessage) => {
                    // Scan error - ignore
                }
            ).then(() => {
                scannerStarted = true;
            }).catch(err => {
                console.log('Error starting scanner:', err);
                // Show manual input if camera fails
                showManual();
                alert('No se pudo acceder a la cámara. Por favor ingrese el código manualmente.');
            });
        }
        
        function submitQrCode(code) {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.href;
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'pass_code';
            input.value = code;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
        
        // Start scanner by default
        document.addEventListener('DOMContentLoaded', function() {
            // Check if there's already a result, don't start scanner
            <?php if (!isset($validationResult)): ?>
            startScanner();
            <?php endif; ?>
        });
    </script>
</body>
</html>
