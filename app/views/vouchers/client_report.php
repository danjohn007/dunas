<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Informe por cliente</h1>
        <p class="text-gray-600">Consulte el registro de vales relacionados con cada cliente.</p>
    </div>

    <form method="GET" action="<?php echo BASE_URL; ?>/vouchers/clientReport" class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-[minmax(0,1.4fr)_repeat(4,minmax(0,1fr))_auto] gap-4 items-end">
            <div class="md:col-span-2 xl:col-span-1">
                <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
                <select id="client_id" name="client_id" required class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clients as $client): ?>
                    <option value="<?php echo (int)$client['id']; ?>" <?php echo ($selectedClient && (int)$selectedClient['id'] === (int)$client['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($client['business_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Fecha de relación desde</label>
                <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($dateFrom ?? ''); ?>" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Fecha de relación hasta</label>
                <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($dateTo ?? ''); ?>" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="registered_date_from" class="block text-sm font-medium text-gray-700 mb-2">Fecha de registro desde</label>
                <input type="date" id="registered_date_from" name="registered_date_from" value="<?php echo htmlspecialchars($registeredDateFrom ?? ''); ?>" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="registered_date_to" class="block text-sm font-medium text-gray-700 mb-2">Fecha de registro hasta</label>
                <input type="date" id="registered_date_to" name="registered_date_to" value="<?php echo htmlspecialchars($registeredDateTo ?? ''); ?>" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex justify-end gap-3">
                <button type="submit" aria-label="Consultar" title="Consultar" class="inline-flex h-12 w-12 flex-none items-center justify-center bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-search" aria-hidden="true"></i>
                </button>
                <?php if ($selectedClient && ($dateFrom || $dateTo || $registeredDateFrom || $registeredDateTo)): ?>
                <a href="<?php echo BASE_URL; ?>/vouchers/clientReport?client_id=<?php echo (int)$selectedClient['id']; ?>" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium rounded-lg transition-colors">
                    Limpiar fechas
                </a>
                <?php endif; ?>
            </div>
        </div>
        <p class="mt-3 text-xs text-gray-500">En vales históricos sin fecha de relación guardada se usa la última actualización disponible como referencia. El rango de registro muestra únicamente vales con estado Registrado.</p>
    </form>

    <?php if (!$selectedClient): ?>
    <section class="mb-8">
        <div class="mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Resumen global de clientes</h2>
            <p class="text-sm text-gray-600">Vales actualmente relacionados, agrupados por cliente.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-5">
                <p class="text-sm font-medium text-gray-600">Clientes con vales</p>
                <p class="text-2xl font-bold text-blue-700"><?php echo number_format($globalVoucherTotals['clients_with_vouchers']); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-5">
                <p class="text-sm font-medium text-gray-600">Total de vales</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo number_format($globalVoucherTotals['total_vouchers']); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-5">
                <p class="text-sm font-medium text-gray-600">Registrados</p>
                <p class="text-2xl font-bold text-green-600"><?php echo number_format($globalVoucherTotals['registered']); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-5">
                <p class="text-sm font-medium text-gray-600">Aún no registrados</p>
                <p class="text-2xl font-bold text-amber-600"><?php echo number_format($globalVoucherTotals['not_registered']); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <section class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Registro global de vales</h3>
                <div class="relative h-72">
                    <canvas id="globalVoucherRegistrationChart" aria-label="Gráfica global de vales registrados y no registrados"></canvas>
                </div>
            </section>

            <section class="lg:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Registrados</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">No registrados</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <?php if (empty($clientVoucherSummary)): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No hay clientes para mostrar.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($clientVoucherSummary as $summary): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($summary['client_name']); ?></td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-700"><?php echo number_format($summary['total_vouchers']); ?></td>
                                    <td class="px-4 py-3 text-sm text-right text-green-700"><?php echo number_format($summary['registered_count']); ?></td>
                                    <td class="px-4 py-3 text-sm text-right text-amber-700"><?php echo number_format($summary['not_registered_count']); ?></td>
                                    <td class="px-4 py-3 text-right">
                                        <a class="text-blue-600 hover:text-blue-800" title="Consultar vales del cliente" aria-label="Consultar vales del cliente" href="<?php echo BASE_URL; ?>/vouchers/clientReport?client_id=<?php echo (int)$summary['client_id']; ?>">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($selectedClient): ?>
    <div class="mb-4">
        <h2 class="text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($selectedClient['business_name']); ?></h2>
        <p class="text-sm text-gray-600"><?php echo number_format(count($vouchers)); ?> vales relacionados</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-sm font-medium text-gray-600">Registrados</p>
            <p class="text-2xl font-bold text-green-600"><?php echo number_format($registeredCount); ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-sm font-medium text-gray-600">Aún no registrados</p>
            <p class="text-2xl font-bold text-amber-600"><?php echo number_format($notRegisteredCount); ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <section class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Estado de registro</h3>
            <div class="relative h-72">
                <canvas id="voucherRegistrationChart" aria-label="Gráfica de vales registrados y no registrados"></canvas>
            </div>
        </section>

        <section class="lg:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
            <form method="POST" action="<?php echo BASE_URL; ?>/vouchers/registerSelectedForClient" id="registerSelectedVouchersForm">
                <input type="hidden" name="client_id" value="<?php echo (int)$selectedClient['id']; ?>">
                <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($dateFrom ?? ''); ?>">
                <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($dateTo ?? ''); ?>">
                <input type="hidden" name="registered_date_from" value="<?php echo htmlspecialchars($registeredDateFrom ?? ''); ?>">
                <input type="hidden" name="registered_date_to" value="<?php echo htmlspecialchars($registeredDateTo ?? ''); ?>">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 border-b border-gray-200">
                    <p class="text-sm text-gray-600" aria-live="polite">
                        <span id="selectedVoucherCount">0</span> vales seleccionados
                    </p>
                    <button type="submit" id="registerSelectedVouchersButton" disabled class="inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-check mr-2"></i>Marcar como Registrado
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    <input type="checkbox" id="selectAllActiveVouchers" aria-label="Seleccionar todos los vales activos" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serie - Folio</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código QR</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Capacidad</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha de relación</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha de registro</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <?php if (empty($vouchers)): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">Este cliente no tiene vales relacionados.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($vouchers as $voucher): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <?php if ($voucher['status'] === 'active'): ?>
                                        <input type="checkbox" name="voucher_ids[]" value="<?php echo (int)$voucher['id']; ?>" class="voucher-selection rounded border-gray-300 text-blue-600 focus:ring-blue-500" aria-label="Seleccionar vale <?php echo htmlspecialchars($voucher['serie'] . '-' . str_pad((string)$voucher['folio'], 4, '0', STR_PAD_LEFT)); ?>">
                                        <?php else: ?>
                                        <span class="text-gray-300" aria-hidden="true">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <a class="text-blue-600 hover:text-blue-800" href="<?php echo BASE_URL; ?>/vouchers/detail/<?php echo (int)$voucher['id']; ?>">
                                            <?php echo htmlspecialchars($voucher['serie']); ?>-<?php echo str_pad((string)$voucher['folio'], 4, '0', STR_PAD_LEFT); ?>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-700"><?php echo htmlspecialchars($voucher['qr_code']); ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?php echo number_format($voucher['capacity']); ?> L</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                        <?php
                                        $statusLabels = [
                                            'active' => 'Activo',
                                            'registered' => 'Registrado',
                                            'used' => 'Usado',
                                            'cancelled' => 'Cancelado',
                                            'pending_assignment' => 'Pendiente de relación'
                                        ];
                                        echo htmlspecialchars($statusLabels[$voucher['status']] ?? $voucher['status']);
                                        ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                        <?php echo !empty($voucher['related_at']) ? date('d/m/Y H:i', strtotime($voucher['related_at'])) : 'No disponible'; ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                        <?php echo $voucher['status'] === 'registered' && !empty($voucher['used_at']) ? date('d/m/Y H:i', strtotime($voucher['used_at'])) : '—'; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </section>
    </div>
    <?php elseif (isset($_GET['client_id'])): ?>
    <p class="text-red-600">No se encontró el cliente seleccionado.</p>
    <?php endif; ?>
</div>

<?php if (!$selectedClient): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartCanvas = document.getElementById('globalVoucherRegistrationChart');
    if (!chartCanvas || typeof Chart === 'undefined') return;

    new Chart(chartCanvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Registrados', 'Aún no registrados'],
            datasets: [{
                data: [<?php echo (int)$globalVoucherTotals['registered']; ?>, <?php echo (int)$globalVoucherTotals['not_registered']; ?>],
                backgroundColor: ['#16a34a', '#f59e0b'],
                borderColor: '#ffffff',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
});
</script>
<?php endif; ?>

<?php if ($selectedClient): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectionForm = document.getElementById('registerSelectedVouchersForm');
    if (selectionForm) {
        const selectAll = document.getElementById('selectAllActiveVouchers');
        const voucherCheckboxes = Array.from(selectionForm.querySelectorAll('.voucher-selection'));
        const selectedCount = document.getElementById('selectedVoucherCount');
        const submitButton = document.getElementById('registerSelectedVouchersButton');

        function updateSelection() {
            const checkedCount = voucherCheckboxes.filter(function (checkbox) {
                return checkbox.checked;
            }).length;
            selectedCount.textContent = checkedCount;
            submitButton.disabled = checkedCount === 0;
            selectAll.checked = voucherCheckboxes.length > 0 && checkedCount === voucherCheckboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < voucherCheckboxes.length;
        }

        selectAll.addEventListener('change', function () {
            voucherCheckboxes.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
            updateSelection();
        });
        voucherCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', updateSelection);
        });
        selectionForm.addEventListener('submit', function (event) {
            if (!confirm('¿Desea cambiar el estado de los vales seleccionados a "Registrado"?')) {
                event.preventDefault();
            }
        });
        updateSelection();
    }

    const chartCanvas = document.getElementById('voucherRegistrationChart');
    if (!chartCanvas || typeof Chart === 'undefined') return;

    new Chart(chartCanvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Registrados', 'Aún no registrados'],
            datasets: [{
                data: [<?php echo (int)$registeredCount; ?>, <?php echo (int)$notRegisteredCount; ?>],
                backgroundColor: ['#16a34a', '#f59e0b'],
                borderColor: '#ffffff',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>
<?php endif; ?>
