<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Reporte de Visitantes</h1>
            <p class="text-gray-600">Registro de visitantes por período</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="<?php echo BASE_URL; ?>/reports" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                <input type="date" name="date_from" value="<?php echo $dateFrom; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                <input type="date" name="date_to" value="<?php echo $dateTo; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-search mr-2"></i>Filtrar
                </button>
            </div>
            <div class="flex items-end">
                <a href="<?php echo BASE_URL; ?>/reports/exportExcel/visitors?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>"
                   class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg text-center">
                    <i class="fas fa-file-excel mr-2"></i>Exportar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full mr-4">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Visitantes</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total'] ?? 0); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full mr-4">
                    <i class="fas fa-door-open text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Actualmente Dentro</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['active'] ?? 0); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 bg-indigo-100 rounded-full mr-4">
                    <i class="fas fa-sign-out-alt text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Salidas Registradas</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['exited'] ?? 0); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-full mr-4">
                    <i class="fas fa-ban text-red-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Cancelados</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['cancelled'] ?? 0); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabla de Visitantes -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Placa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entrada</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Salida</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duración</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($visitors)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-search text-4xl mb-4"></i>
                            <p>No hay visitantes en el período seleccionado</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($visitors as $visitor): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            #<?php echo $visitor['id']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($visitor['visitor_name'] ?: '-'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                            <?php echo htmlspecialchars($visitor['plate_number'] ?: '-'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($visitor['phone'] ?: '-'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d/m/Y H:i', strtotime($visitor['entry_datetime'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $visitor['exit_datetime'] ? date('d/m/Y H:i', strtotime($visitor['exit_datetime'])) : '-'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php
                            if ($visitor['exit_datetime']) {
                                $entry = new DateTime($visitor['entry_datetime']);
                                $exit = new DateTime($visitor['exit_datetime']);
                                $diff = $entry->diff($exit);
                                if ($diff->days > 0) {
                                    echo $diff->format('%d día(s) %H:%I');
                                } else {
                                    echo $diff->format('%H:%I');
                                }
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusClasses = [
                                'in' => 'bg-green-100 text-green-800',
                                'out' => 'bg-blue-100 text-blue-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                            $statusLabels = [
                                'in' => 'Dentro',
                                'out' => 'Salió',
                                'cancelled' => 'Cancelado'
                            ];
                            $statusClass = $statusClasses[$visitor['status']] ?? 'bg-gray-100 text-gray-800';
                            $statusLabel = $statusLabels[$visitor['status']] ?? $visitor['status'];
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo $statusLabel; ?>
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
