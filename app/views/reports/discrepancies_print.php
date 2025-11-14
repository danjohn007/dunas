<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #EAB308;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #1F2937;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .header .subtitle {
            color: #6B7280;
            font-size: 14px;
        }
        
        .header .date-range {
            color: #4B5563;
            font-size: 12px;
            margin-top: 10px;
        }
        
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
            gap: 15px;
        }
        
        .stat-card {
            flex: 1;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-card.red {
            background-color: #FEE2E2;
            border-left: 4px solid #EF4444;
        }
        
        .stat-card.yellow {
            background-color: #FEF3C7;
            border-left: 4px solid #EAB308;
        }
        
        .stat-card.green {
            background-color: #D1FAE5;
            border-left: 4px solid #10B981;
        }
        
        .stat-card.gray {
            background-color: #F3F4F6;
            border-left: 4px solid #6B7280;
        }
        
        .stat-card .label {
            font-size: 11px;
            color: #6B7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .stat-card .value {
            font-size: 28px;
            font-weight: bold;
            color: #1F2937;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        th {
            background-color: #F3F4F6;
            padding: 12px 8px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #6B7280;
            text-transform: uppercase;
            border-bottom: 2px solid #E5E7EB;
        }
        
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 12px;
        }
        
        tr:hover {
            background-color: #F9FAFB;
        }
        
        .ticket-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #2563EB;
        }
        
        .plate {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 13px;
        }
        
        .plate.registered {
            color: #1F2937;
        }
        
        .plate.detected {
            color: #DC2626;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-badge.in-progress {
            background-color: #FEF3C7;
            color: #92400E;
        }
        
        .status-badge.completed {
            background-color: #D1FAE5;
            color: #065F46;
        }
        
        .status-badge.cancelled {
            background-color: #FEE2E2;
            color: #991B1B;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #E5E7EB;
            text-align: center;
            font-size: 11px;
            color: #6B7280;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6B7280;
        }
        
        @media print {
            body {
                padding: 10px;
            }
            
            .header {
                margin-bottom: 20px;
                padding-bottom: 15px;
            }
            
            .stats {
                margin-bottom: 20px;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            thead {
                display: table-header-group;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚨 <?php echo $title; ?></h1>
        <div class="subtitle">Control de Acceso DUNAS</div>
        <div class="date-range">
            Período: <?php echo date('d/m/Y', strtotime($dateFrom)); ?> - <?php echo date('d/m/Y', strtotime($dateTo)); ?>
        </div>
        <div class="date-range">
            Generado: <?php echo date('d/m/Y H:i:s'); ?>
        </div>
    </div>
    
    <!-- Estadísticas -->
    <div class="stats">
        <div class="stat-card red">
            <div class="label">Total Discrepancias</div>
            <div class="value"><?php echo $stats['total_discrepancies']; ?></div>
        </div>
        
        <div class="stat-card yellow">
            <div class="label">En Progreso</div>
            <div class="value"><?php echo $stats['by_status']['in_progress']; ?></div>
        </div>
        
        <div class="stat-card green">
            <div class="label">Completados</div>
            <div class="value"><?php echo $stats['by_status']['completed']; ?></div>
        </div>
        
        <div class="stat-card gray">
            <div class="label">Cancelados</div>
            <div class="value"><?php echo $stats['by_status']['cancelled']; ?></div>
        </div>
    </div>
    
    <!-- Tabla -->
    <?php if (empty($discrepancies)): ?>
        <div class="no-data">
            <p style="font-size: 18px; margin-bottom: 10px;">✅ No hay discrepancias de placas</p>
            <p>No se encontraron registros en el período seleccionado</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Fecha/Hora</th>
                    <th>Cliente</th>
                    <th>Placa Registrada</th>
                    <th>Placa Detectada</th>
                    <th>Chofer</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $statusLabels = [
                    'in_progress' => 'En Progreso',
                    'completed' => 'Completado',
                    'cancelled' => 'Cancelado'
                ];
                
                foreach ($discrepancies as $log): 
                ?>
                    <tr>
                        <td class="ticket-code"><?php echo htmlspecialchars($log['ticket_code']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($log['entry_datetime'])); ?></td>
                        <td><?php echo htmlspecialchars($log['client_name']); ?></td>
                        <td>
                            <span class="plate registered">
                                <?php echo htmlspecialchars($log['plate_number']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="plate detected">
                                <?php echo htmlspecialchars($log['license_plate_reading'] ?? 'N/A'); ?> ⚠️
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($log['driver_name']); ?></td>
                        <td>
                            <span class="status-badge <?php echo str_replace('_', '-', $log['status']); ?>">
                                <?php echo $statusLabels[$log['status']]; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <div class="footer">
        <p><strong>Sistema de Control de Acceso DUNAS</strong></p>
        <p>Este reporte muestra accesos donde la placa detectada no coincide con la registrada</p>
        <p>Documento generado automáticamente - <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        window.onload = function() {
            const element = document.body;
            const opt = {
                margin: 10,
                filename: 'Discrepancias_Placas_<?php echo date("Y-m-d_His"); ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, logging: false },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
            };
            
            html2pdf().set(opt).from(element).save().then(function() {
                // Cerrar ventana después de descargar
                setTimeout(function() {
                    window.close();
                }, 1000);
            });
        };
    </script>
</body>
</html>">
