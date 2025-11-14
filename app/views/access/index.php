<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Control de Acceso</h1>
            <p class="text-gray-600">Gesti&oacute;n de accesos de unidades</p>
        </div>
        <?php if (Auth::hasRole(['admin', 'supervisor', 'operator'])): ?>
        <div class="flex gap-2">
            <a href="<?php echo BASE_URL; ?>/access/quickRegistration" 
               class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-bolt mr-2"></i>Registro R&aacute;pido
            </a>
            <a href="<?php echo BASE_URL; ?>/access/scanExit" 
               class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-barcode mr-2"></i>Escanear Salida
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Filtros y Búsqueda -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/access" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Búsqueda -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i>Buscar
                </label>
                <input type="text" 
                       name="search" 
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                       placeholder="Ticket, placa, cliente o chofer..."
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <!-- Estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-filter mr-1"></i>Estado
                </label>
                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="in_progress" <?php echo ($filters['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>En Progreso</option>
                    <option value="completed" <?php echo ($filters['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completado</option>
                    <option value="cancelled" <?php echo ($filters['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            
            <!-- Fecha Desde -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar mr-1"></i>Desde
                </label>
                <input type="date" 
                       name="date_from" 
                       value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <!-- Fecha Hasta -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar mr-1"></i>Hasta
                </label>
                <input type="date" 
                       name="date_to" 
                       value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <!-- Botones -->
            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">
                    <i class="fas fa-search mr-2"></i>Buscar
                </button>
                <a href="<?php echo BASE_URL; ?>/access" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Limpiar
                </a>
            </div>
        </form>
    </div>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidad</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chofer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($accessLogs)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No se encontraron registros</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($accessLogs as $access): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo date('d/m/Y H:i', strtotime($access['entry_datetime'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($access['client_name'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($access['plate_number'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($access['driver_name'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusColors = [
                                    'in_progress' => 'bg-yellow-100 text-yellow-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800'
                                ];
                                $statusLabels = [
                                    'in_progress' => 'En Progreso',
                                    'completed' => 'Completado',
                                    'cancelled' => 'Cancelado'
                                ];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusColors[$access['status']]; ?>">
                                    <?php echo $statusLabels[$access['status']]; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="<?php echo BASE_URL; ?>/access/detail/<?php echo $access['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($access['status'] === 'in_progress' && Auth::hasRole(['admin', 'supervisor', 'operator'])): ?>
                                <a href="<?php echo BASE_URL; ?>/access/registerExit/<?php echo $access['id']; ?>" class="text-green-600 hover:text-green-900 mr-3" title="Registrar salida">
                                    <i class="fas fa-sign-out-alt"></i>
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>/access/printTicket/<?php echo $access['id']; ?>" class="text-red-600 hover:text-red-900" title="Imprimir ticket" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Paginación -->
    <?php 
    // Helper para construir URLs con filtros
    function buildFilterUrl($page, $filters) {
        $params = ['page' => $page];
        if (!empty($filters['search'])) $params['search'] = $filters['search'];
        if (!empty($filters['status'])) $params['status'] = $filters['status'];
        if (!empty($filters['date_from'])) $params['date_from'] = $filters['date_from'];
        if (!empty($filters['date_to'])) $params['date_to'] = $filters['date_to'];
        return '?' . http_build_query($params);
    }
    ?>
    
    <?php if ($pagination['totalPages'] > 1): ?>
    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-4 rounded-lg shadow-md">
        <div class="flex-1 flex justify-between sm:hidden">
            <?php if ($pagination['currentPage'] > 1): ?>
                <a href="<?php echo buildFilterUrl($pagination['currentPage'] - 1, $filters); ?>" 
                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Anterior
                </a>
            <?php endif; ?>
            <?php if ($pagination['currentPage'] < $pagination['totalPages']): ?>
                <a href="<?php echo buildFilterUrl($pagination['currentPage'] + 1, $filters); ?>" 
                   class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Siguiente
                </a>
            <?php endif; ?>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Mostrando
                    <span class="font-medium"><?php echo (($pagination['currentPage'] - 1) * $pagination['perPage']) + 1; ?></span>
                    a
                    <span class="font-medium"><?php echo min($pagination['currentPage'] * $pagination['perPage'], $pagination['totalRecords']); ?></span>
                    de
                    <span class="font-medium"><?php echo $pagination['totalRecords']; ?></span>
                    resultados
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <!-- Botón anterior -->
                    <?php if ($pagination['currentPage'] > 1): ?>
                        <a href="<?php echo buildFilterUrl($pagination['currentPage'] - 1, $filters); ?>" 
                           class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Anterior</span>
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-300 cursor-not-allowed">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    <?php endif; ?>
                    
                    <!-- Números de página -->
                    <?php
                    $startPage = max(1, $pagination['currentPage'] - 2);
                    $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);
                    
                    if ($startPage > 1): ?>
                        <a href="<?php echo buildFilterUrl(1, $filters); ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            1
                        </a>
                        <?php if ($startPage > 2): ?>
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                ...
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <?php if ($i == $pagination['currentPage']): ?>
                            <span class="z-10 bg-blue-600 border-blue-600 text-white relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                <?php echo $i; ?>
                            </span>
                        <?php else: ?>
                            <a href="<?php echo buildFilterUrl($i, $filters); ?>" 
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $pagination['totalPages']): ?>
                        <?php if ($endPage < $pagination['totalPages'] - 1): ?>
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                ...
                            </span>
                        <?php endif; ?>
                        <a href="<?php echo buildFilterUrl($pagination['totalPages'], $filters); ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <?php echo $pagination['totalPages']; ?>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Botón siguiente -->
                    <?php if ($pagination['currentPage'] < $pagination['totalPages']): ?>
                        <a href="<?php echo buildFilterUrl($pagination['currentPage'] + 1, $filters); ?>" 
                           class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Siguiente</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-300 cursor-not-allowed">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
