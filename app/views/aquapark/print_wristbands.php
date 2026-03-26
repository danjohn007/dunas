<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Pulseras - Parque Acuático</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: monospace;
            background: #f5f5f5;
        }

        /* -------- Toolbar (no-print) -------- */
        .toolbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: #1e40af;
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 100;
        }
        .toolbar h1 { font-size: 18px; font-weight: bold; }
        .toolbar .info { font-size: 13px; opacity: 0.85; }
        .btn-print {
            background: white;
            color: #1e40af;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-back {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 1px solid rgba(255,255,255,0.4);
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            margin-right: 10px;
        }

        /* -------- Print page (landscape) -------- */
        .print-area {
            margin-top: 60px;
            padding: 20px;
        }

        /*
         * Letter landscape = 11 × 8.5 in.
         * 11 wristband columns per page (≈ 1 in each).
         * Content inside each column is rotated 90° CCW so the
         * strip reads naturally left-to-right when worn on a wrist.
         */
        .page {
            width: 11in;
            height: 8.5in;
            background: white;
            margin: 0 auto 20px auto;
            padding: 0.15in 0.05in;
            display: flex;
            flex-direction: row;
            gap: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        /* One wristband column */
        .wristband-col {
            flex: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;   /* QR at bottom */
            border-right: 1px dashed #aaa;
            padding: 4px 2px;
            overflow: hidden;
        }
        .wristband-col:last-child {
            border-right: none;
        }

        /*
         * Text block — rotated 90° CCW (reading from bottom to top).
         * writing-mode + transform combo works in Chrome/Edge print.
         */
        .wristband-info {
            flex: 1;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            display: flex;
            flex-direction: row;   /* logical "row" = physical column after rotation */
            align-items: center;
            justify-content: flex-end;
            gap: 3px;
            overflow: hidden;
            width: 100%;
        }
        .wristband-number {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #111;
            white-space: nowrap;
        }
        .wristband-date {
            font-size: 9px;
            color: #555;
            white-space: nowrap;
        }
        .wristband-code {
            font-size: 6px;
            color: #888;
            word-break: break-all;
        }
        .wristband-price {
            font-size: 11px;
            font-weight: bold;
            color: #1e40af;
            white-space: nowrap;
        }
        .wristband-type-label {
            font-size: 7px;
            color: #666;
            white-space: nowrap;
        }

        /* Bottom stub: QR + cost info grouped together */
        .wristband-stub {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 4px;
            gap: 2px;
        }
        .wristband-stub-info {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 3px;
        }

        /* QR code cell */
        .wristband-qr {
            width: 0.78in;
            height: 0.78in;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .wristband-qr canvas,
        .wristband-qr img {
            width: 0.78in !important;
            height: 0.78in !important;
        }

        /* -------- Print styles -------- */
        @media print {
            .toolbar { display: none !important; }
            .print-area { margin-top: 0; padding: 0; }
            .page {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0.15in 0.05in;
                box-shadow: none;
                page-break-after: always;
            }
            .page:last-child { page-break-after: auto; }
            @page {
                size: letter landscape;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar (hidden on print) -->
    <div class="toolbar">
        <div>
            <h1>Imprimir Pulseras - Parque Acuático</h1>
            <div class="info">
                Serie <?php echo (int)$start; ?> – <?php echo (int)$end; ?> |
                Fecha: <?php echo date('d/m/Y', strtotime($date)); ?> |
                Total: <?php echo count($codes); ?> pulseras
            </div>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/aquapark/codes" class="btn-back">← Volver</a>
            <button class="btn-print" onclick="window.print()">🖨️ Imprimir</button>
        </div>
    </div>

    <!-- Print area -->
    <div class="print-area">
        <?php
        // Label map for ticket types
        $typeLabels = [
            'normal'                 => 'Normal',
            'nino'                   => 'Niño',
            'adulto_mayor'           => 'Adulto Mayor',
            'capacidades_diferentes' => 'Cap. Dif.',
        ];

        $perPage  = 11;   // 11 columns per landscape page
        $pages    = array_chunk($codes, $perPage);
        $baseUrl  = BASE_URL;
        ?>

        <?php foreach ($pages as $pageIndex => $pageRows): ?>
        <div class="page">
            <?php foreach ($pageRows as $c):
                $type      = $c['ticket_type'] ?? 'normal';
                $price     = $ticketPrices[$type] ?? 0;
                $typeLabel = $typeLabels[$type] ?? $type;
            ?>
            <div class="wristband-col">
                <!-- Info block (rotated): series number, date, code -->
                <div class="wristband-info">
                    <div class="wristband-number"><?php echo (int)$c['series_number']; ?></div>
                    <div class="wristband-date"><?php echo date('d/m/Y', strtotime($c['valid_date'])); ?></div>
                    <div class="wristband-code"><?php echo htmlspecialchars($c['code']); ?></div>
                </div>
                <!-- Bottom stub: price + type label + QR code, all together -->
                <div class="wristband-stub">
                    <?php if ($price > 0 || $typeLabel): ?>
                    <div class="wristband-stub-info">
                        <?php if ($price > 0): ?>
                        <span class="wristband-price">$<?php echo number_format($price, 2); ?></span>
                        <?php endif; ?>
                        <span class="wristband-type-label"><?php echo htmlspecialchars($typeLabel); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="wristband-qr" id="qr-<?php echo (int)$c['id']; ?>"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
    // Build QR codes for each wristband
    (function () {
        var codes = <?php echo json_encode(array_map(fn($c) => ['id' => $c['id'], 'code' => $c['code']], $codes)); ?>;
        var px = Math.round(0.78 * 96); // 0.78 in at 96dpi

        codes.forEach(function (c) {
            var el = document.getElementById('qr-' + c.id);
            if (!el) return;
            try {
                new QRCode(el, {
                    text: c.code,
                    width: px,
                    height: px,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            } catch (e) {
                el.innerHTML = '<span style="font-size:8px;color:#c00">Error QR</span>';
            }
        });

        // Auto-print if requested
        if (window.location.search.indexOf('autoprint=1') !== -1) {
            setTimeout(function () { window.print(); }, 800);
        }
    })();
    </script>
</body>
</html>
