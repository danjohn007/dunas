<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Transacciones</h1>
            <p class="text-gray-600">Administración de transacciones y pagos</p>
        </div>
        <?php if (Auth::hasRole(['admin', 'supervisor', 'operator'])): ?>
        <a href="<?php echo BASE_URL; ?>/transactions/create" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Nueva Transacción
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/transactions" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from']); ?>" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to']); ?>" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
                <select name="client_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los clientes</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['id']; ?>" <?php echo ($filters['client_id'] == $client['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($client['business_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los estados</option>
                    <option value="pending" <?php echo ($filters['payment_status'] == 'pending') ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="paid" <?php echo ($filters['payment_status'] == 'paid') ? 'selected' : ''; ?>>Pagado</option>
                    <option value="cancelled" <?php echo ($filters['payment_status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            <div class="md:col-span-4 flex justify-end space-x-2">
                <a href="<?php echo BASE_URL; ?>/transactions" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Limpiar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-filter mr-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Litros</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No se encontraron transacciones</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo date('d/m/Y H:i', strtotime($transaction['transaction_date'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($transaction['client_name'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo number_format($transaction['liters_supplied']); ?> L</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">$<?php echo number_format($transaction['total_amount'], 2); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'paid' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800'
                                ];
                                $statusLabels = [
                                    'pending' => 'Pendiente',
                                    'paid' => 'Pagado',
                                    'cancelled' => 'Cancelado'
                                ];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusColors[$transaction['payment_status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $statusLabels[$transaction['payment_status']] ?? $transaction['payment_status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="<?php echo BASE_URL; ?>/transactions/detail/<?php echo $transaction['id']; ?>" class="text-blue-600 hover:text-blue-900" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-700">Total:</td>
                    <td colspan="3" class="px-6 py-4 font-bold text-gray-900">$<?php echo number_format($totalAmount, 2); ?></td>
                </tr>
            </tfoot>
        </table>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Anterior
                    </a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo $currentPage + 1; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>" 
                       class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Siguiente
                    </a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando
                        <span class="font-medium"><?php echo (($currentPage - 1) * 10) + 1; ?></span>
                        a
                        <span class="font-medium"><?php echo min($currentPage * 10, $totalRecords); ?></span>
                        de
                        <span class="font-medium"><?php echo $totalRecords; ?></span>
                        resultados
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?php echo $currentPage - 1; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Anterior</span>
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <a href="?page=<?php echo $i; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>" 
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo ($i == $currentPage) ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?php echo $currentPage + 1; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Siguiente</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
