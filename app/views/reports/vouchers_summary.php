<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-chart-pie text-indigo-600 mr-2"></i>Resumen de Vales Generados
            </h1>
            <p class="text-gray-600">Agrupación por empresa y serie</p>
        </div>
        <div class="flex space-x-2">
            <a href="<?php echo BASE_URL; ?>/reports/financial?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Volver al Reporte Financiero
            </a>
        </div>
    </div>
    
    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/reports/vouchersSummary" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                <input type="date" name="date_from" value="<?php echo $dateFrom; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                <input type="date" name="date_to" value="<?php echo $dateTo; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="md:col-span-2 flex items-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-search mr-2"></i>Filtrar Período
                </button>
            </div>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <?php
    $totalesGenerales = [
        'cantidadTotal' => 0,
        'capacidadTotal' => 0,
        'activosTotal' => 0,
        'usadosTotal' => 0,
        'pagoTotal' => 0,
        'creditoTotal' => 0
    ];
    
    foreach ($vouchersByCompany as $item) {
        $totalesGenerales['cantidadTotal'] += $item['total_vouchers'];
        $totalesGenerales['capacidadTotal'] += $item['total_capacity'];
        $totalesGenerales['activosTotal'] += $item['active_count'];
        $totalesGenerales['usadosTotal'] += $item['used_count'];
        $totalesGenerales['pagoTotal'] += $item['total_paid'];
        $totalesGenerales['creditoTotal'] += $item['total_pending'];
    }
    ?>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90 mb-1">Vales Generados</p>
                    <p class="text-4xl font-bold"><?php echo number_format($totalesGenerales['cantidadTotal']); ?></p>
                    <p class="text-xs opacity-80 mt-1"><?php echo number_format($totalesGenerales['capacidadTotal']); ?> L Total</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-ticket-alt text-3xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-500 to-teal-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90 mb-1">Total Pagado</p>
                    <p class="text-4xl font-bold">$<?php echo number_format($totalesGenerales['pagoTotal'], 2); ?></p>
                    <p class="text-xs opacity-80 mt-1"><?php echo $totalesGenerales['usadosTotal']; ?> vales utilizados</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-dollar-sign text-3xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90 mb-1">Pendiente/Crédito</p>
                    <p class="text-4xl font-bold">$<?php echo number_format($totalesGenerales['creditoTotal'], 2); ?></p>
                    <p class="text-xs opacity-80 mt-1"><?php echo $totalesGenerales['activosTotal']; ?> vales activos</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-clock text-3xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detailed Table by Company -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
            <h3 class="text-lg font-semibold">
                <i class="fas fa-table mr-2"></i>Detalle por Empresa
            </h3>
        </div>
        
        <?php if (!empty($vouchersByCompany)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Empresa / Cliente</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Serie</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Rango Folios</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Cantidad</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Capacidad</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Activos</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Utilizados</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">Pagado</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">Pendiente</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($vouchersByCompany as $companyVoucher): ?>
                    <tr class="hover:bg-indigo-50 transition-colors duration-150">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <i class="fas fa-building text-indigo-500 mr-2"></i>
                            <?php echo htmlspecialchars($companyVoucher['client_name'] ?? 'Sin asignar'); ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs font-bold">
                                <?php echo htmlspecialchars($companyVoucher['serie']); ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-600 font-mono">
                            <?php echo str_pad($companyVoucher['folio_inicial'], 4, '0', STR_PAD_LEFT); ?> 
                            <i class="fas fa-arrow-right text-gray-400 mx-1"></i>
                            <?php echo str_pad($companyVoucher['folio_final'], 4, '0', STR_PAD_LEFT); ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 text-indigo-800 rounded-full font-bold text-lg">
                                <?php echo $companyVoucher['total_vouchers']; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold text-blue-700">
                            <?php echo number_format($companyVoucher['total_capacity']); ?> L
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold">
                                <i class="fas fa-check-circle mr-1"></i><?php echo $companyVoucher['active_count']; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold">
                                <i class="fas fa-check mr-1"></i><?php echo $companyVoucher['used_count']; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-green-700">
                            $<?php echo number_format($companyVoucher['total_paid'], 2); ?>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-orange-600">
                            $<?php echo number_format($companyVoucher['total_pending'], 2); ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="<?php echo BASE_URL; ?>/reports/vouchersByCompany?client_id=<?php echo $companyVoucher['client_id']; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
                               class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-xs font-medium transition">
                                <i class="fas fa-eye mr-1"></i>Ver Detalle
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="px-6 py-12 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                <i class="fas fa-inbox text-4xl text-gray-400"></i>
            </div>
            <p class="text-lg text-gray-600">No se encontraron vales en el período seleccionado</p>
        </div>
        <?php endif; ?>
    </div>
</div>
