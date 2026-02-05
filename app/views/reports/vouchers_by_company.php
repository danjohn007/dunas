<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-list-ul text-blue-600 mr-2"></i>
                    <?php if ($clientName): ?>
                        Vales de: <?php echo htmlspecialchars($clientName); ?>
                    <?php else: ?>
                        Todos los Vales del Sistema
                    <?php endif; ?>
                </h1>
                <p class="text-gray-600">
                    Período: <?php echo date('d/m/Y', strtotime($dateFrom)); ?> al <?php echo date('d/m/Y', strtotime($dateTo)); ?>
                </p>
            </div>
            <a href="<?php echo BASE_URL; ?>/reports/financial?date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Regresar
            </a>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/reports/vouchersByCompany">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                    <input type="date" name="date_from" value="<?php echo $dateFrom; ?>"
                           class="w-full rounded-lg border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                    <input type="date" name="date_to" value="<?php echo $dateTo; ?>"
                           class="w-full rounded-lg border-gray-300">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg w-full">
                        <i class="fas fa-filter mr-2"></i>Aplicar Filtro
                    </button>
                </div>
            </div>
            <?php if ($clientName): ?>
                <input type="hidden" name="client_id" value="<?php echo $_GET['client_id'] ?? ''; ?>">
            <?php endif; ?>
        </form>
    </div>
    
    <?php if (!empty($vouchers)): ?>
        <?php
        $contadores = ['activos' => 0, 'utilizados' => 0, 'anulados' => 0, 'litrosTotal' => 0, 'montoTotal' => 0];
        foreach ($vouchers as $v) {
            if ($v['status'] == 'active') $contadores['activos']++;
            elseif ($v['status'] == 'used' || $v['status'] == 'registered') $contadores['utilizados']++;
            elseif ($v['status'] == 'cancelled') $contadores['anulados']++;
            $contadores['litrosTotal'] += $v['capacity'];
            $contadores['montoTotal'] += $v['cost'];
        }
        ?>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-500 rounded-lg p-4 text-white shadow-lg">
                <div class="text-sm opacity-90 mb-1">Total Registros</div>
                <div class="text-3xl font-bold"><?php echo count($vouchers); ?></div>
            </div>
            <div class="bg-green-500 rounded-lg p-4 text-white shadow-lg">
                <div class="text-sm opacity-90 mb-1">Vales Activos</div>
                <div class="text-3xl font-bold"><?php echo $contadores['activos']; ?></div>
            </div>
            <div class="bg-purple-500 rounded-lg p-4 text-white shadow-lg">
                <div class="text-sm opacity-90 mb-1">Vales Utilizados</div>
                <div class="text-3xl font-bold"><?php echo $contadores['utilizados']; ?></div>
            </div>
            <div class="bg-orange-500 rounded-lg p-4 text-white shadow-lg">
                <div class="text-sm opacity-90 mb-1">Capacidad Total</div>
                <div class="text-3xl font-bold"><?php echo number_format($contadores['litrosTotal']); ?><span class="text-lg ml-1">L</span></div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                            <th class="px-4 py-3 text-left text-sm font-semibold">Código Vale</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Cliente/Empresa</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Capacidad</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Costo</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Estado Pago</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Estado Vale</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Fecha Creación</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Fecha Uso</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($vouchers as $voucher): ?>
                        <tr class="hover:bg-blue-50 transition-colors duration-200">
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <i class="fas fa-qrcode text-blue-600 mr-2"></i>
                                    <span class="font-mono font-bold text-blue-700">
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
                                $estadoPago = $voucher['payment_status'];
                                $clasePago = $estadoPago == 'paid' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800';
                                $iconoPago = $estadoPago == 'paid' ? 'fa-check-circle' : 'fa-clock';
                                $textoPago = $estadoPago == 'paid' ? 'Pagado' : 'Pendiente';
                                ?>
                                <span class="inline-flex items-center <?php echo $clasePago; ?> px-3 py-1 rounded-full text-xs font-bold">
                                    <i class="fas <?php echo $iconoPago; ?> mr-1"></i>
                                    <?php echo $textoPago; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php
                                $estadoVale = $voucher['status'];
                                if ($estadoVale == 'active') {
                                    $claseEstado = 'bg-green-100 text-green-800 border-green-300';
                                    $iconoEstado = 'fa-check-circle';
                                    $textoEstado = 'Activo';
                                } elseif ($estadoVale == 'used' || $estadoVale == 'registered') {
                                    $claseEstado = 'bg-gray-100 text-gray-700 border-gray-300';
                                    $iconoEstado = 'fa-check';
                                    $textoEstado = 'Utilizado';
                                } else {
                                    $claseEstado = 'bg-red-100 text-red-800 border-red-300';
                                    $iconoEstado = 'fa-ban';
                                    $textoEstado = 'Cancelado';
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
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Sin resultados</h3>
            <p class="text-gray-500">No hay vales registrados en el período seleccionado</p>
        </div>
    <?php endif; ?>
</div>
