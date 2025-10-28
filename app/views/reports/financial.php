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
    
    <!-- Ingresos por Método de Pago -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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
                    <span class="text-gray-700"><i class="fas fa-ticket-alt text-blue-600 mr-2"></i>Vales</span>
                    <span class="font-semibold text-gray-900">$<?php echo number_format($stats['by_method']['voucher'], 2); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-700"><i class="fas fa-university text-purple-600 mr-2"></i>Transferencia</span>
                    <span class="font-semibold text-gray-900">$<?php echo number_format($stats['by_method']['bank_transfer'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Gráfica de Ingresos por Día -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-line text-blue-600 mr-2"></i>Ingresos por Día
            </h3>
            <canvas id="revenueChart" height="200"></canvas>
        </div>
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
// Gráfica de ingresos por día
const revenueData = <?php echo json_encode($revenueByDay); ?>;
const dates = revenueData.map(item => {
    const date = new Date(item.date);
    return date.toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
});
const revenues = revenueData.map(item => parseFloat(item.revenue));

const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: dates,
        datasets: [{
            label: 'Ingresos ($)',
            data: revenues,
            backgroundColor: 'rgba(59, 130, 246, 0.5)',
            borderColor: 'rgb(59, 130, 246)',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
