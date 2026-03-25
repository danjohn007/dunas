<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Encabezado -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-ticket-alt text-blue-600 mr-2"></i>Visitantes
            </h1>
            <p class="text-gray-600">Boletos registrados manualmente en el Parque Acuático</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/aquapark/registerVisitor"
           class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Registrar Visitante
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/aquapark/visitors">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                    <input type="date" name="date_from"
                           value="<?php echo htmlspecialchars($filters['date_from']); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                    <input type="date" name="date_to"
                           value="<?php echo htmlspecialchars($filters['date_to']); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" name="search"
                           value="<?php echo htmlspecialchars($filters['search']); ?>"
                           placeholder="Nombre, teléfono, código..."
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="w-full bg-gray-700 hover:bg-gray-800 text-white font-semibold py-2 px-4 rounded-lg">
                        <i class="fas fa-search mr-2"></i>Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Resumen -->
    <?php
    $totalTickets = array_sum(array_column($tickets, 'ticket_count'));
    $totalAmount  = array_sum(array_column($tickets, 'total_amount'));
    ?>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-blue-700"><?php echo $pagination['totalRecords']; ?></p>
            <p class="text-sm text-blue-600">Registros</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-green-700"><?php echo $totalTickets; ?></p>
            <p class="text-sm text-green-600">Total boletos</p>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-purple-700">$<?php echo number_format($totalAmount, 2); ?></p>
            <p class="text-sm text-purple-600">Monto total</p>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Visitante</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Boletos</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registrado por</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($tickets)): ?>
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 block text-gray-300"></i>
                        No hay visitantes registrados para este período.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($tickets as $t): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-xs font-mono text-gray-600"><?php echo htmlspecialchars($t['code']); ?></td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                        <?php echo htmlspecialchars($t['visitor_name'] ?: '—'); ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($t['phone'] ?: '—'); ?></td>
                    <td class="px-4 py-3 text-sm text-gray-700"><?php echo date('d/m/Y', strtotime($t['visit_date'])); ?></td>
                    <td class="px-4 py-3 text-sm text-center font-bold text-blue-700"><?php echo (int)$t['ticket_count']; ?></td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        <?php echo $t['total_amount'] !== null ? '$' . number_format($t['total_amount'], 2) : '—'; ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($t['created_by_name'] ?? '—'); ?></td>
                    <td class="px-4 py-3 text-sm">
                        <a href="<?php echo BASE_URL; ?>/aquapark/printTicket/<?php echo $t['id']; ?>"
                           target="_blank"
                           class="inline-flex items-center bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium py-1 px-3 rounded text-xs">
                            <i class="fas fa-print mr-1"></i>Imprimir
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <?php if ($pagination['totalPages'] > 1): ?>
    <div class="mt-4 flex justify-between items-center text-sm text-gray-600">
        <span>Mostrando <?php echo count($tickets); ?> de <?php echo $pagination['totalRecords']; ?> registros</span>
        <div class="flex gap-2">
            <?php if ($pagination['currentPage'] > 1): ?>
            <a href="?page=<?php echo $pagination['currentPage'] - 1; ?>&<?php echo http_build_query(array_diff_key($filters, ['limit' => '', 'offset' => ''])); ?>"
               class="px-3 py-1 bg-white border rounded hover:bg-gray-50">Anterior</a>
            <?php endif; ?>
            <span class="px-3 py-1 bg-blue-600 text-white rounded">Página <?php echo $pagination['currentPage']; ?></span>
            <?php if ($pagination['currentPage'] < $pagination['totalPages']): ?>
            <a href="?page=<?php echo $pagination['currentPage'] + 1; ?>&<?php echo http_build_query(array_diff_key($filters, ['limit' => '', 'offset' => ''])); ?>"
               class="px-3 py-1 bg-white border rounded hover:bg-gray-50">Siguiente</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
