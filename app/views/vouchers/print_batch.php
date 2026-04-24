<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Vales</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #e5e7eb;
            font-family: Arial, Helvetica, sans-serif;
        }

        .no-print-bar {
            background: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,.15);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .no-print-bar h1 { font-size: 20px; font-weight: bold; color: #111; flex: 1; }
        .no-print-bar p  { font-size: 13px; color: #555; }

        .btn-print {
            padding: 8px 18px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
        }
        .btn-print:hover { background: #1d4ed8; }

        .btn-close {
            padding: 8px 18px;
            background: #4b5563;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
        }
        .btn-close:hover { background: #374151; }

        /* ── Vouchers grid ── */
        .vouchers-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6cm;
            padding: 1cm;
            justify-content: center;
        }

        /* ── Single voucher card: 12.5 cm wide × 9.2 cm tall (landscape) ── */
        .voucher-card {
            width: 12.5cm;
            height: 9.2cm;
            background-color: #d5e8d3;
            border: 2px solid #333;
            border-radius: 4px;
            padding: 0.45cm 0.5cm 0.35cm 0.5cm;
            display: flex;
            flex-direction: column;
            page-break-inside: avoid;
        }

        /* ── Body: left fields + right QR/folio ── */
        .voucher-body {
            display: flex;
            flex: 1;
            gap: 0.35cm;
            overflow: hidden;
        }

        /* ── Left column: form fields ── */
        .voucher-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
        }

        .field-row {
            display: flex;
            align-items: flex-end;
            line-height: 1;
        }

        .field-label {
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            white-space: nowrap;
            margin-right: 3px;
            color: #111;
            padding-bottom: 1px;
        }

        .field-line {
            flex: 1;
            border-bottom: 1.5px solid #333;
            font-size: 9.5px;
            font-weight: 600;
            color: #111;
            padding-bottom: 1px;
            min-width: 0;
        }

        /* ── Right column: QR box + folio ── */
        .voucher-right {
            width: 3.8cm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            flex-shrink: 0;
        }

        .qr-box {
            width: 3.4cm;
            height: 3.15cm;
            background: #fff;
            border: 1.5px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.18cm;
            flex-shrink: 0;
        }

        .folio-section {
            text-align: center;
            width: 100%;
        }

        .folio-title {
            font-size: 12px;
            font-weight: 900;
            line-height: 1.25;
            color: #111;
        }

        .serie-title {
            font-size: 10px;
            font-weight: 700;
            line-height: 1.25;
            color: #111;
            margin-bottom: 0.12cm;
        }

        .folio-digits {
            display: flex;
            justify-content: center;
            gap: 3px;
        }

        .folio-digit-box {
            width: 0.72cm;
            height: 0.65cm;
            border: 1.5px solid #333;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            color: #111;
        }

        /* ── Footer ── */
        .voucher-footer {
            text-align: center;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            color: #111;
            margin-top: 0.22cm;
            padding-top: 0.12cm;
            border-top: 2px solid #333;
            letter-spacing: 0.5px;
            flex-shrink: 0;
        }

        /* ── Print overrides ── */
        @media print {
            .no-print-bar { display: none !important; }
            .page-break   { page-break-after: always; }
            body          { margin: 0; background: #fff; }
            .vouchers-container { padding: 0.3cm; gap: 0.4cm; }
        }

        @page {
            size: auto;
            margin: 0.5cm;
        }
    </style>
</head>
<body>

    <!-- Print Controls (hidden when printing) -->
    <div class="no-print-bar">
        <h1>Vista Previa de Impresión</h1>
        <p>Total de vales: <?php echo count($vouchers); ?> &nbsp;|&nbsp; Formato: 12.5 × 9.2 cm (horizontal)</p>
        <button class="btn-print" onclick="window.print()">&#128438; Imprimir</button>
        <a class="btn-close" href="<?php echo BASE_URL; ?>/vouchers">&#10005; Cerrar</a>
    </div>

    <!-- Vouchers -->
    <div class="vouchers-container">
        <?php foreach ($vouchers as $index => $voucher):
            $folioStr  = str_pad((string)$voucher['folio'], 4, '0', STR_PAD_LEFT);
            $digits    = str_split($folioStr);
            $serieUpper = strtoupper($voucher['serie']);
            /* QR encodes the short SERIE-FOLIO format, e.g. S-1000 */
            $qrContent = $serieUpper . '-' . $voucher['folio'];
        ?>
        <div class="voucher-card">

            <!-- Body -->
            <div class="voucher-body">

                <!-- Left: form fields -->
                <div class="voucher-left">
                    <div class="field-row">
                        <span class="field-label">EMPRESA:</span>
                        <span class="field-line"></span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">OPERADOR:</span>
                        <span class="field-line"></span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">PLACAS:</span>
                        <span class="field-line"></span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">CAPACIDAD:</span>
                        <span class="field-line"><?php echo number_format($voucher['capacity']); ?> L</span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">TELEFONO:</span>
                        <span class="field-line"></span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">FECHA:</span>
                        <span class="field-line"></span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">HORA DE CARGA:</span>
                        <span class="field-line"></span>
                    </div>
                </div>

                <!-- Right: QR box + folio section -->
                <div class="voucher-right">
                    <div class="qr-box">
                        <div id="qrcode-<?php echo $voucher['id']; ?>"></div>
                    </div>
                    <div class="folio-section">
                        <div class="folio-title">FOLIO</div>
                        <div class="serie-title">SERIE &ldquo;<?php echo htmlspecialchars($serieUpper); ?>&rdquo;</div>
                        <div class="folio-digits">
                            <?php foreach ($digits as $digit): ?>
                            <div class="folio-digit-box"><?php echo htmlspecialchars($digit); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            </div><!-- /voucher-body -->

            <!-- Footer -->
            <div class="voucher-footer">AGUA DE SERVICIOS</div>

        </div><!-- /voucher-card -->

        <?php if (($index + 1) % 4 === 0 && $index < count($vouchers) - 1): ?>
        <div class="page-break"></div>
        <?php endif; ?>

        <?php endforeach; ?>
    </div><!-- /vouchers-container -->

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($vouchers as $voucher):
            $qrContent = strtoupper($voucher['serie']) . '-' . $voucher['folio'];
        ?>
        try {
            new QRCode(document.getElementById('qrcode-<?php echo (int)$voucher['id']; ?>'), {
                text: '<?php echo htmlspecialchars($qrContent, ENT_QUOTES); ?>',
                width: 108,
                height: 108,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        } catch (e) {
            document.getElementById('qrcode-<?php echo (int)$voucher['id']; ?>').innerHTML =
                '<p style="color:red;font-size:10px;">Error QR</p>';
        }
        <?php endforeach; ?>
    });
    </script>
</body>
</html>
