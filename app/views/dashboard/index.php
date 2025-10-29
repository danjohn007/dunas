<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600">Resumen general del sistema</p>
    </div>
    
    <!-- Tarjetas de Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Clientes Activos -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Clientes Activos</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo $stats['active_clients']; ?></p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Unidades Activas -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Unidades Activas</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo $stats['active_units']; ?></p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-truck text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Choferes Activos -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Choferes Activos</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo $stats['active_drivers']; ?></p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-id-card text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Accesos en Progreso -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">En Progreso</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo $stats['in_progress_access']; ?></p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-door-open text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Estadísticas del Día -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90 mb-1">Accesos Hoy</p>
                    <p class="text-4xl font-bold"><?php echo $stats['today_access']; ?></p>
                </div>
                <i class="fas fa-chart-line text-5xl opacity-50"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90 mb-1">Litros Hoy</p>
                    <p class="text-4xl font-bold"><?php echo number_format($stats['today_liters']); ?></p>
                </div>
                <i class="fas fa-tint text-5xl opacity-50"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90 mb-1">Ingresos Hoy</p>
                    <p class="text-4xl font-bold">$<?php echo number_format($todayRevenue, 2); ?></p>
                </div>
                <i class="fas fa-dollar-sign text-5xl opacity-50"></i>
            </div>
        </div>
    </div>
    
    <!-- Accesos Recientes y Gráfica -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Accesos Recientes -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-history text-blue-600 mr-2"></i>Accesos Recientes
            </h2>
            <div class="space-y-3">
                <?php if (empty($recentAccess)): ?>
                    <p class="text-gray-500 text-center py-4">No hay registros recientes</p>
                <?php else: ?>
                    <?php foreach ($recentAccess as $access): ?>
                        <div class="border-l-4 <?php echo $access['status'] == 'completed' ? 'border-green-500' : 'border-yellow-500'; ?> bg-gray-50 p-3 rounded">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($access['client_name']); ?></p>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-truck mr-1"></i><?php echo htmlspecialchars($access['plate_number']); ?> 
                                        | <i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($access['driver_name']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-clock mr-1"></i><?php echo date('d/m/Y H:i', strtotime($access['entry_datetime'])); ?>
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded <?php echo $access['status'] == 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo $access['status'] == 'completed' ? 'Completado' : 'En Progreso'; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if (Auth::hasRole(['admin', 'supervisor', 'operator'])): ?>
            <div class="mt-4">
                <a href="<?php echo BASE_URL; ?>/access" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Ver todos los accesos <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Gráfica de Ingresos Mensuales -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-chart-bar text-blue-600 mr-2"></i>Ingresos Mensuales
            </h2>
            <div id="chartContainer">
                <canvas id="revenueChart" height="250"></canvas>
            </div>
            <div id="noDataMessage" class="hidden text-center py-12 text-gray-500">
                <i class="fas fa-chart-line text-5xl mb-3 opacity-30"></i>
                <p class="text-lg">No hay datos de ingresos disponibles</p>
                <p class="text-sm mt-2">Los datos aparecerán cuando haya transacciones registradas en los últimos 6 meses</p>
            </div>
        </div>
    </div>
    
    <!-- Accesos Rápidos -->
    <?php if (Auth::hasRole(['admin', 'supervisor', 'operator'])): ?>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">
            <i class="fas fa-bolt text-blue-600 mr-2"></i>Accesos Rápidos
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="<?php echo BASE_URL; ?>/access/create" class="bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg p-4 text-center transition">
                <i class="fas fa-plus-circle text-blue-600 text-3xl mb-2"></i>
                <p class="text-sm font-medium text-gray-900">Nuevo Acceso</p>
            </a>
            <a href="<?php echo BASE_URL; ?>/clients/create" class="bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg p-4 text-center transition">
                <i class="fas fa-user-plus text-green-600 text-3xl mb-2"></i>
                <p class="text-sm font-medium text-gray-900">Nuevo Cliente</p>
            </a>
            <a href="<?php echo BASE_URL; ?>/transactions" class="bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg p-4 text-center transition">
                <i class="fas fa-receipt text-purple-600 text-3xl mb-2"></i>
                <p class="text-sm font-medium text-gray-900">Transacciones</p>
            </a>
            <a href="<?php echo BASE_URL; ?>/reports" class="bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 rounded-lg p-4 text-center transition">
                <i class="fas fa-file-alt text-yellow-600 text-3xl mb-2"></i>
                <p class="text-sm font-medium text-gray-900">Reportes</p>
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Gráfica de ingresos mensuales
document.addEventListener('DOMContentLoaded', function() {
    try {
        const monthlyData = <?php echo json_encode($monthlyData); ?>;
        
        // Validar que hay datos
        if (!monthlyData || monthlyData.length === 0) {
            console.warn('No hay datos mensuales para mostrar en la gráfica');
            document.getElementById('chartContainer').classList.add('hidden');
            document.getElementById('noDataMessage').classList.remove('hidden');
            return;
        }
        
        // Verificar que Chart.js esté cargado
        if (typeof Chart === 'undefined') {
            console.error('Chart.js no está cargado. Asegúrese de que la librería esté incluida correctamente.');
            document.getElementById('chartContainer').innerHTML = '<div class="text-center py-12 text-red-500"><i class="fas fa-exclamation-triangle text-5xl mb-3"></i><p>Error: Chart.js no está cargado</p></div>';
            return;
        }
        
        const labels = monthlyData.map(item => {
            try {
                const date = new Date(item.month + '-01');
                return date.toLocaleDateString('es-MX', { month: 'short', year: '2-digit' });
            } catch(e) {
                console.error('Error formateando fecha:', item.month, e);
                return item.month;
            }
        });
        const revenues = monthlyData.map(item => parseFloat(item.revenue) || 0);
        
        const ctx = document.getElementById('revenueChart');
        if (!ctx) {
            console.error('No se encontró el elemento canvas para la gráfica');
            return;
        }
        
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: revenues,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
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
        
        console.log('Gráfica de ingresos mensuales creada exitosamente con', monthlyData.length, 'puntos de datos');
    } catch (error) {
        console.error('Error al crear la gráfica:', error);
        const container = document.getElementById('chartContainer');
        if (container) {
            container.innerHTML = '<div class="text-center py-12 text-red-500"><i class="fas fa-exclamation-triangle text-5xl mb-3"></i><p>Error al cargar la gráfica</p><p class="text-sm mt-2">' + error.message + '</p></div>';
        }
    }
});
</script>
