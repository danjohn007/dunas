<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Reporte de Accesos</h1>
            <p class="text-gray-600">Entradas y salidas registradas en el sistema</p>
        </div>
        <div class="space-x-2">
            <a href="<?php echo BASE_URL; ?>/reports/exportExcel/access?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
               class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg inline-block">
                <i class="fas fa-file-excel mr-2"></i>Exportar Excel
            </a>
            <a href="<?php echo BASE_URL; ?>/reports/exportPdf/access?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
               class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg inline-block">
                <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
            </a>
        </div>
    </div>
    
    <!-- Filtros de Fecha -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/reports/access" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                <input type="date" name="date_from" value="<?php echo $dateFrom; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                <input type="date" name="date_to" value="<?php echo $dateTo; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="md:col-span-2 flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg mr-2">
                    <i class="fas fa-search mr-2"></i>Generar Reporte
                </button>
                <a href="<?php echo BASE_URL; ?>/reports" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
            </div>
        </form>
    </div>
    
    <!-- Estadísticas Resumidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-6 text-white">
            <p class="text-sm opacity-90 mb-1">Total Accesos</p>
            <p class="text-3xl font-bold"><?php echo $stats['total_access']; ?></p>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-md p-6 text-white">
            <p class="text-sm opacity-90 mb-1">Completados</p>
            <p class="text-3xl font-bold"><?php echo $stats['completed']; ?></p>
        </div>
        
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow-md p-6 text-white">
            <p class="text-sm opacity-90 mb-1">En Progreso</p>
            <p class="text-3xl font-bold"><?php echo $stats['in_progress']; ?></p>
        </div>
        
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-md p-6 text-white">
            <p class="text-sm opacity-90 mb-1">Total Litros</p>
            <p class="text-3xl font-bold"><?php echo number_format($stats['total_liters']); ?></p>
        </div>
    </div>
    
    <!-- Tabla de Accesos -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-list text-blue-600 mr-2"></i>Detalle de Accesos
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Entrada</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Chofer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Litros</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($accessLogs)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No se encontraron registros de acceso en el período seleccionado
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($accessLogs as $log): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo date('d/m/Y H:i', strtotime($log['entry_datetime'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($log['client_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($log['plate_number']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($log['driver_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $log['liters_supplied'] ? number_format($log['liters_supplied']) . ' L' : '-'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php
                                    $statusLabels = [
                                        'in_progress' => ['label' => 'En Progreso', 'class' => 'bg-yellow-100 text-yellow-800'],
                                        'completed' => ['label' => 'Completado', 'class' => 'bg-green-100 text-green-800'],
                                        'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-red-100 text-red-800']
                                    ];
                                    $status = $statusLabels[$log['status']];
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded <?php echo $status['class']; ?>">
                                        <?php echo $status['label']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
