<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Encabezado -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-water text-blue-600 mr-2"></i>Códigos de Acceso
            </h1>
            <p class="text-gray-600">Pulseras QR generadas por serie para el Parque Acuático</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/aquapark/generateCodes"
           class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Generar Códigos
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/aquapark/codes">
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="validated" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="0" <?php echo $filters['validated'] === '0' ? 'selected' : ''; ?>>Sin validar</option>
                        <option value="1" <?php echo $filters['validated'] === '1' ? 'selected' : ''; ?>>Validados</option>
                    </select>
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
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <?php
        $total     = $pagination['totalRecords'];
        $validated = count(array_filter($codes, fn($c) => !empty($c['validated_at'])));
        $pending   = $total - $validated;
        ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-blue-700"><?php echo $total; ?></p>
            <p class="text-sm text-blue-600">Total códigos</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-green-700"><?php echo $validated; ?></p>
            <p class="text-sm text-green-600">Validados</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-yellow-700"><?php echo $pending; ?></p>
            <p class="text-sm text-yellow-600">Pendientes</p>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Válido hasta</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Validado</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Generado por</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($codes)): ?>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 block text-gray-300"></i>
                        No hay códigos para el rango seleccionado.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($codes as $code): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-mono font-semibold text-gray-900"><?php echo (int)$code['series_number']; ?></td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-700"><?php echo htmlspecialchars($code['code']); ?></td>
                    <td class="px-4 py-3 text-sm text-gray-700"><?php echo date('d/m/Y', strtotime($code['valid_date'])); ?></td>
                    <td class="px-4 py-3 text-sm">
                        <?php if (!empty($code['validated_at'])): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check mr-1"></i>Validado
                        </span>
                        <?php else: ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-1"></i>Pendiente
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        <?php echo !empty($code['validated_at']) ? date('d/m/Y H:i', strtotime($code['validated_at'])) : '—'; ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($code['created_by_name'] ?? '—'); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <?php if ($pagination['totalPages'] > 1): ?>
    <div class="mt-4 flex justify-between items-center text-sm text-gray-600">
        <span>Mostrando <?php echo count($codes); ?> de <?php echo $pagination['totalRecords']; ?> registros</span>
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
