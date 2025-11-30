<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Visitantes</h1>
            <p class="text-gray-600">Gestión de registros de visitantes</p>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/visitors/register" target="_blank"
               class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-external-link-alt mr-2"></i>Formulario Público
            </a>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <input type="text" name="search" placeholder="Buscar..." 
                       value="<?php echo htmlspecialchars($filters['search']); ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos los estados</option>
                    <option value="in" <?php echo $filters['status'] === 'in' ? 'selected' : ''; ?>>Dentro</option>
                    <option value="out" <?php echo $filters['status'] === 'out' ? 'selected' : ''; ?>>Salió</option>
                    <option value="cancelled" <?php echo $filters['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            <div>
                <input type="date" name="date_from" value="<?php echo $filters['date_from']; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <input type="date" name="date_to" value="<?php echo $filters['date_to']; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-search mr-2"></i>Buscar
                </button>
            </div>
        </form>
    </div>
    
    <!-- Tabla de Visitantes -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fotos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Placa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entrada</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Salida</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($visitors)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-4"></i>
                            <p>No hay visitantes registrados</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($visitors as $visitor): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            #<?php echo $visitor['id']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-1">
                                <?php if (!empty($visitor['id_photo'])): ?>
                                <a href="<?php echo BASE_URL . $visitor['id_photo']; ?>" target="_blank" title="Ver ID">
                                    <img src="<?php echo BASE_URL . $visitor['id_photo']; ?>" 
                                         class="h-10 w-10 rounded object-cover border border-gray-200">
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($visitor['plate_photo'])): ?>
                                <a href="<?php echo BASE_URL . $visitor['plate_photo']; ?>" target="_blank" title="Ver Placas">
                                    <img src="<?php echo BASE_URL . $visitor['plate_photo']; ?>" 
                                         class="h-10 w-10 rounded object-cover border border-gray-200">
                                </a>
                                <?php endif; ?>
                            </div>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex space-x-2">
                                <a href="<?php echo BASE_URL; ?>/visitors/view/<?php echo $visitor['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($visitor['status'] === 'in'): ?>
                                <a href="<?php echo BASE_URL; ?>/visitors/exit/<?php echo $visitor['id']; ?>" 
                                   class="text-green-600 hover:text-green-900" title="Registrar Salida"
                                   onclick="return confirm('¿Registrar salida de este visitante?')">
                                    <i class="fas fa-sign-out-alt"></i>
                                </a>
                                <?php if (Auth::hasRole(['admin', 'supervisor'])): ?>
                                <a href="<?php echo BASE_URL; ?>/visitors/cancel/<?php echo $visitor['id']; ?>" 
                                   class="text-red-600 hover:text-red-900" title="Cancelar"
                                   onclick="return confirm('¿Cancelar este registro de visitante?')">
                                    <i class="fas fa-times"></i>
                                </a>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if ($pagination['totalPages'] > 1): ?>
        <div class="bg-gray-50 px-4 py-3 flex items-center justify-between border-t border-gray-200">
            <div class="text-sm text-gray-700">
                Mostrando <?php echo min(($pagination['currentPage'] - 1) * $pagination['perPage'] + 1, $pagination['totalRecords']); ?> 
                a <?php echo min($pagination['currentPage'] * $pagination['perPage'], $pagination['totalRecords']); ?> 
                de <?php echo $pagination['totalRecords']; ?> registros
            </div>
            <div class="flex space-x-1">
                <?php 
                $queryParams = $_GET;
                for ($i = 1; $i <= $pagination['totalPages']; $i++): 
                    $queryParams['page'] = $i;
                    $isActive = $i === $pagination['currentPage'];
                ?>
                <a href="?<?php echo http_build_query($queryParams); ?>"
                   class="<?php echo $isActive ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?> px-3 py-1 rounded border border-gray-300">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
