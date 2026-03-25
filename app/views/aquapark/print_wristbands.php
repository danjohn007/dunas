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

        /* -------- Print page -------- */
        .print-area {
            margin-top: 60px;
            padding: 20px;
        }

        /* Each page holds up to 11 wristbands stacked vertically.
           Letter = 8.5 × 11 in.  Wristband strip ≈ 0.9 in tall × 8.5 in wide */
        .page {
            width: 8.5in;
            min-height: 10in;
            background: white;
            margin: 0 auto 20px auto;
            padding: 0.25in 0.1in;
            display: flex;
            flex-direction: column;
            gap: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        /* One wristband row */
        .wristband-row {
            display: flex;
            align-items: center;
            height: 0.88in;
            border-bottom: 1px dashed #aaa;
            padding: 2px 4px;
            gap: 6px;
            overflow: hidden;
        }
        .wristband-row:last-child {
            border-bottom: none;
        }

        /* QR code cell */
        .wristband-qr {
            width: 0.75in;
            height: 0.75in;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .wristband-qr canvas,
        .wristband-qr img {
            width: 0.75in !important;
            height: 0.75in !important;
        }

        /* Info cell */
        .wristband-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
        }
        .wristband-number {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #111;
        }
        .wristband-date {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }
        .wristband-code {
            font-size: 7px;
            color: #888;
            margin-top: 1px;
            word-break: break-all;
        }

        /* -------- Print styles -------- */
        @media print {
            .toolbar { display: none !important; }
            .print-area { margin-top: 0; padding: 0; }
            .page {
                width: 100%;
                min-height: 0;
                margin: 0;
                padding: 0.2in 0.05in;
                box-shadow: none;
                page-break-after: always;
            }
            .page:last-child { page-break-after: auto; }
            @page {
                size: letter portrait;
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
        $perPage  = 11;
        $pages    = array_chunk($codes, $perPage);
        $baseUrl  = BASE_URL;
        ?>

        <?php foreach ($pages as $pageIndex => $pageRows): ?>
        <div class="page">
            <?php foreach ($pageRows as $c): ?>
            <div class="wristband-row">
                <div class="wristband-qr" id="qr-<?php echo (int)$c['id']; ?>"></div>
                <div class="wristband-info">
                    <div class="wristband-number"><?php echo (int)$c['series_number']; ?></div>
                    <div class="wristband-date"><?php echo date('d/m/Y', strtotime($c['valid_date'])); ?></div>
                    <div class="wristband-code"><?php echo htmlspecialchars($c['code']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
    // Build QR codes for each wristband using the validation code
    (function () {
        var codes = <?php echo json_encode(array_map(fn($c) => ['id' => $c['id'], 'code' => $c['code']], $codes)); ?>;
        var px = Math.round(0.75 * 96); // 0.75 in at 96dpi

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
