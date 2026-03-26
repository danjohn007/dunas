<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-chart-bar text-blue-600 mr-2"></i>Reportes - Parque Acuático
        </h1>
        <p class="text-gray-600">Ingresos por pulseras validadas y boletos manuales</p>
    </div>

    <!-- Filtros de fecha -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/aquapark/reports">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                    <input type="date" name="date_from"
                           value="<?php echo htmlspecialchars($dateFrom); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                    <input type="date" name="date_to"
                           value="<?php echo htmlspecialchars($dateTo); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                        <i class="fas fa-search mr-2"></i>Generar Reporte
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Resumen ejecutivo -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-600 rounded-lg p-4 text-white">
            <p class="text-blue-200 text-sm">Pulseras validadas</p>
            <p class="text-3xl font-bold"><?php echo number_format($totalCodesValidated); ?></p>
            <?php if ($priceSerials > 0): ?>
            <p class="text-blue-200 text-xs mt-1">$<?php echo number_format($priceSerials, 2); ?> c/u</p>
            <?php endif; ?>
        </div>
        <div class="bg-green-600 rounded-lg p-4 text-white">
            <p class="text-green-200 text-sm">Boletos manuales</p>
            <p class="text-3xl font-bold"><?php echo number_format($totalTickets); ?></p>
            <?php if ($priceManual > 0): ?>
            <p class="text-green-200 text-xs mt-1">$<?php echo number_format($priceManual, 2); ?> c/u</p>
            <?php endif; ?>
        </div>
        <div class="bg-purple-600 rounded-lg p-4 text-white">
            <p class="text-purple-200 text-sm">Ingresos pulseras</p>
            <p class="text-3xl font-bold">$<?php echo number_format($totalAmountCodes, 2); ?></p>
        </div>
        <div class="bg-orange-600 rounded-lg p-4 text-white">
            <p class="text-orange-200 text-sm">Ingresos totales</p>
            <p class="text-3xl font-bold">$<?php echo number_format($grandTotal, 2); ?></p>
        </div>
    </div>

    <!-- Sección pulseras por día -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-id-badge text-blue-600 mr-2"></i>Pulseras por Día
        </h2>
        <?php if (empty($codeStats)): ?>
        <p class="text-gray-500 text-sm text-center py-4">Sin datos para el período seleccionado.</p>
        <?php else:
            // Group rows by validated_date for display
            $codeStatsByDate = [];
            foreach ($codeStats as $row) {
                $d = $row['validated_date'];
                if (!isset($codeStatsByDate[$d])) {
                    $codeStatsByDate[$d] = ['validated_date' => $d, 'validated_count' => 0, 'amount' => 0];
                }
                $typePrice = $ticketPrices[$row['ticket_type']] ?? $priceSerials;
                $codeStatsByDate[$d]['validated_count'] += (int)$row['validated_count'];
                $codeStatsByDate[$d]['amount']          += (int)$row['validated_count'] * $typePrice;
            }
        ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pulseras validadas</th>
                        <?php if ($priceSerials > 0 || max(array_values($ticketPrices)) > 0): ?>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ingresos</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($codeStatsByDate as $row): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-semibold"><?php echo date('d/m/Y', strtotime($row['validated_date'])); ?></td>
                        <td class="px-4 py-3 text-sm text-center text-green-700 font-semibold"><?php echo (int)$row['validated_count']; ?></td>
                        <?php if ($priceSerials > 0 || max(array_values($ticketPrices)) > 0): ?>
                        <td class="px-4 py-3 text-sm text-right font-semibold">
                            $<?php echo number_format($row['amount'], 2); ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sección boletos manuales por día -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-ticket-alt text-green-600 mr-2"></i>Boletos Manuales por Día
        </h2>
        <?php if (empty($ticketStats)): ?>
        <p class="text-gray-500 text-sm text-center py-4">Sin boletos manuales para el período seleccionado.</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Registros</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total boletos</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($ticketStats as $row): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-semibold"><?php echo date('d/m/Y', strtotime($row['visit_date'])); ?></td>
                        <td class="px-4 py-3 text-sm text-center"><?php echo (int)$row['total_records']; ?></td>
                        <td class="px-4 py-3 text-sm text-center font-semibold text-green-700"><?php echo (int)$row['total_tickets']; ?></td>
                        <td class="px-4 py-3 text-sm text-right font-semibold">
                            $<?php echo number_format((float)$row['total_amount'], 2); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Detalle de boletos manuales (opcional, colapsable) -->
    <?php if (!empty($tickets)): ?>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-list text-gray-500 mr-2"></i>Detalle de Boletos Manuales
        </h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Visitante</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Boletos</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Registrado por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($tickets as $t): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-mono text-xs text-gray-500"><?php echo htmlspecialchars($t['code']); ?></td>
                        <td class="px-3 py-2"><?php echo htmlspecialchars($t['visitor_name'] ?: '—'); ?></td>
                        <td class="px-3 py-2"><?php echo date('d/m/Y', strtotime($t['visit_date'])); ?></td>
                        <td class="px-3 py-2 text-center font-bold text-blue-700"><?php echo (int)$t['ticket_count']; ?></td>
                        <td class="px-3 py-2 text-right"><?php echo $t['total_amount'] !== null ? '$' . number_format($t['total_amount'], 2) : '—'; ?></td>
                        <td class="px-3 py-2 text-gray-500"><?php echo htmlspecialchars($t['created_by_name'] ?? '—'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
