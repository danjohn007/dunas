<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-car text-indigo-600 mr-2"></i>
            Reporte de Accesos con Placas Verificadas
        </h1>
        <p class="text-gray-600">Veh&iacute;culos cuyas placas coincidieron correctamente</p>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/reports/plateVerification" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                <input type="date" name="date_from" value="<?php echo $dateFrom; ?>" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                <input type="date" name="date_to" value="<?php echo $dateTo; ?>" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg w-full">
                    <i class="fas fa-search mr-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
    
    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-clock text-3xl text-yellow-500 mr-4"></i>
                <div>
                    <p class="text-sm text-gray-600">En Progreso</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['by_status']['in_progress']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-3xl text-green-500 mr-4"></i>
                <div>
                    <p class="text-sm text-gray-600">Completados</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['by_status']['completed']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-red-50 border-l-4 border-red-700 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-times-circle text-3xl text-red-700 mr-4"></i>
                <div>
                    <p class="text-sm text-gray-600">Cancelados</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['by_status']['cancelled']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-indigo-50 border-l-4 border-indigo-500 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-percentage text-3xl text-indigo-500 mr-4"></i>
                <div>
                    <p class="text-sm text-gray-600">Tasa Verificaci&oacute;n</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php 
                        $rate = $stats['total_accesses'] > 0 
                            ? round(($stats['plates_matched'] / $stats['total_accesses']) * 100, 1) 
                            : 0;
                        echo $rate . '%'; 
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Botones de exportar -->
    <div class="mb-4 flex justify-end gap-2">
        <a href="<?php echo BASE_URL; ?>/reports/exportExcel/plateVerification?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
           class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
            <i class="fas fa-file-excel mr-2"></i>Exportar a Excel
        </a>
        <button onclick="printVerificationReport()" 
           class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg">
            <i class="fas fa-file-pdf mr-2"></i>Exportar a PDF
        </button>
    </div>
    
    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ticket
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha/Hora Entrada
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Placa Registrada
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Placa Detectada
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Verificaci&oacute;n
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Chofer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($accesses)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-check-circle text-4xl text-green-400 mb-2"></i>
                                <p class="text-lg">No hay accesos con placas verificadas en el per&iacute;odo seleccionado</p>
                                <p class="text-sm">Este reporte solo muestra veh&iacute;culos cuyas placas coincidieron correctamente</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($accesses as $log): ?>
                            <?php
                            $statusColors = [
                                'in_progress' => 'bg-yellow-100 text-yellow-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                            $statusLabels = [
                                'in_progress' => 'En Progreso',
                                'completed' => 'Completado',
                                'cancelled' => 'Cancelado'
                            ];
                            
                            // Determinar estado de verificación
                            // Usar detected_plate del JOIN con detected_plates
                            if (empty($log['detected_plate'])) {
                                $verificationStatus = 'not_detected';
                                $verificationLabel = 'No Detectada';
                                $verificationColor = 'bg-gray-100 text-gray-800';
                                $verificationIcon = 'fa-question-circle';
                            } elseif ($log['plate_discrepancy'] == 1) {
                                $verificationStatus = 'not_match';
                                $verificationLabel = 'No Coincide';
                                $verificationColor = 'bg-red-100 text-red-800';
                                $verificationIcon = 'fa-times-circle';
                            } else {
                                $verificationStatus = 'match';
                                $verificationLabel = 'Coincide';
                                $verificationColor = 'bg-green-100 text-green-800';
                                $verificationIcon = 'fa-check-circle';
                            }
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono font-bold text-blue-600">
                                        <?php echo htmlspecialchars($log['ticket_code']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo date('d/m/Y H:i', strtotime($log['entry_datetime'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($log['client_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono font-bold text-gray-900">
                                        <?php echo htmlspecialchars($log['plate_number']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono font-bold <?php echo $verificationStatus === 'match' ? 'text-green-600' : ($verificationStatus === 'not_match' ? 'text-red-600' : 'text-gray-500'); ?>">
                                        <?php echo htmlspecialchars($log['detected_plate'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full <?php echo $verificationColor; ?>">
                                        <i class="fas <?php echo $verificationIcon; ?> mr-1"></i>
                                        <?php echo $verificationLabel; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($log['driver_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusColors[$log['status']]; ?>">
                                        <?php echo $statusLabels[$log['status']]; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Información adicional -->
    <div class="mt-6 bg-indigo-50 border-l-4 border-indigo-400 p-4 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-indigo-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-indigo-800">Informaci&oacute;n sobre este Reporte</h3>
                <div class="mt-2 text-sm text-indigo-700">
                    <p><strong>Este reporte muestra &uacute;nicamente los veh&iacute;culos cuyas placas fueron verificadas exitosamente.</strong></p>
                    <p class="mt-2">Criterios de inclusi&oacute;n:</p>
                    <ul class="list-disc list-inside mt-1 ml-4 space-y-1">
                        <li>La c&aacute;mara detect&oacute; correctamente la placa del veh&iacute;culo</li>
                        <li>La placa detectada coincide exactamente con la placa registrada en el sistema</li>
                        <li>El acceso fue validado autom&aacute;ticamente sin discrepancias</li>
                    </ul>
                    <p class="mt-2"><em>Para ver accesos con placas que NO coincidieron, consulte el "Reporte de Discrepancias".</em></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function printVerificationReport() {
    // Crear contenido HTML para el PDF
    const content = document.createElement('div');
    content.style.padding = '20px';
    content.style.fontFamily = 'Arial, sans-serif';
    
    // Título
    const title = document.createElement('h1');
    title.style.textAlign = 'center';
    title.style.marginBottom = '20px';
    title.style.fontSize = '20px';
    title.textContent = 'Reporte de Accesos con Placas Verificadas';
    content.appendChild(title);
    
    // Fecha del reporte
    const dateInfo = document.createElement('p');
    dateInfo.style.textAlign = 'center';
    dateInfo.style.marginBottom = '20px';
    dateInfo.style.fontSize = '12px';
    dateInfo.textContent = 'Periodo: <?php echo date("d/m/Y", strtotime($dateFrom)); ?> - <?php echo date("d/m/Y", strtotime($dateTo)); ?>';
    content.appendChild(dateInfo);
    
    // Crear tabla
    const table = document.createElement('table');
    table.style.width = '100%';
    table.style.borderCollapse = 'collapse';
    table.style.fontSize = '10px';
    
    // Encabezados
    const thead = document.createElement('thead');
    thead.innerHTML = `
        <tr style="background-color: #f3f4f6;">
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Ticket</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Fecha/Hora</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Cliente</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Placa Registrada</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Placa Detectada</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Verificacion</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Chofer</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Estado</th>
        </tr>
    `;
    table.appendChild(thead);
    
    // Cuerpo de la tabla
    const tbody = document.createElement('tbody');
    <?php if (!empty($accesses)): ?>
        <?php foreach ($accesses as $log): ?>
            <?php
            $statusLabels = [
                'in_progress' => 'En Progreso',
                'completed' => 'Completado',
                'cancelled' => 'Cancelado'
            ];
            $verificationLabel = empty($log['detected_plate']) ? 'No Detectada' : 'Coincide';
            ?>
            const row<?php echo $log['id']; ?> = document.createElement('tr');
            row<?php echo $log['id']; ?>.innerHTML = `
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($log['ticket_code']); ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo date('d/m/Y H:i', strtotime($log['entry_datetime'])); ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($log['client_name']); ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($log['plate_number']); ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($log['detected_plate'] ?? 'N/A'); ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $verificationLabel; ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($log['driver_name']); ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $statusLabels[$log['status']]; ?></td>
            `;
            tbody.appendChild(row<?php echo $log['id']; ?>);
        <?php endforeach; ?>
    <?php endif; ?>
    table.appendChild(tbody);
    content.appendChild(table);
    
    // Opciones de html2pdf
    const options = {
        margin: 10,
        filename: 'reporte_placas_verificadas_<?php echo date("Y-m-d"); ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
    };
    
    // Generar PDF
    html2pdf().set(options).from(content).save();
}
</script>
