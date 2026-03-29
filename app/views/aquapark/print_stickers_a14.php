<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Etiquetas Adhesivas A14 - Parque Acuático</title>
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
        .toolbar .badge {
            font-size: 11px;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 4px;
            padding: 2px 7px;
            margin-left: 8px;
        }
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

        /* -------- Print page (letter portrait) -------- */
        .print-area {
            margin-top: 60px;
            padding: 20px;
        }

        /*
         * Tuk-Stik A14: 19 mm tall × 50 mm wide per sticker (landscape label).
         * Letter portrait: 8.5 in × 11 in (215.9 mm × 279.4 mm).
         * Layout: 4 columns × 6 rows = 24 stickers per sheet.
         * Column width uses 25% (1/4) of the flex-container content width so
         * that exactly 4 columns are guaranteed regardless of DPI rounding.
         * Row height = 19 mm (sticker content) + configurable row gap.
         */
        .page {
            width: 8.5in;
            min-height: 11in;
            background: white;
            margin: 0 auto 20px auto;
            padding: 8mm;
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
            row-gap: 0;
            column-gap: <?php echo number_format($colGap ?? 0, 2, '.', ''); ?>mm;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        /*
         * A14 sticker cell: exactly 1/4 of the container width (4 columns).
         * Height = 19 mm sticker content + row gap (configurable, default 1 mm).
         * Width accounts for 3 column gaps distributed across 4 columns.
         */
        .sticker {
            flex: 0 0 calc(25% - <?php echo number_format(3 / 4 * ($colGap ?? 0), 4, '.', ''); ?>mm);
            width: calc(25% - <?php echo number_format(3 / 4 * ($colGap ?? 0), 4, '.', ''); ?>mm);
            height: <?php echo number_format(19 + ($rowGap ?? 1), 2, '.', ''); ?>mm;
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            overflow: hidden;
            /* subtle outline to help align on screen; hidden on print */
            outline: 0.3px dashed #ccc;
            padding: 1mm 1.5mm 0 1.5mm;
        }

        /* QR code area inside the sticker */
        .sticker-qr {
            flex-shrink: 0;
            width: 16mm;
            height: 16mm;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 2mm;
        }
        .sticker-qr canvas,
        .sticker-qr img {
            width: 16mm !important;
            height: 16mm !important;
        }

        /* Text area beside the QR code */
        .sticker-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 0.5mm;
            overflow: hidden;
            padding-top: 0.5mm;
        }
        .sticker-number {
            font-size: 9pt;
            font-weight: bold;
            color: #111;
            white-space: nowrap;
            line-height: 1;
        }
        .sticker-date {
            font-size: 7pt;
            color: #555;
            white-space: nowrap;
            line-height: 1;
        }
        .sticker-type {
            font-size: 6pt;
            color: #777;
            white-space: nowrap;
            line-height: 1;
        }
        .sticker-price {
            font-size: 7pt;
            font-weight: bold;
            color: #1e40af;
            white-space: nowrap;
            line-height: 1;
        }

        /* -------- Print styles -------- */
        @media print {
            .toolbar { display: none !important; }
            .print-area { margin-top: 0; padding: 0; }
            .page {
                width: 100%;
                min-height: 0;
                height: 11in;
                margin: 0;
                padding: 8mm;
                box-shadow: none;
                page-break-after: always;
            }
            .page:last-child { page-break-after: auto; }
            .sticker { outline: none; }
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
            <h1>Imprimir Etiquetas Adhesivas - Parque Acuático
                <span class="badge">Tuk-Stik A14 · 19×50 mm · 4×6 por hoja</span>
            </h1>
            <div class="info">
                Serie <?php echo (int)$start; ?> – <?php echo (int)$end; ?> |
                Fecha: <?php echo date('d/m/Y', strtotime($date)); ?> |
                Total: <?php echo count($codes); ?> etiquetas
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
        $typeLabels = [
            'normal'                 => 'Normal',
            'nino'                   => 'Niño',
            'adulto_mayor'           => 'Ad. Mayor',
            'capacidades_diferentes' => 'Cap. Dif.',
        ];

        // 4 columns × 6 rows = 24 stickers per letter page
        $perPage = 24;
        $pages   = array_chunk($codes, $perPage);
        ?>

        <?php foreach ($pages as $pageRows): ?>
        <div class="page">
            <?php foreach ($pageRows as $c):
                $type      = $c['ticket_type'] ?? 'normal';
                $price     = $ticketPrices[$type] ?? 0;
                $typeLabel = $typeLabels[$type] ?? $type;
            ?>
            <div class="sticker">
                <div class="sticker-qr" id="qr-<?php echo (int)$c['id']; ?>"></div>
                <div class="sticker-info">
                    <span class="sticker-number"><?php echo (int)$c['series_number']; ?></span>
                    <span class="sticker-date"><?php echo date('d/m/Y', strtotime($c['valid_date'])); ?></span>
                    <span class="sticker-type"><?php echo htmlspecialchars($typeLabel); ?></span>
                    <?php if ($price > 0): ?>
                    <span class="sticker-price">$<?php echo number_format($price, 2); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
    (function () {
        var codes = <?php echo json_encode(array_map(fn($c) => ['id' => $c['id'], 'code' => $c['code']], $codes), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        // 16 mm at 96 dpi ≈ 60 px  (1 in = 25.4 mm, 96 px/in → 1 mm ≈ 3.78 px)
        var px = Math.round(16 * 3.7795275591);

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
                el.innerHTML = '<span style="font-size:5px;color:#c00">QR</span>';
            }
        });

        if (window.location.search.indexOf('autoprint=1') !== -1) {
            setTimeout(function () { window.print(); }, 800);
        }
    })();
    </script>
</body>
</html>
