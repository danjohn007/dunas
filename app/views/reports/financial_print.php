<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Financiero - <?php echo $dateFrom; ?> a <?php echo $dateTo; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #1e40af;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
        }
        .stat-box {
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex: 1;
            margin: 0 5px;
        }
        .stat-box .label {
            color: #666;
            font-size: 11px;
            margin-bottom: 5px;
        }
        .stat-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th {
            background-color: #f3f4f6;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background-color: #1e40af; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Imprimir / Guardar PDF
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background-color: #666; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Cerrar
        </button>
    </div>
    
    <div class="header">
        <h1>Reporte Financiero</h1>
        <p>Período: <?php echo date('d/m/Y', strtotime($dateFrom)); ?> - <?php echo date('d/m/Y', strtotime($dateTo)); ?></p>
        <p>Generado: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
    
    <div class="stats">
        <div class="stat-box">
            <div class="label">Total Ingresos</div>
            <div class="value">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Total Litros</div>
            <div class="value"><?php echo number_format($stats['total_liters']); ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Transacciones</div>
            <div class="value"><?php echo $stats['total_transactions']; ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Promedio</div>
            <div class="value">$<?php echo $stats['total_transactions'] > 0 ? number_format($stats['total_revenue'] / $stats['total_transactions'], 2) : '0.00'; ?></div>
        </div>
    </div>
    
    <h3>Ingresos por Método de Pago</h3>
    <table>
        <tr>
            <th>Método</th>
            <th>Monto</th>
        </tr>
        <tr>
            <td>Efectivo</td>
            <td>$<?php echo number_format($stats['by_method']['cash'], 2); ?></td>
        </tr>
        <tr>
            <td>Vales</td>
            <td>$<?php echo number_format($stats['by_method']['voucher'], 2); ?></td>
        </tr>
        <tr>
            <td>Transferencia</td>
            <td>$<?php echo number_format($stats['by_method']['bank_transfer'], 2); ?></td>
        </tr>
    </table>
    
    <h3>Detalle de Transacciones</h3>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Litros</th>
                <th>Método</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #666;">
                        No se encontraron transacciones en el período seleccionado
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($transactions as $trans): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($trans['transaction_date'])); ?></td>
                        <td><?php echo htmlspecialchars($trans['client_name']); ?></td>
                        <td><?php echo number_format($trans['liters_supplied']); ?> L</td>
                        <td>
                            <?php
                            $methodLabels = [
                                'cash' => 'Efectivo',
                                'voucher' => 'Vales',
                                'bank_transfer' => 'Transferencia'
                            ];
                            echo $methodLabels[$trans['payment_method']];
                            ?>
                        </td>
                        <td style="font-weight: bold;">$<?php echo number_format($trans['total_amount'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p><?php echo APP_NAME; ?> - Versión <?php echo APP_VERSION; ?></p>
        <p>© <?php echo date('Y'); ?> Todos los derechos reservados</p>
    </div>
</body>
</html>
