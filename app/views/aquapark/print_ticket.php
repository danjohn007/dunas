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

        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
        }

        /* ── Screen preview ─────────────────────────────────── */
        .ticket-header {
            background: linear-gradient(135deg, <?php echo $primaryColor; ?>, <?php echo $secondaryColor; ?>);
        }

        /* ── Thermal print styles ───────────────────────────── */
        @media print {
            .no-print { display: none !important; }

            /* Thermal receipt paper: 80 mm wide, auto height */
            @page {
                size: 80mm auto;
                margin: 0;
            }

            html, body {
                margin: 0;
                padding: 0;
                background: white;
                /* Inherit the 80 mm width from @page */
                width: 80mm;
            }

            /* Reset the screen layout */
            .screen-wrapper { display: none !important; }

            /* Show the thermal receipt blocks */
            .thermal-ticket {
                display: block !important;
                width: 80mm;
                padding: 4mm 4mm 6mm;
                page-break-after: always;
                border-bottom: 1px dashed #000;
                font-size: 9pt;
                font-family: 'Courier New', monospace;
                color: #000;
            }
            .thermal-ticket:last-child {
                page-break-after: auto;
                border-bottom: none;
            }

            .thermal-header {
                text-align: center;
                border-bottom: 1px dashed #000;
                padding-bottom: 3mm;
                margin-bottom: 3mm;
            }
            .thermal-header h1 { font-size: 12pt; font-weight: bold; margin: 0; }
            .thermal-header p  { font-size: 8pt; margin: 1mm 0 0; }

            .thermal-qr {
                text-align: center;
                margin: 3mm 0;
            }

            .thermal-row {
                display: flex;
                justify-content: space-between;
                margin: 1.5mm 0;
                font-size: 8pt;
            }
            .thermal-row .label { color: #555; }
            .thermal-row .value { font-weight: bold; text-align: right; }

            .thermal-total {
                border-top: 1px dashed #000;
                margin-top: 3mm;
                padding-top: 3mm;
                display: flex;
                justify-content: space-between;
                font-size: 11pt;
                font-weight: bold;
            }

            .thermal-code {
                text-align: center;
                font-size: 7pt;
                word-break: break-all;
                margin: 2mm 0;
                color: #333;
            }

            .thermal-footer {
                text-align: center;
                font-size: 7pt;
                color: #555;
                border-top: 1px dashed #000;
                padding-top: 3mm;
                margin-top: 3mm;
            }

            .ticket-num {
                text-align: center;
                font-size: 8pt;
                color: #666;
                margin-top: 1mm;
            }
        }
    </style>
</head>
<body>

    <!-- ── Toolbar (screen only) ─────────────────────────── -->
    <div class="no-print max-w-xl mx-auto px-4 py-4 flex items-center justify-between screen-wrapper">
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
    $printItems = !empty($items) ? $items : [['code' => $ticket['code'], 'item_number' => null]];
    $totalItems = count($printItems);
    $unitPrice  = (!empty($ticket['ticket_count']) && (int)$ticket['ticket_count'] > 0 && $ticket['total_amount'] !== null)
        ? $ticket['total_amount'] / (int)$ticket['ticket_count']
        : $ticket['total_amount'];
    ?>

    <!-- ── Screen preview ───────────────────────────────────── -->
    <div class="screen-wrapper">
        <?php foreach ($printItems as $idx => $item): ?>
        <?php $isLast = ($idx === $totalItems - 1); ?>
        <div class="max-w-xl mx-auto px-4 pb-8 <?php echo !$isLast ? 'ticket-page-break' : ''; ?>">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

                <div class="ticket-header text-white p-5">
                    <h1 class="text-xl font-bold">
                        <i class="fas fa-water mr-2"></i>Parque Acuático
                    </h1>
                    <p class="text-white/80 text-sm mt-1">Boleto de Visita</p>
                </div>

                <div class="p-5">
                    <div class="flex flex-col sm:flex-row gap-5">
                        <div class="flex flex-col items-center">
                            <div id="qrcode-screen-<?php echo $idx; ?>" class="p-2 border border-gray-200 rounded-lg bg-white"></div>
                            <p class="text-xs text-gray-500 mt-2 font-mono text-center" style="max-width:160px;word-break:break-all;">
                                <?php echo htmlspecialchars($item['code']); ?>
                            </p>
                            <?php if ($item['item_number'] !== null): ?>
                            <p class="text-xs text-blue-600 font-semibold mt-1">
                                Boleto <?php echo (int)$item['item_number']; ?> de <?php echo $totalItems; ?>
                            </p>
                            <?php endif; ?>
                        </div>
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
                            <?php if ($unitPrice !== null): ?>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Precio Unitario</p>
                                <p class="font-semibold text-gray-900">$<?php echo number_format($unitPrice, 2); ?></p>
                            </div>
                            <?php endif; ?>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Registrado</p>
                                <p class="text-sm text-gray-700"><?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 border-t px-5 py-3">
                    <p class="text-center text-xs text-gray-500">
                        Presente este boleto al ingresar al parque •
                        <?php echo htmlspecialchars($systemSettings['site_name'] ?? 'Parque Acuático'); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Thermal receipt blocks (hidden on screen, shown on print) ── -->
    <?php foreach ($printItems as $idx => $item): ?>
    <div class="thermal-ticket" style="display:none;">
        <div class="thermal-header">
            <h1><?php echo htmlspecialchars($systemSettings['site_name'] ?? 'Parque Acuático'); ?></h1>
            <p>Boleto de Visita</p>
        </div>

        <div class="thermal-qr">
            <div id="qrcode-thermal-<?php echo $idx; ?>"></div>
        </div>

        <div class="thermal-code"><?php echo htmlspecialchars($item['code']); ?></div>

        <?php if ($item['item_number'] !== null): ?>
        <div class="ticket-num">Boleto <?php echo (int)$item['item_number']; ?> de <?php echo $totalItems; ?></div>
        <?php endif; ?>

        <?php if (!empty($ticket['visitor_name'])): ?>
        <div class="thermal-row">
            <span class="label">Visitante:</span>
            <span class="value"><?php echo htmlspecialchars($ticket['visitor_name']); ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($ticket['phone'])): ?>
        <div class="thermal-row">
            <span class="label">Teléfono:</span>
            <span class="value"><?php echo htmlspecialchars($ticket['phone']); ?></span>
        </div>
        <?php endif; ?>

        <div class="thermal-row">
            <span class="label">Fecha visita:</span>
            <span class="value"><?php echo date('d/m/Y', strtotime($ticket['visit_date'])); ?></span>
        </div>

        <div class="thermal-row">
            <span class="label">Registrado:</span>
            <span class="value"><?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></span>
        </div>

        <?php if ($unitPrice !== null): ?>
        <div class="thermal-total">
            <span>PRECIO:</span>
            <span>$<?php echo number_format($unitPrice, 2); ?></span>
        </div>
        <?php endif; ?>

        <div class="thermal-footer">
            Presente este boleto al ingresar al parque
        </div>
    </div>
    <?php endforeach; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var items = <?php echo json_encode(array_values(array_map(function($item) {
            return $item['code'];
        }, $printItems))); ?>;

        items.forEach(function(code, idx) {
            // Screen preview QR
            try {
                new QRCode(document.getElementById('qrcode-screen-' + idx), {
                    text: code,
                    width: 160,
                    height: 160,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            } catch (e) {
                var el = document.getElementById('qrcode-screen-' + idx);
                if (el) el.innerHTML = '<p style="color:red;font-size:12px">Error QR</p>';
            }

            // Thermal print QR (smaller)
            try {
                new QRCode(document.getElementById('qrcode-thermal-' + idx), {
                    text: code,
                    width: 180,
                    height: 180,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            } catch (e) {}
        });

        if (window.location.search.indexOf('print=1') !== -1) {
            setTimeout(function () { window.print(); }, 800);
        }
    });
    </script>
</body>
</html>
