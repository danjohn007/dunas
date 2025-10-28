<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Reporte Operativo</h1>
            <p class="text-gray-600">Análisis de eficiencia operacional</p>
        </div>
        <div class="space-x-2">
            <a href="<?php echo BASE_URL; ?>/reports/exportExcel/operational?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
               class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg inline-block">
                <i class="fas fa-file-excel mr-2"></i>Exportar Excel
            </a>
            <a href="<?php echo BASE_URL; ?>/reports/exportPdf/operational?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
               class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg inline-block">
                <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
            </a>
        </div>
    </div>
    
    <!-- Filtros de Fecha -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/reports/operational" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
    
    <!-- Estadísticas por Unidad -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-truck text-blue-600 mr-2"></i>Eficiencia por Unidad
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Viajes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Litros Totales</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Promedio por Viaje</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($unitStats)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No hay datos disponibles
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($unitStats as $stat): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($stat['plate_number']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $stat['trips']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo number_format($stat['liters']); ?> L
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $stat['trips'] > 0 ? number_format($stat['liters'] / $stat['trips']) : '0'; ?> L
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Estadísticas por Chofer -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-id-card text-green-600 mr-2"></i>Rendimiento por Chofer
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Chofer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Viajes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Litros Totales</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Promedio por Viaje</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($driverStats)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No hay datos disponibles
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($driverStats as $stat): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($stat['driver_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $stat['trips']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo number_format($stat['liters']); ?> L
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $stat['trips'] > 0 ? number_format($stat['liters'] / $stat['trips']) : '0'; ?> L
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Estadísticas por Tipo de Cliente -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-users text-purple-600 mr-2"></i>Consumo por Tipo de Cliente
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold text-gray-900">Residencial</h4>
                    <i class="fas fa-home text-blue-600 text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-blue-600"><?php echo $clientTypeStats['residential']['count']; ?></p>
                <p class="text-sm text-gray-600"><?php echo number_format($clientTypeStats['residential']['liters']); ?> litros</p>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold text-gray-900">Comercial</h4>
                    <i class="fas fa-building text-green-600 text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-green-600"><?php echo $clientTypeStats['commercial']['count']; ?></p>
                <p class="text-sm text-gray-600"><?php echo number_format($clientTypeStats['commercial']['liters']); ?> litros</p>
            </div>
            
            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold text-gray-900">Industrial</h4>
                    <i class="fas fa-industry text-purple-600 text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-purple-600"><?php echo $clientTypeStats['industrial']['count']; ?></p>
                <p class="text-sm text-gray-600"><?php echo number_format($clientTypeStats['industrial']['liters']); ?> litros</p>
            </div>
        </div>
    </div>
</div>
