<!-- Vista de Listado de Vales -->
<div class="container mx-auto px-4 py-6">
    
    <!-- Encabezado con estadísticas -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-ticket-alt text-blue-600 mr-2"></i>
            Gestión de Vales
        </h1>
        
        <?php if (Auth::hasRole(['admin', 'supervisor'])): ?>
        <a href="<?php echo BASE_URL; ?>/vouchers/create" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200 shadow-md">
            <i class="fas fa-plus mr-2"></i>Generar Vales
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Tarjetas de estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                    <i class="fas fa-ticket-alt text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Vales</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Vales Activos</p>
                    <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['active']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-gray-100 rounded-lg p-3">
                    <i class="fas fa-times-circle text-gray-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Vales Usados</p>
                    <p class="text-2xl font-bold text-gray-600"><?php echo number_format($stats['used']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                    <i class="fas fa-tint text-purple-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Litros Disponibles</p>
                    <p class="text-2xl font-bold text-purple-600"><?php echo number_format($stats['total_liters_available']); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/vouchers" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Activos</option>
                    <option value="used" <?php echo $filters['status'] === 'used' ? 'selected' : ''; ?>>Usados</option>
                    <option value="cancelled" <?php echo $filters['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelados</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Serie</label>
                <input type="text" name="serie" value="<?php echo htmlspecialchars($filters['serie']); ?>" 
                       placeholder="Ej: R, A, B..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" 
                       placeholder="Código, serie..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition duration-200">
                    <i class="fas fa-search mr-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
    
    <!-- Tabla de vales -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Creación</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($vouchers)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No hay vales registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($vouchers as $voucher): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($voucher['serie']); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-gray-900"><?php echo str_pad($voucher['folio'], 4, '0', STR_PAD_LEFT); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm text-gray-700"><?php echo htmlspecialchars($voucher['voucher_code']); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-gray-900"><?php echo number_format($voucher['capacity_liters']); ?> L</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'used' => 'bg-gray-100 text-gray-800',
                                        'cancelled' => 'bg-red-100 text-red-800'
                                    ];
                                    $statusLabels = [
                                        'active' => 'Activo',
                                        'used' => 'Usado',
                                        'cancelled' => 'Cancelado'
                                    ];
                                    ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusColors[$voucher['status']]; ?>">
                                        <?php echo $statusLabels[$voucher['status']]; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y H:i', strtotime($voucher['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="<?php echo BASE_URL; ?>/vouchers/view/<?php echo $voucher['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 mr-3" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/vouchers/print?ids=<?php echo $voucher['id']; ?>" 
                                       target="_blank"
                                       class="text-green-600 hover:text-green-900 mr-3" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <?php if ($voucher['status'] === 'active' && Auth::hasRole(['admin', 'supervisor'])): ?>
                                        <button onclick="cancelVoucher(<?php echo $voucher['id']; ?>)" 
                                                class="text-red-600 hover:text-red-900" title="Cancelar">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

<script>
function cancelVoucher(voucherId) {
    if (!confirm('¿Está seguro de que desea cancelar este vale?')) {
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/vouchers/cancel/' + voucherId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cancelar el vale');
    });
}
</script>
