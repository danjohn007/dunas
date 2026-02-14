<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-chart-pie text-indigo-600 mr-2"></i>Resumen de Vales Generados
            </h1>
            <p class="text-gray-600">Agrupación por empresa y serie</p>
        </div>
        <div class="flex space-x-2">
            <a href="<?php echo BASE_URL; ?>/reports/financial?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Volver al Reporte Financiero
            </a>
        </div>
    </div>
    
    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/reports/vouchersSummary" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                <input type="date" name="date_from" value="<?php echo $dateFrom; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                <input type="date" name="date_to" value="<?php echo $dateTo; ?>"
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="md:col-span-2 flex items-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-search mr-2"></i>Filtrar Período
                </button>
            </div>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <?php
    $totalesGenerales = [
        'cantidadTotal' => 0,
        'capacidadTotal' => 0,
        'activosTotal' => 0,
        'usadosTotal' => 0,
        'pagoTotal' => 0,
        'creditoTotal' => 0
    ];
    
    foreach ($vouchersByCompany as $item) {
        $totalesGenerales['cantidadTotal'] += $item['total_vouchers'];
        $totalesGenerales['capacidadTotal'] += $item['total_capacity'];
        $totalesGenerales['activosTotal'] += $item['active_count'];
        $totalesGenerales['usadosTotal'] += $item['used_count'];
        $totalesGenerales['pagoTotal'] += $item['total_paid'];
        $totalesGenerales['creditoTotal'] += $item['total_pending'];
    }
    ?>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90 mb-1">Vales Generados</p>
                    <p class="text-4xl font-bold"><?php echo number_format($totalesGenerales['cantidadTotal']); ?></p>
                    <p class="text-xs opacity-80 mt-1"><?php echo number_format($totalesGenerales['capacidadTotal']); ?> L Total</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-ticket-alt text-3xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-500 to-teal-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90 mb-1">Total Pagado</p>
                    <p class="text-4xl font-bold">$<?php echo number_format($totalesGenerales['pagoTotal'], 2); ?></p>
                    <p class="text-xs opacity-80 mt-1"><?php echo $totalesGenerales['usadosTotal']; ?> vales utilizados</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-dollar-sign text-3xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90 mb-1">Pendiente/Crédito</p>
                    <p class="text-4xl font-bold">$<?php echo number_format($totalesGenerales['creditoTotal'], 2); ?></p>
                    <p class="text-xs opacity-80 mt-1"><?php echo $totalesGenerales['activosTotal']; ?> vales activos</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-clock text-3xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detailed Table by Company -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white flex justify-between items-center">
            <h3 class="text-lg font-semibold">
                <i class="fas fa-table mr-2"></i>Detalle por Empresa
            </h3>
            <div class="flex space-x-2">
                <a href="<?php echo BASE_URL; ?>/reports/exportVouchersSummary?format=csv&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
                   class="bg-white hover:bg-gray-100 text-indigo-600 font-semibold py-2 px-4 rounded-lg inline-flex items-center text-sm"
                   title="Exportar a Excel (CSV)">
                    <i class="fas fa-file-excel mr-2"></i>Excel
                </a>
                <a href="<?php echo BASE_URL; ?>/reports/exportVouchersSummary?format=pdf&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
                   class="bg-white hover:bg-gray-100 text-indigo-600 font-semibold py-2 px-4 rounded-lg inline-flex items-center text-sm"
                   target="_blank"
                   title="Exportar a PDF">
                    <i class="fas fa-file-pdf mr-2"></i>PDF
                </a>
            </div>
        </div>
        
        <?php if (!empty($vouchersByCompany)): ?>
        <!-- Search Box for Company Name -->
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
            <input type="text" 
                   id="companySearchInput" 
                   placeholder="Buscar por nombre de empresa..." 
                   class="w-full md:w-96 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="vouchersCompanyTable">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Empresa / Cliente</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Serie</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Rango Folios</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Cantidad</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Capacidad</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Activos</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Utilizados</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">Pagado</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">Pendiente</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($vouchersByCompany as $companyVoucher): ?>
                    <tr class="hover:bg-indigo-50 transition-colors duration-150">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <i class="fas fa-building text-indigo-500 mr-2"></i>
                            <?php echo htmlspecialchars($companyVoucher['client_name'] ?? 'Sin asignar'); ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs font-bold">
                                <?php echo htmlspecialchars($companyVoucher['serie']); ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-600 font-mono">
                            <?php echo str_pad($companyVoucher['folio_inicial'], 4, '0', STR_PAD_LEFT); ?> 
                            <i class="fas fa-arrow-right text-gray-400 mx-1"></i>
                            <?php echo str_pad($companyVoucher['folio_final'], 4, '0', STR_PAD_LEFT); ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 text-indigo-800 rounded-full font-bold text-lg">
                                <?php echo $companyVoucher['total_vouchers']; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold text-blue-700">
                            <?php echo number_format($companyVoucher['total_capacity']); ?> L
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold">
                                <i class="fas fa-check-circle mr-1"></i><?php echo $companyVoucher['active_count']; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold">
                                <i class="fas fa-check mr-1"></i><?php echo $companyVoucher['used_count']; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="text-sm font-bold text-green-700">
                                $<?php echo number_format($companyVoucher['total_paid'], 2); ?>
                            </div>
                            <?php if (!empty($companyVoucher['total_paid_registered'])): ?>
                            <div class="text-xs text-green-600 mt-1">
                                + $<?php echo number_format($companyVoucher['total_paid_registered'], 2); ?> registrado
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="text-sm font-bold text-orange-600">
                                $<?php echo number_format($companyVoucher['actual_pending'] ?? $companyVoucher['total_pending'], 2); ?>
                            </div>
                            <?php if (!empty($companyVoucher['total_paid_registered'])): ?>
                            <div class="text-xs text-gray-500 line-through mt-1">
                                $<?php echo number_format($companyVoucher['total_pending'], 2); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="<?php echo BASE_URL; ?>/reports/vouchersByCompany?client_id=<?php echo $companyVoucher['client_id']; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
                                   class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-xs font-medium transition">
                                    <i class="fas fa-eye mr-1"></i>Ver Detalle
                                </a>
                                <?php if ($companyVoucher['client_id'] && ($companyVoucher['actual_pending'] ?? $companyVoucher['total_pending']) > 0): ?>
                                <button onclick="openPaymentModal(<?php echo htmlspecialchars(json_encode($companyVoucher)); ?>)" 
                                        class="inline-block bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-medium transition">
                                    <i class="fas fa-dollar-sign mr-1"></i>Registrar Pago
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="px-6 py-12 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                <i class="fas fa-inbox text-4xl text-gray-400"></i>
            </div>
            <p class="text-lg text-gray-600">No se encontraron vales en el período seleccionado</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-dollar-sign text-green-600 mr-2"></i>Registrar Pago
                </h3>
                <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <form method="POST" action="<?php echo BASE_URL; ?>/reports/registerPayment" id="paymentForm">
            <div class="px-6 py-4">
                <input type="hidden" name="client_id" id="payment_client_id">
                <input type="hidden" name="serie" id="payment_serie">
                <input type="hidden" name="folio_inicio" id="payment_folio_inicio">
                <input type="hidden" name="folio_fin" id="payment_folio_fin">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Empresa
                    </label>
                    <div id="payment_client_name" class="text-base font-semibold text-gray-900"></div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Lote de Vales
                    </label>
                    <div class="text-sm text-gray-700">
                        <span class="font-semibold">Serie:</span> <span id="payment_serie_display" class="text-purple-600"></span>
                        <span class="mx-2">|</span>
                        <span class="font-semibold">Folios:</span> <span id="payment_folio_range" class="text-blue-600"></span>
                    </div>
                </div>
                
                <div class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Monto Pendiente:</span>
                        <span id="payment_pending_amount" class="text-lg font-bold text-orange-600"></span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                        Monto a Pagar <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                        <input type="number" name="amount" id="amount" step="0.01" min="0.01" required
                               class="w-full pl-8 rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
                               placeholder="0.00">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Puede registrar pagos parciales</p>
                </div>
                
                <div class="mb-4">
                    <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha de Pago <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="payment_date" id="payment_date" required
                           value="<?php echo date('Y-m-d'); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                </div>
                
                <div class="mb-4">
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                        Método de Pago <span class="text-red-500">*</span>
                    </label>
                    <select name="payment_method" id="payment_method" required
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                        <option value="cash">Efectivo</option>
                        <option value="transfer">Transferencia</option>
                        <option value="check">Cheque</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="reference" class="block text-sm font-medium text-gray-700 mb-2">
                        Referencia
                    </label>
                    <input type="text" name="reference" id="reference"
                           class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
                           placeholder="Número de transferencia, cheque, etc.">
                </div>
                
                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notas
                    </label>
                    <textarea name="notes" id="notes" rows="3"
                              class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500"
                              placeholder="Notas adicionales sobre el pago"></textarea>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closePaymentModal()" 
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i>Registrar Pago
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openPaymentModal(company) {
    document.getElementById('payment_client_id').value = company.client_id;
    document.getElementById('payment_serie').value = company.serie || '';
    document.getElementById('payment_folio_inicio').value = company.folio_inicial || '';
    document.getElementById('payment_folio_fin').value = company.folio_final || '';
    
    document.getElementById('payment_client_name').textContent = company.client_name || 'Sin asignar';
    document.getElementById('payment_serie_display').textContent = company.serie || 'N/A';
    document.getElementById('payment_folio_range').textContent = 
        (company.folio_inicial || 'N/A') + ' - ' + (company.folio_final || 'N/A');
    
    const pendingAmount = company.actual_pending || company.total_pending || 0;
    document.getElementById('payment_pending_amount').textContent = '$' + pendingAmount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    
    // Set max amount for validation
    document.getElementById('amount').max = pendingAmount;
    
    document.getElementById('paymentModal').classList.remove('hidden');
    document.getElementById('paymentModal').classList.add('flex');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
    document.getElementById('paymentModal').classList.remove('flex');
    document.getElementById('paymentForm').reset();
}

// Close modal when clicking outside
document.getElementById('paymentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePaymentModal();
    }
});

// Validate amount on submit
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const amount = parseFloat(document.getElementById('amount').value);
    const maxAmount = parseFloat(document.getElementById('amount').max);
    
    if (amount > maxAmount) {
        e.preventDefault();
        alert('El monto a pagar no puede ser mayor al monto pendiente ($' + maxAmount.toFixed(2) + ')');
        return false;
    }
    
    if (amount <= 0) {
        e.preventDefault();
        alert('El monto debe ser mayor a 0');
        return false;
    }
});

// Company search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('companySearchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('vouchersCompanyTable');
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(function(row) {
                    const firstCell = row.querySelector('td:first-child');
                    if (!firstCell) return;
                    const companyName = firstCell.textContent.toLowerCase();
                    if (companyName.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    }
});
</script>
