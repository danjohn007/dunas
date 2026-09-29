<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Informe por cliente</h1>
        <p class="text-gray-600">Consulte el registro de vales relacionados con cada cliente.</p>
    </div>

    <form method="GET" action="<?php echo BASE_URL; ?>/vouchers/clientReport" class="bg-white rounded-lg shadow-md p-6 mb-6">
        <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
        <div class="flex flex-col sm:flex-row gap-3">
            <select id="client_id" name="client_id" required class="w-full sm:max-w-xl rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Seleccione un cliente</option>
                <?php foreach ($clients as $client): ?>
                <option value="<?php echo (int)$client['id']; ?>" <?php echo ($selectedClient && (int)$selectedClient['id'] === (int)$client['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($client['business_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-search mr-2"></i>Consultar
            </button>
        </div>
    </form>

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
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serie - Folio</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código QR</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Capacidad</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha de registro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <?php if (empty($vouchers)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">Este cliente no tiene vales relacionados.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($vouchers as $voucher): ?>
                            <tr class="hover:bg-gray-50">
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
                                    <?php echo $voucher['status'] === 'registered' && !empty($voucher['used_at']) ? date('d/m/Y H:i', strtotime($voucher['used_at'])) : '—'; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <?php elseif (isset($_GET['client_id'])): ?>
    <p class="text-red-600">No se encontró el cliente seleccionado.</p>
    <?php endif; ?>
</div>

<?php if ($selectedClient): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
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
