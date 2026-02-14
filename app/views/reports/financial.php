<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Reporte Financiero</h1>
            <p class="text-gray-600">Análisis de ingresos y transacciones</p>
        </div>
        <div class="space-x-2">
            <a href="<?php echo BASE_URL; ?>/reports/exportExcel/financial?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
               class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg inline-block">
                <i class="fas fa-file-excel mr-2"></i>Exportar Excel
            </a>
            <a href="<?php echo BASE_URL; ?>/reports/exportPdf/financial?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
               class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg inline-block">
                <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
            </a>
        </div>
    </div>
    
    <!-- Filtros de Fecha -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/reports/financial" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-md p-6 text-white">
            <p class="text-sm opacity-90 mb-1">Total Ingresos</p>
            <p class="text-3xl font-bold">$<?php echo number_format($stats['total_revenue'], 2); ?></p>
        </div>
        
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-6 text-white">
            <p class="text-sm opacity-90 mb-1">Total Litros</p>
            <p class="text-3xl font-bold"><?php echo number_format($stats['total_liters']); ?></p>
        </div>
        
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-md p-6 text-white">
            <p class="text-sm opacity-90 mb-1">Transacciones</p>
            <p class="text-3xl font-bold"><?php echo $stats['total_transactions']; ?></p>
        </div>
        
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow-md p-6 text-white">
            <p class="text-sm opacity-90 mb-1">Promedio por Transacción</p>
            <p class="text-3xl font-bold">$<?php echo $stats['total_transactions'] > 0 ? number_format($stats['total_revenue'] / $stats['total_transactions'], 2) : '0.00'; ?></p>
        </div>
    </div>
    
    <!-- Ingresos por Método de Pago y Estadísticas de Vales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-money-bill-wave text-green-600 mr-2"></i>Ingresos por Método de Pago
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700"><i class="fas fa-money-bill text-green-600 mr-2"></i>Efectivo</span>
                    <span class="font-semibold text-gray-900">$<?php echo number_format($stats['by_method']['cash'], 2); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-700"><i class="fas fa-ticket-alt text-blue-600 mr-2"></i>Vales (Transacciones)</span>
                    <span class="font-semibold text-gray-900">$<?php echo number_format($stats['by_method']['voucher'], 2); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-700"><i class="fas fa-university text-purple-600 mr-2"></i>Transferencia</span>
                    <span class="font-semibold text-gray-900">$<?php echo number_format($stats['by_method']['bank_transfer'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas de Vales Generados -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-receipt text-blue-600 mr-2"></i>Vales Generados
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700"><i class="fas fa-check-circle text-green-600 mr-2"></i>Pagados</span>
                    <div class="text-right">
                        <div class="font-semibold text-gray-900">$<?php echo number_format($stats['vouchers']['total_paid'], 2); ?></div>
                        <div class="text-xs text-gray-500"><?php echo $stats['vouchers']['paid_count']; ?> vales</div>
                    </div>
                </div>
                <?php if (!empty($stats['vouchers']['total_paid_registered'])): ?>
                <div class="flex justify-between items-center">
                    <span class="text-gray-700"><i class="fas fa-coins text-green-500 mr-2"></i>Pagos Parciales</span>
                    <div class="text-right">
                        <div class="font-semibold text-green-600">$<?php echo number_format($stats['vouchers']['total_paid_registered'], 2); ?></div>
                        <div class="text-xs text-gray-500">Registrados</div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="flex justify-between items-center">
                    <span class="text-gray-700"><i class="fas fa-clock text-orange-600 mr-2"></i>Pendientes (Crédito)</span>
                    <div class="text-right">
                        <div class="font-semibold text-orange-600">$<?php echo number_format($stats['vouchers']['total_pending'], 2); ?></div>
                        <div class="text-xs text-gray-500"><?php echo $stats['vouchers']['pending_count']; ?> vales</div>
                    </div>
                </div>
                <div class="flex justify-between items-center border-t pt-2">
                    <span class="text-gray-700 font-semibold">Total</span>
                    <div class="text-right">
                        <div class="font-bold text-gray-900">$<?php echo number_format($stats['vouchers']['total_amount'], 2); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gráfica de Ingresos por Día -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-bar text-blue-600 mr-2"></i>Ingresos Período
            </h3>
            <div style="height: 250px; position: relative;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Resumen de Vales por Empresa - Nueva Sección -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white flex justify-between items-center">
            <h3 class="text-lg font-semibold">
                <i class="fas fa-building mr-2"></i>Resumen de Vales por Empresa
            </h3>
            <div class="flex space-x-2">
                <a href="<?php echo BASE_URL; ?>/reports/vouchersSummary?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
                   class="bg-white text-indigo-600 hover:bg-gray-100 px-3 py-1 rounded text-sm font-medium transition">
                    <i class="fas fa-chart-pie mr-1"></i>Ver Resumen Completo
                </a>
                <a href="<?php echo BASE_URL; ?>/reports/vouchersByCompany?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
                   class="bg-white text-indigo-600 hover:bg-gray-100 px-3 py-1 rounded text-sm font-medium transition">
                    <i class="fas fa-list-alt mr-1"></i>Ver Detalle de Vales
                </a>
            </div>
        </div>
        
        <?php if (!empty($vouchersByCompany)): ?>
        <!-- Search Box for Company Name -->
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
            <input type="text" 
                   id="companySearchInput" 
                   placeholder="Buscar por nombre de empresa..." 
                   class="w-full md:w-96 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full" id="vouchersCompanyTable">
                <thead class="bg-gray-100 border-b-2 border-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Empresa</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Serie</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Folios</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Total Vales</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Capacidad Total</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Montos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($vouchersByCompany as $companyVoucher): ?>
                    <tr class="hover:bg-blue-50 transition-colors">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($companyVoucher['client_name'] ?? 'Sin asignar'); ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-bold">
                                <?php echo htmlspecialchars($companyVoucher['serie']); ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-600">
                            <?php echo $companyVoucher['folio_inicial']; ?> - <?php echo $companyVoucher['folio_final']; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 bg-indigo-100 text-indigo-800 rounded-full font-bold">
                                <?php echo $companyVoucher['total_vouchers']; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold text-blue-700">
                            <?php echo number_format($companyVoucher['total_capacity']); ?> L
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col space-y-1 text-xs">
                                <?php if ($companyVoucher['active_count'] > 0): ?>
                                <div class="flex items-center justify-center">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded font-medium">
                                        <i class="fas fa-check-circle mr-1"></i><?php echo $companyVoucher['active_count']; ?> Activos
                                    </span>
                                </div>
                                <?php endif; ?>
                                <?php if ($companyVoucher['used_count'] > 0): ?>
                                <div class="flex items-center justify-center">
                                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded font-medium">
                                        <i class="fas fa-check mr-1"></i><?php echo $companyVoucher['used_count']; ?> Usados
                                    </span>
                                </div>
                                <?php endif; ?>
                                <?php if ($companyVoucher['cancelled_count'] > 0): ?>
                                <div class="flex items-center justify-center">
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded font-medium">
                                        <i class="fas fa-ban mr-1"></i><?php echo $companyVoucher['cancelled_count']; ?> Cancelados
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="space-y-1">
                                <div class="text-sm">
                                    <span class="text-gray-600">Pagado:</span>
                                    <span class="font-bold text-green-700">$<?php echo number_format($companyVoucher['total_paid'], 2); ?></span>
                                </div>
                                <?php if (!empty($companyVoucher['total_paid_registered'])): ?>
                                <div class="text-xs text-green-600">
                                    + $<?php echo number_format($companyVoucher['total_paid_registered'], 2); ?> registrado
                                </div>
                                <?php endif; ?>
                                <?php if (($companyVoucher['actual_pending'] ?? $companyVoucher['total_pending']) > 0): ?>
                                <div class="text-sm">
                                    <span class="text-gray-600">Pendiente:</span>
                                    <span class="font-bold text-orange-600">$<?php echo number_format($companyVoucher['actual_pending'] ?? $companyVoucher['total_pending'], 2); ?></span>
                                </div>
                                <?php if (!empty($companyVoucher['total_paid_registered'])): ?>
                                <div class="text-xs text-gray-500 line-through">
                                    $<?php echo number_format($companyVoucher['total_pending'], 2); ?>
                                </div>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="px-6 py-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-3">
                <i class="fas fa-inbox text-3xl text-gray-400"></i>
            </div>
            <p class="text-gray-600">No hay vales generados en este período</p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Tabla de Transacciones -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-list text-blue-600 mr-2"></i>Detalle de Transacciones
            </h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Litros</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No se encontraron transacciones en el período seleccionado
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $trans): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('d/m/Y H:i', strtotime($trans['transaction_date'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($trans['client_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo number_format($trans['liters_supplied']); ?> L
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php
                                $methodLabels = [
                                    'cash' => 'Efectivo',
                                    'voucher' => 'Vales',
                                    'bank_transfer' => 'Transferencia'
                                ];
                                echo $methodLabels[$trans['payment_method']];
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                $<?php echo number_format($trans['total_amount'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Gráfica de ingresos en el período seleccionado
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Verificar que Chart.js esté cargado
        if (typeof Chart === 'undefined') {
            console.error('Chart.js no está cargado');
            return;
        }
        
        const revenueData = <?php echo json_encode($revenueByDay); ?>;
        
        // Validar que hay datos
        if (!revenueData || revenueData.length === 0) {
            console.warn('No hay datos de ingresos por día para mostrar');
            document.getElementById('revenueChart').parentElement.innerHTML = 
                '<p class="text-gray-500 text-center py-8">No hay datos disponibles para el período seleccionado</p>';
            return;
        }
        
        const dates = revenueData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
        });
        const revenues = revenueData.map(item => parseFloat(item.revenue));
        
        const ctx = document.getElementById('revenueChart');
        if (!ctx) {
            console.error('No se encontró el elemento canvas para la gráfica');
            return;
        }
        
        // Destruir instancia previa del gráfico si existe
        const existingChart = Chart.getChart(ctx);
        if (existingChart) {
            existingChart.destroy();
        }
        
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: revenues,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 2,
                    maxBarThickness: 50
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Ingresos: $' + context.parsed.y.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString('es-MX');
                            }
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error al crear la gráfica:', error);
    }
});

// Company search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('companySearchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('vouchersCompanyTable');
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(function(row) {
                    const firstCell = row.querySelector('td:first-child');
                    if (!firstCell) return;
                    const companyName = firstCell.textContent.toLowerCase();
                    if (companyName.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    }
});
</script>
