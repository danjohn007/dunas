<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Reportes del Sistema</h1>
        <p class="text-gray-600">Acceda a los diferentes tipos de reportes disponibles</p>
    </div>
    
    <!-- Tarjetas de Reportes -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6">
        <!-- Reporte de Accesos -->
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4 mx-auto">
                <i class="fas fa-door-open text-blue-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Reporte de Accesos</h3>
            <p class="text-gray-600 text-center mb-4">Entradas y salidas por período</p>
            <a href="<?php echo BASE_URL; ?>/reports/access" 
               class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold py-2 px-4 rounded-lg">
                Ver Reporte
            </a>
        </div>
        
        <!-- Reporte Financiero -->
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4 mx-auto">
                <i class="fas fa-dollar-sign text-green-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Reporte Financiero</h3>
            <p class="text-gray-600 text-center mb-4">Litros e ingresos por período</p>
            <a href="<?php echo BASE_URL; ?>/reports/financial" 
               class="block w-full bg-green-600 hover:bg-green-700 text-white text-center font-semibold py-2 px-4 rounded-lg">
                Ver Reporte
            </a>
        </div>
        
        <!-- Reporte Operativo -->
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4 mx-auto">
                <i class="fas fa-chart-line text-purple-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Reporte Operativo</h3>
            <p class="text-gray-600 text-center mb-4">Eficiencia de unidades y choferes</p>
            <a href="<?php echo BASE_URL; ?>/reports/operational" 
               class="block w-full bg-purple-600 hover:bg-purple-700 text-white text-center font-semibold py-2 px-4 rounded-lg">
                Ver Reporte
            </a>
        </div>
        
        <!-- Reporte de Discrepancias de Placas -->
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mb-4 mx-auto">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Discrepancias</h3>
            <p class="text-gray-600 text-center mb-4">Placas que no coincidieron</p>
            <a href="<?php echo BASE_URL; ?>/reports/plateDiscrepancies" 
               class="block w-full bg-yellow-600 hover:bg-yellow-700 text-white text-center font-semibold py-2 px-4 rounded-lg">
                Ver Reporte
            </a>
        </div>
        
        <!-- Reporte de Acceso por Placas -->
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-full mb-4 mx-auto">
                <i class="fas fa-check-circle text-indigo-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Placas Verificadas</h3>
            <p class="text-gray-600 text-center mb-4">Placas que coincidieron</p>
            <a href="<?php echo BASE_URL; ?>/reports/plateVerification" 
               class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center font-semibold py-2 px-4 rounded-lg">
                Ver Reporte
            </a>
        </div>
        
        <!-- Reporte de Visitantes -->
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-4 mx-auto">
                <i class="fas fa-user-plus text-orange-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Visitantes</h3>
            <p class="text-gray-600 text-center mb-4">Registro de visitantes</p>
            <a href="<?php echo BASE_URL; ?>/reports/visitors" 
               class="block w-full bg-orange-600 hover:bg-orange-700 text-white text-center font-semibold py-2 px-4 rounded-lg">
                Ver Reporte
            </a>
        </div>
    </div>
    
    <!-- Reporte de Vales -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-ticket-alt text-teal-600 mr-2"></i>Reportes de Vales
                </h3>
                <p class="text-gray-600 text-sm mt-1">
                    Vales en estado <span class="font-semibold text-green-600">Activo</span> y 
                    <span class="font-semibold text-blue-600">Registrado</span>, 
                    filtrables por estado y rango de fechas.
                </p>
            </div>
        </div>

        <!-- Filtros -->
        <form method="GET" action="<?php echo BASE_URL; ?>/reports" class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado del Vale</label>
                    <select name="status" class="w-full rounded-lg border-gray-300 focus:border-teal-500 focus:ring-teal-500">
                        <option value="all" <?php echo ($status === 'all') ? 'selected' : ''; ?>>Todos (Activos y Registrados)</option>
                        <option value="active" <?php echo ($status === 'active') ? 'selected' : ''; ?>>Solo Activos</option>
                        <option value="registered" <?php echo ($status === 'registered') ? 'selected' : ''; ?>>Solo Registrados</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom ?? ''); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-teal-500 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo ?? ''); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-teal-500 focus:ring-teal-500">
                </div>
                <div class="lg:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 bg-teal-600 hover:bg-teal-700 text-white font-semibold py-2 px-4 rounded-lg">
                        <i class="fas fa-filter mr-2"></i>Filtrar
                    </button>
                    <a href="<?php echo BASE_URL; ?>/reports" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg">
                        <i class="fas fa-undo mr-2"></i>Limpiar
                    </a>
                </div>
            </div>
        </form>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-teal-500 rounded-lg p-4 text-white shadow-lg">
                <div class="text-sm opacity-90 mb-1">Total de Vales</div>
                <div class="text-3xl font-bold"><?php echo number_format($voucherStats['total']); ?></div>
            </div>
            <div class="bg-green-500 rounded-lg p-4 text-white shadow-lg">
                <div class="text-sm opacity-90 mb-1">Vales Activos</div>
                <div class="text-3xl font-bold"><?php echo number_format($voucherStats['active']); ?></div>
            </div>
            <div class="bg-blue-500 rounded-lg p-4 text-white shadow-lg">
                <div class="text-sm opacity-90 mb-1">Vales Registrados</div>
                <div class="text-3xl font-bold"><?php echo number_format($voucherStats['registered']); ?></div>
            </div>
            <div class="bg-indigo-500 rounded-lg p-4 text-white shadow-lg">
                <div class="text-sm opacity-90 mb-1">Capacidad Total</div>
                <div class="text-3xl font-bold"><?php echo number_format($voucherStats['capacity']); ?><span class="text-lg ml-1">L</span></div>
            </div>
        </div>

        <!-- Barra de acciones: selección y exportación -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-3 text-sm text-gray-700">
                <label class="inline-flex items-center cursor-pointer select-none">
                    <input type="checkbox" id="selectAllVouchers" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500 mr-2">
                    <span class="font-medium">Seleccionar página</span>
                </label>
                <span id="selectedVouchersCount" class="bg-teal-100 text-teal-800 px-3 py-1 rounded-full font-semibold">0 vales seleccionados</span>
            </div>
            <div>
                <form method="GET" action="<?php echo BASE_URL; ?>/reports/exportVouchersReport" id="exportVouchersForm" class="flex flex-wrap gap-2">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                    <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($dateFrom ?? ''); ?>">
                    <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($dateTo ?? ''); ?>">
                    <input type="hidden" name="ids" id="exportSelectedIds" value="">
                    <button type="submit" name="format" value="excel"
                            class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center"
                            title="Exportar a Excel (CSV)">
                        <i class="fas fa-file-excel mr-2"></i>Exportar Excel
                    </button>
                    <button type="submit" name="format" value="pdf"
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center"
                            title="Exportar a PDF">
                        <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                    </button>
                </form>
            </div>
        </div>

        <?php if (!empty($vouchers)): ?>
        <!-- Tabla de vales -->
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-teal-600 to-cyan-600 text-white">
                        <th class="px-4 py-3 text-left text-sm font-semibold w-10"><i class="fas fa-check-square"></i></th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Código Vale</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Cliente/Empresa</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Capacidad</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Costo</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Estado Vale</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Fecha Creación</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Fecha Registro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($vouchers as $voucher): ?>
                    <tr class="hover:bg-teal-50 transition-colors duration-200">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" class="voucher-checkbox rounded border-gray-300 text-teal-600 focus:ring-teal-500"
                                   value="<?php echo (int)$voucher['id']; ?>">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <i class="fas fa-qrcode text-teal-600 mr-2"></i>
                                <span class="font-mono font-bold text-teal-700">
                                    <?php echo htmlspecialchars($voucher['qr_code']); ?>
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Serie: <?php echo htmlspecialchars($voucher['serie']); ?> | 
                                Folio: <?php echo str_pad($voucher['folio'], 4, '0', STR_PAD_LEFT); ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-gray-900">
                                <?php echo htmlspecialchars($voucher['client_name'] ?? 'Sin asignar'); ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                <?php echo number_format($voucher['capacity']); ?> L
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-gray-900">
                            $<?php echo number_format($voucher['cost'], 2); ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php
                            if ($voucher['status'] == 'active') {
                                $claseEstado = 'bg-green-100 text-green-800 border-green-300';
                                $iconoEstado = 'fa-check-circle';
                                $textoEstado = 'Activo';
                            } else {
                                $claseEstado = 'bg-blue-100 text-blue-800 border-blue-300';
                                $iconoEstado = 'fa-clipboard-check';
                                $textoEstado = 'Registrado';
                            }
                            ?>
                            <span class="inline-flex items-center <?php echo $claseEstado; ?> border px-3 py-1 rounded-full text-xs font-bold">
                                <i class="fas <?php echo $iconoEstado; ?> mr-1"></i>
                                <?php echo $textoEstado; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-600">
                            <?php echo date('d/m/Y', strtotime($voucher['created_at'])); ?>
                            <div class="text-xs text-gray-400"><?php echo date('H:i', strtotime($voucher['created_at'])); ?></div>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-600">
                            <?php if ($voucher['used_at']): ?>
                                <?php echo date('d/m/Y', strtotime($voucher['used_at'])); ?>
                                <div class="text-xs text-gray-400"><?php echo date('H:i', strtotime($voucher['used_at'])); ?></div>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 bg-gray-50 border border-gray-200 rounded-lg mt-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
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
        <?php else: ?>
        <div class="bg-gray-50 rounded-lg p-12 text-center border border-dashed border-gray-300">
            <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Sin resultados</h3>
            <p class="text-gray-500">No hay vales activos o registrados en el período seleccionado</p>
        </div>
        <?php endif; ?>

        <script>
        (function() {
            "use strict";
            var masterCheck = document.getElementById('selectAllVouchers');
            var checkboxes = Array.prototype.slice.call(document.querySelectorAll('.voucher-checkbox'));
            var countBadge = document.getElementById('selectedVouchersCount');
            var exportForm = document.getElementById('exportVouchersForm');
            var exportIdsField = document.getElementById('exportSelectedIds');

            function updateCount() {
                var checked = checkboxes.filter(function (cb) { return cb.checked; });
                var total = checked.length;
                countBadge.textContent = total === 1 ? '1 vale seleccionado' : total + ' vales seleccionados';
                if (masterCheck) {
                    masterCheck.checked = checkboxes.length > 0 && total === checkboxes.length;
                    masterCheck.indeterminate = total > 0 && total < checkboxes.length;
                }
            }

            if (masterCheck) {
                masterCheck.addEventListener('change', function () {
                    checkboxes.forEach(function (cb) { cb.checked = masterCheck.checked; });
                    updateCount();
                });
            }
            checkboxes.forEach(function (cb) { cb.addEventListener('change', updateCount); });

            if (exportForm) {
                exportForm.addEventListener('submit', function (e) {
                    var ids = checkboxes
                        .filter(function (cb) { return cb.checked; })
                        .map(function (cb) { return cb.value; });
                    exportIdsField.value = ids.join(',');
                    if (ids.length === 0) {
                        var proceed = window.confirm(
                            'No hay vales seleccionados en esta página. ¿Desea exportar todo el reporte de vales según los filtros aplicados?');
                        if (!proceed) {
                            e.preventDefault();
                        }
                    }
                });
            }
        })();
        </script>
    </div>



    <!-- Información adicional -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información sobre Reportes
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Formatos de Exportación</h4>
                <ul class="text-gray-600 space-y-1">
                    <li><i class="fas fa-file-excel text-green-600 mr-2"></i>Excel (.xlsx) - Para análisis de datos</li>
                    <li><i class="fas fa-file-pdf text-red-600 mr-2"></i>PDF - Para impresión y archivo</li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Filtros Disponibles</h4>
                <ul class="text-gray-600 space-y-1">
                    <li><i class="fas fa-calendar text-blue-600 mr-2"></i>Rango de fechas personalizado</li>
                    <li><i class="fas fa-filter text-blue-600 mr-2"></i>Filtros por estado, tipo, método de pago</li>
                    <li><i class="fas fa-search text-blue-600 mr-2"></i>Búsqueda específica por cliente/unidad/chofer</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Accesos rápidos -->
    <div class="mt-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-6 text-white">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-bolt mr-2"></i>Reportes Rápidos
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="<?php echo BASE_URL; ?>/reports/access?date_from=<?php echo date('Y-m-d'); ?>&date_to=<?php echo date('Y-m-d'); ?>" 
               class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-3 text-center transition">
                <p class="font-semibold">Accesos Hoy</p>
            </a>
            <a href="<?php echo BASE_URL; ?>/reports/financial?date_from=<?php echo date('Y-m-01'); ?>&date_to=<?php echo date('Y-m-d'); ?>" 
               class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-3 text-center transition">
                <p class="font-semibold">Ingresos del Mes</p>
            </a>
            <a href="<?php echo BASE_URL; ?>/reports/operational?date_from=<?php echo date('Y-m-01'); ?>&date_to=<?php echo date('Y-m-d'); ?>" 
               class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-3 text-center transition">
                <p class="font-semibold">Rendimiento del Mes</p>
            </a>
            <a href="<?php echo BASE_URL; ?>/reports/financial?date_from=<?php echo date('Y-01-01'); ?>&date_to=<?php echo date('Y-m-d'); ?>" 
               class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-3 text-center transition">
                <p class="font-semibold">Ingresos Anuales</p>
            </a>
        </div>
    </div>
</div>
