<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
            Reporte de Discrepancias de Placas
        </h1>
        <p class="text-gray-600">Accesos donde la placa detectada no coincide con la registrada</p>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/reports/plateDiscrepancies" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-3xl text-red-500 mr-4"></i>
                <div>
                    <p class="text-sm text-gray-600">Total Discrepancias</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_discrepancies']; ?></p>
                </div>
            </div>
        </div>
        
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
        
        <div class="bg-gray-50 border-l-4 border-gray-500 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-ban text-3xl text-gray-500 mr-4"></i>
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
    <div class="mb-4 flex justify-end gap-3">
        <a href="<?php echo BASE_URL; ?>/reports/exportExcel/discrepancies?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
           class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
            <i class="fas fa-file-excel mr-2"></i>Exportar a Excel
        </a>
        <a href="<?php echo BASE_URL; ?>/reports/exportPdf/discrepancies?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
           target="_blank"
           class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg">
            <i class="fas fa-file-pdf mr-2"></i>Imprimir PDF
        </a>
    </div>
    
    <!-- Tabla de Discrepancias -->
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
                            Chofer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($discrepancies)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-check-circle text-4xl text-green-500 mb-2"></i>
                                <p class="text-lg">No hay discrepancias de placas en el período seleccionado</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($discrepancies as $log): ?>
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
                                    <span class="font-mono font-bold text-red-600">
                                        <?php echo htmlspecialchars($log['license_plate_reading'] ?? 'N/A'); ?>
                                    </span>
                                    <i class="fas fa-exclamation-triangle text-red-500 ml-2" title="Placa no coincide"></i>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($log['driver_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusColors[$log['status']]; ?>">
                                        <?php echo $statusLabels[$log['status']]; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="<?php echo BASE_URL; ?>/access/detail/<?php echo $log['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye mr-1"></i>Ver Detalle
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Información adicional -->
    <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Información sobre Discrepancias</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>Este reporte muestra todos los accesos donde la placa detectada por la cámara no coincide con la placa registrada en el sistema.</p>
                    <p class="mt-1">Esto puede indicar:</p>
                    <ul class="list-disc list-inside mt-1 ml-4">
                        <li>Error en el registro de la placa de la unidad</li>
                        <li>Uso de una unidad diferente a la registrada</li>
                        <li>Error en la detección de la cámara LPR</li>
                        <li>Placa sucia o ilegible</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
