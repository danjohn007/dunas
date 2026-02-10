<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-bug text-red-600 mr-2"></i>Registro de Errores
            </h1>
            <p class="text-gray-600">Monitor de errores del sistema en tiempo real</p>
        </div>
        <div class="flex space-x-2">
            <?php if (Auth::hasRole(['admin'])): ?>
            <form method="POST" action="<?php echo BASE_URL; ?>/errors/clear" 
                  onsubmit="return confirm('¿Está seguro de que desea limpiar todo el registro de errores? Esta acción no se puede deshacer.');">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                    <i class="fas fa-trash mr-2"></i>
                    Limpiar Log
                </button>
            </form>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/errors/export?<?php echo http_build_query($filters); ?>" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-download mr-2"></i>
                Descargar Log
            </a>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Errores</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total']); ?></p>
                </div>
                <div class="p-3 bg-gray-100 rounded-full">
                    <i class="fas fa-list text-gray-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Fatal / Parse</p>
                    <p class="text-2xl font-bold text-red-600"><?php echo number_format($stats['critical']); ?></p>
                </div>
                <div class="p-3 bg-red-100 rounded-full">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Warnings</p>
                    <p class="text-2xl font-bold text-orange-600"><?php echo number_format($stats['warning']); ?></p>
                </div>
                <div class="p-3 bg-orange-100 rounded-full">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Notice / Deprecated</p>
                    <p class="text-2xl font-bold text-yellow-600"><?php echo number_format($stats['info']); ?></p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-info-circle text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Tamaño Log</p>
                    <p class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['total']); ?> registros</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/errors" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nivel</label>
                <select name="level" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="critical" <?php echo ($filters['level'] === 'critical') ? 'selected' : ''; ?>>Critical/Fatal</option>
                    <option value="error" <?php echo ($filters['level'] === 'error') ? 'selected' : ''; ?>>Error</option>
                    <option value="warning" <?php echo ($filters['level'] === 'warning') ? 'selected' : ''; ?>>Warning</option>
                    <option value="info" <?php echo ($filters['level'] === 'info') ? 'selected' : ''; ?>>Info/Notice</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from']); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to']); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar en errores</label>
                <div class="flex gap-2">
                    <input type="text" name="search" 
                           value="<?php echo htmlspecialchars($filters['search']); ?>"
                           placeholder="Buscar en mensaje o contexto..."
                           class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Errors Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha/Hora
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nivel
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Mensaje
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Usuario
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            IP
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($errors)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-check-circle text-4xl mb-3 text-green-400"></i>
                            <p>No se encontraron errores</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($errors as $error): ?>
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location.href='<?php echo BASE_URL; ?>/errors/detail/<?php echo $error['id']; ?>'">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo date('d/m/Y H:i:s', strtotime($error['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $levelColors = [
                                'critical' => 'bg-red-100 text-red-800',
                                'error' => 'bg-orange-100 text-orange-800',
                                'warning' => 'bg-yellow-100 text-yellow-800',
                                'info' => 'bg-blue-100 text-blue-800'
                            ];
                            $levelIcons = [
                                'critical' => 'fa-times-circle',
                                'error' => 'fa-exclamation-circle',
                                'warning' => 'fa-exclamation-triangle',
                                'info' => 'fa-info-circle'
                            ];
                            $colorClass = $levelColors[$error['level']] ?? 'bg-gray-100 text-gray-800';
                            $iconClass = $levelIcons[$error['level']] ?? 'fa-circle';
                            ?>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $colorClass; ?>">
                                <i class="fas <?php echo $iconClass; ?> mr-1"></i>
                                <?php echo strtoupper($error['level']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="max-w-md truncate">
                                <?php echo htmlspecialchars($error['message']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $error['user_name'] ? htmlspecialchars($error['user_name']) : '-'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                            <?php echo htmlspecialchars($error['ip_address'] ?? '-'); ?>
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
                    (<?php echo number_format($totalErrors); ?> errores en total)
                </div>
                <div class="flex space-x-2">
                    <?php if ($currentPage > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>" 
                       class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-chevron-left mr-1"></i> Anterior
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
