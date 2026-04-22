<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Vales</h1>
            <p class="text-gray-600">Administre los vales de suministro de agua</p>
        </div>
        <?php if (Auth::hasRole(['admin', 'supervisor'])): ?>
        <div class="flex flex-wrap gap-2">
            <a href="<?php echo BASE_URL; ?>/vouchers/create" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Generar Vales
            </a>
            <a href="<?php echo BASE_URL; ?>/vouchers/imprenta" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-print mr-2"></i>
                Imprenta
            </a>
            <a href="<?php echo BASE_URL; ?>/vouchers/relate" 
               class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-link mr-2"></i>
                RELACIONAR VALES
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Vales</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total']); ?></p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-ticket-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Activos</p>
                    <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['active']); ?></p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Usados</p>
                    <p class="text-2xl font-bold text-gray-600"><?php echo number_format($stats['used']); ?></p>
                </div>
                <div class="p-3 bg-gray-100 rounded-full">
                    <i class="fas fa-times-circle text-gray-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Capacidad Activa</p>
                    <p class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['total_active_capacity']); ?> L</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-tint text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/vouchers" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Serie</label>
                <select name="serie" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todas las series</option>
                    <?php foreach ($series as $s): ?>
                    <option value="<?php echo htmlspecialchars($s['serie']); ?>" 
                            <?php echo ($filters['serie'] === $s['serie']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['serie']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos los estados</option>
                    <option value="active" <?php echo ($filters['status'] === 'active') ? 'selected' : ''; ?>>Activo</option>
                    <option value="pending_assignment" <?php echo ($filters['status'] === 'pending_assignment') ? 'selected' : ''; ?>>Pendiente de Relación</option>
                    <option value="registered" <?php echo ($filters['status'] === 'registered') ? 'selected' : ''; ?>>Registrado</option>
                    <option value="used" <?php echo ($filters['status'] === 'used') ? 'selected' : ''; ?>>Usado</option>
                    <option value="cancelled" <?php echo ($filters['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <input type="text" name="search" 
                       value="<?php echo htmlspecialchars($filters['search']); ?>"
                       placeholder="Serie, folio, código QR..."
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="flex items-end">
                <button type="submit" 
                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    <i class="fas fa-search mr-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Tabla de Vales -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Serie - Folio
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Código QR
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Capacidad
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Creado Por
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha Creación
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($vouchers)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3 text-gray-400"></i>
                            <p>No se encontraron vales</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($vouchers as $voucher): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($voucher['serie']); ?>-<?php echo str_pad($voucher['folio'], 4, '0', STR_PAD_LEFT); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-mono">
                                <?php echo htmlspecialchars($voucher['qr_code']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <?php echo number_format($voucher['capacity']); ?> L
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'registered' => 'bg-blue-100 text-blue-800',
                                'used' => 'bg-gray-100 text-gray-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'pending_assignment' => 'bg-yellow-100 text-yellow-800'
                            ];
                            $statusLabels = [
                                'active' => 'Activo',
                                'registered' => 'Registrado',
                                'used' => 'Usado',
                                'cancelled' => 'Cancelado',
                                'pending_assignment' => 'Pendiente de Relación'
                            ];
                            $colorClass = $statusColors[$voucher['status']] ?? 'bg-gray-100 text-gray-800';
                            $label = $statusLabels[$voucher['status']] ?? $voucher['status'];
                            ?>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $colorClass; ?>">
                                <?php echo $label; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($voucher['created_by_name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d/m/Y H:i', strtotime($voucher['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?php echo BASE_URL; ?>/vouchers/detail/<?php echo $voucher['id']; ?>" 
                               class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($voucher['status'] === 'active' && Auth::hasRole(['admin', 'supervisor'])): ?>
                            <button onclick="confirmCancel(<?php echo $voucher['id']; ?>)" 
                                    class="text-red-600 hover:text-red-900">
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
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Mostrando página <span class="font-medium"><?php echo $currentPage; ?></span> 
                    de <span class="font-medium"><?php echo $totalPages; ?></span>
                    (<?php echo number_format($totalVouchers); ?> vales en total)
                </div>
                <div class="flex space-x-2">
                    <?php if ($currentPage > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>" 
                       class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-chevron-left mr-1"></i> Anterior
                    </a>
                    <?php endif; ?>
                    
                    <?php
                    // Show page numbers
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    
                    if ($startPage > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" 
                           class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                            1
                        </a>
                        <?php if ($startPage > 2): ?>
                            <span class="px-3 py-2 text-gray-500">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                           class="px-3 py-2 border rounded-lg text-sm font-medium <?php echo $i === $currentPage ? 'bg-blue-600 text-white border-blue-600' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="px-3 py-2 text-gray-500">...</span>
                        <?php endif; ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>" 
                           class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <?php echo $totalPages; ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>" 
                       class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Siguiente <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmCancel(voucherId) {
    if (confirm('¿Está seguro de que desea cancelar este vale? Esta acción no se puede deshacer.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo BASE_URL; ?>/vouchers/cancel/' + voucherId;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
