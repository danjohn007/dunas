<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Acceso - Parque Acuático</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <?php
    $primaryColor   = $systemSettings['theme_primary_color']   ?? '#2563eb';
    $secondaryColor = $systemSettings['theme_secondary_color'] ?? '#1e40af';
    ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        :root {
            --color-primary: <?php echo $primaryColor; ?>;
            --color-secondary: <?php echo $secondaryColor; ?>;
        }
        .bg-primary   { background-color: var(--color-primary)   !important; }
        .text-primary { color: var(--color-primary)              !important; }
        #qr-reader { width: 100%; max-width: 400px; margin: 0 auto; }
        #qr-reader video { border-radius: 0.5rem; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Header -->
    <header class="bg-primary shadow-lg">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-center">
                <?php if (!empty($systemSettings['site_logo'])): ?>
                    <img src="<?php echo BASE_URL . $systemSettings['site_logo']; ?>" alt="Logo" class="h-10 mr-3">
                <?php else: ?>
                    <i class="fas fa-water text-white text-2xl mr-3"></i>
                <?php endif; ?>
                <div class="text-white">
                    <h1 class="text-xl font-bold"><?php echo htmlspecialchars($systemSettings['site_name'] ?? 'Parque Acuático'); ?></h1>
                    <p class="text-white/70 text-sm">Validación de Acceso</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Main -->
    <main class="max-w-lg mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-1">Validar Código QR</h2>
                <p class="text-gray-500 text-sm">Escanee la pulsera o ingrese el código</p>
            </div>

            <!-- Tabs -->
            <div class="flex justify-center mb-6">
                <button type="button" id="btnScanner" onclick="showScanner()"
                        class="bg-primary text-white font-semibold py-3 px-6 rounded-l-lg flex items-center">
                    <i class="fas fa-camera mr-2"></i>Escanear
                </button>
                <button type="button" id="btnManual" onclick="showManual()"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-r-lg flex items-center">
                    <i class="fas fa-keyboard mr-2"></i>Manual
                </button>
            </div>

            <!-- Scanner -->
            <div id="scannerSection" class="mb-6">
                <div id="qr-reader" class="mb-3"></div>
                <p class="text-center text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>Apunta la cámara al código QR de la pulsera
                </p>
            </div>

            <!-- Manual Input -->
            <div id="manualSection" class="hidden mb-6">
                <form method="POST" id="manualForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Código</label>
                        <input type="text" name="code" id="codeInput" autofocus
                               placeholder="AQP-XXXXXXXX-XXXXXX o TKT-XXXXXXXX-XXXXXXXX"
                               class="w-full rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-3 px-4 font-mono uppercase">
                    </div>
                    <button type="submit"
                            class="w-full bg-primary hover:opacity-90 text-white font-semibold py-3 px-6 rounded-lg">
                        <i class="fas fa-search mr-2"></i>Validar
                    </button>
                </form>
            </div>

            <!-- Resultado -->
            <?php if (isset($validationResult)): ?>
            <div class="mt-4">
                <?php if ($validationResult['valid']): ?>
                <div class="bg-green-50 border-2 border-green-500 rounded-xl p-5">
                    <div class="flex items-center mb-3">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-check text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-green-800">¡Acceso Autorizado!</h3>
                            <p class="text-green-600 text-sm"><?php echo htmlspecialchars($validationResult['message']); ?></p>
                        </div>
                    </div>
                    <?php if (!empty($validationResult['record'])): ?>
                    <div class="bg-white rounded-lg p-3 text-sm space-y-1">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Pulsera #:</span>
                            <span class="font-bold"><?php echo (int)$validationResult['record']['series_number']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Válida:</span>
                            <span class="font-semibold"><?php echo date('d/m/Y', strtotime($validationResult['record']['valid_date'])); ?></span>
                        </div>
                    </div>
                    <?php elseif (!empty($validationResult['ticket'])): ?>
                    <div class="bg-white rounded-lg p-3 text-sm space-y-1">
                        <?php if (!empty($validationResult['ticket']['visitor_name'])): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Visitante:</span>
                            <span class="font-semibold"><?php echo htmlspecialchars($validationResult['ticket']['visitor_name']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Boletos:</span>
                            <span class="font-bold"><?php echo (int)$validationResult['ticket']['ticket_count']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Fecha:</span>
                            <span class="font-semibold"><?php echo date('d/m/Y', strtotime($validationResult['ticket']['visit_date'])); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="bg-red-50 border-2 border-red-500 rounded-xl p-5">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-times text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-red-800">Acceso Denegado</h3>
                            <p class="text-red-600 text-sm"><?php echo htmlspecialchars($validationResult['message']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Nuevo intento -->
            <div class="mt-6 text-center">
                <a href="<?php echo BASE_URL; ?>/aquapark/validateCode"
                   class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg text-sm">
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
        if (!scannerStarted) startScanner();
    }

    function showManual() {
        document.getElementById('scannerSection').classList.add('hidden');
        document.getElementById('manualSection').classList.remove('hidden');
        document.getElementById('btnManual').classList.remove('bg-gray-500');
        document.getElementById('btnManual').classList.add('bg-primary');
        document.getElementById('btnScanner').classList.remove('bg-primary');
        document.getElementById('btnScanner').classList.add('bg-gray-500');
        if (scannerStarted && html5QrCode) {
            html5QrCode.stop().catch(() => {});
            scannerStarted = false;
        }
        const input = document.getElementById('codeInput');
        if (input) input.focus();
    }

    function startScanner() {
        html5QrCode = new Html5Qrcode('qr-reader');
        html5QrCode.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
            function (decodedText) {
                html5QrCode.stop().then(() => {
                    scannerStarted = false;
                    submitCode(decodedText);
                }).catch(() => submitCode(decodedText));
            },
            function () { /* scan errors ignored */ }
        ).then(() => { scannerStarted = true; })
         .catch(() => { showManual(); });
    }

    function submitCode(code) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.location.href;
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'code'; inp.value = code;
        form.appendChild(inp);
        document.body.appendChild(form);
        form.submit();
    }

    // Input to uppercase
    const codeInput = document.getElementById('codeInput');
    if (codeInput) {
        codeInput.addEventListener('input', function () {
            const pos = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(pos, pos);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        <?php if (!isset($validationResult)): ?>
        startScanner();
        <?php endif; ?>
    });
    </script>
</body>
</html>
