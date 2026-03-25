<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleto de Visita - Parque Acuático</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <?php
    $primaryColor   = $systemSettings['theme_primary_color']   ?? '#2563eb';
    $secondaryColor = $systemSettings['theme_secondary_color'] ?? '#1e40af';
    ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        .ticket-header {
            background: linear-gradient(135deg, <?php echo $primaryColor; ?>, <?php echo $secondaryColor; ?>);
        }

        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; background: white; }
            .ticket-wrapper {
                max-width: 100%;
                box-shadow: none !important;
                border: 1px solid #ddd;
                page-break-inside: avoid;
            }
            .ticket-page-break { page-break-after: always; }
            @page { size: letter portrait; margin: 1cm; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Toolbar (no print) -->
    <div class="no-print max-w-xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="<?php echo BASE_URL; ?>/aquapark/visitors"
           class="inline-flex items-center text-gray-600 hover:text-gray-900 text-sm">
            <i class="fas fa-arrow-left mr-2"></i>Volver a Visitantes
        </a>
        <button onclick="window.print()"
                class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-sm">
            <i class="fas fa-print mr-2"></i>Imprimir
        </button>
    </div>

    <?php
    // Determine which codes to show: individual items if they exist, else the parent ticket code
    $printItems = !empty($items) ? $items : [['code' => $ticket['code'], 'item_number' => null]];
    $totalItems  = count($printItems);
    ?>

    <?php foreach ($printItems as $idx => $item): ?>
    <?php $isLast = ($idx === $totalItems - 1); ?>
    <!-- Ticket <?php echo $idx + 1; ?> -->
    <div class="max-w-xl mx-auto px-4 pb-8 <?php echo !$isLast ? 'ticket-page-break' : ''; ?>">
        <div class="ticket-wrapper bg-white rounded-2xl shadow-xl overflow-hidden">

            <!-- Header -->
            <div class="ticket-header text-white p-5">
                <h1 class="text-xl font-bold">
                    <i class="fas fa-water mr-2"></i>Parque Acuático
                </h1>
                <p class="text-white/80 text-sm mt-1">Boleto de Visita</p>
            </div>

            <!-- Body -->
            <div class="p-5">
                <div class="flex flex-col sm:flex-row gap-5">

                    <!-- QR Code -->
                    <div class="flex flex-col items-center">
                        <div id="qrcode-<?php echo $idx; ?>" class="p-2 border border-gray-200 rounded-lg bg-white"></div>
                        <p class="text-xs text-gray-500 mt-2 font-mono text-center" style="max-width:160px;word-break:break-all;">
                            <?php echo htmlspecialchars($item['code']); ?>
                        </p>
                        <?php if ($item['item_number'] !== null): ?>
                        <p class="text-xs text-blue-600 font-semibold mt-1">
                            Boleto <?php echo (int)$item['item_number']; ?> de <?php echo $totalItems; ?>
                        </p>
                        <?php endif; ?>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 space-y-3">
                        <?php if (!empty($ticket['visitor_name'])): ?>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Visitante</p>
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($ticket['visitor_name']); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($ticket['phone'])): ?>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Teléfono</p>
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($ticket['phone']); ?></p>
                        </div>
                        <?php endif; ?>

                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Fecha de Visita</p>
                            <p class="font-semibold text-gray-900"><?php echo date('d/m/Y', strtotime($ticket['visit_date'])); ?></p>
                        </div>

                        <?php if ($ticket['total_amount'] !== null): ?>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Total</p>
                            <p class="font-semibold text-gray-900">$<?php echo number_format($ticket['total_amount'], 2); ?></p>
                        </div>
                        <?php endif; ?>

                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Registrado</p>
                            <p class="text-sm text-gray-700"><?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 border-t px-5 py-3">
                <p class="text-center text-xs text-gray-500">
                    Presente este boleto al ingresar al parque •
                    <?php echo htmlspecialchars($systemSettings['site_name'] ?? 'Parque Acuático'); ?>
                </p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var items = <?php echo json_encode(array_values(array_map(function($item) {
            return $item['code'];
        }, $printItems))); ?>;

        items.forEach(function(code, idx) {
            try {
                new QRCode(document.getElementById('qrcode-' + idx), {
                    text: code,
                    width: 160,
                    height: 160,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            } catch (e) {
                var el = document.getElementById('qrcode-' + idx);
                if (el) el.innerHTML = '<p class="text-red-500 text-xs">Error al generar QR</p>';
            }
        });

        // Delay allows all QR codes to finish rendering before the print dialog opens
        if (window.location.search.indexOf('print=1') !== -1) {
            setTimeout(function () { window.print(); }, 800);
        }
    });
    </script>
</body>
</html>
