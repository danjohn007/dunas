<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Detalle de Transacción</h1>
        <p class="text-gray-600">Información de la transacción</p>
    </div>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Transaction Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-white">Transacción #<?php echo $transaction['id']; ?></h2>
                    <p class="text-blue-100">Código de Ticket: <?php echo htmlspecialchars($transaction['ticket_code']); ?></p>
                </div>
                <div class="text-right">
                    <?php
                    $statusColors = [
                        'pending' => 'bg-yellow-500',
                        'paid' => 'bg-green-500',
                        'cancelled' => 'bg-red-500'
                    ];
                    $statusLabels = [
                        'pending' => 'Pendiente',
                        'paid' => 'Pagado',
                        'cancelled' => 'Cancelado'
                    ];
                    ?>
                    <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full text-white <?php echo $statusColors[$transaction['payment_status']] ?? 'bg-gray-500'; ?>">
                        <?php echo $statusLabels[$transaction['payment_status']] ?? $transaction['payment_status']; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Transaction Details -->
        <div class="px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Client Information -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user-tie mr-2 text-blue-600"></i>
                        Información del Cliente
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nombre/Razón Social</label>
                            <p class="text-base font-semibold text-gray-900"><?php echo htmlspecialchars($transaction['client_name']); ?></p>
                        </div>
                        <?php if (!empty($transaction['client_phone'])): ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Teléfono</label>
                            <p class="text-base text-gray-900"><?php echo htmlspecialchars($transaction['client_phone']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Transaction Information -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                        Información de la Transacción
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Fecha y Hora</label>
                            <p class="text-base font-semibold text-gray-900"><?php echo date('d/m/Y H:i:s', strtotime($transaction['transaction_date'])); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Método de Pago</label>
                            <p class="text-base text-gray-900">
                                <?php
                                $paymentMethods = [
                                    'cash' => 'Efectivo',
                                    'voucher' => 'Vale',
                                    'bank_transfer' => 'Transferencia Bancaria'
                                ];
                                echo $paymentMethods[$transaction['payment_method']] ?? $transaction['payment_method'];
                                ?>
                            </p>
                        </div>
                        <?php if ($transaction['payment_method'] === 'voucher' && (!empty($transaction['voucher_code']) || (!empty($transaction['voucher_serie']) && !empty($transaction['voucher_folio'])))): ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Número de Vale</label>
                            <p class="text-base font-semibold text-blue-600">
                                <?php 
                                if (!empty($transaction['voucher_serie']) && !empty($transaction['voucher_folio'])) {
                                    echo htmlspecialchars($transaction['voucher_serie']) . '-' . htmlspecialchars($transaction['voucher_folio']);
                                } else {
                                    echo htmlspecialchars($transaction['voucher_code']);
                                }
                                ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Supply Details -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-gas-pump mr-2 text-blue-600"></i>
                        Detalles del Suministro
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Litros Suministrados</label>
                            <p class="text-base font-semibold text-gray-900"><?php echo number_format($transaction['liters_supplied']); ?> L</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Precio por Litro</label>
                            <p class="text-base text-gray-900">$<?php echo number_format($transaction['price_per_liter'], 2); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Access Information -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-door-open mr-2 text-blue-600"></i>
                        Información de Acceso
                    </h3>
                    <div class="space-y-3">
                        <?php if (!empty($transaction['entry_datetime'])): ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Hora de Entrada</label>
                            <p class="text-base text-gray-900"><?php echo date('d/m/Y H:i:s', strtotime($transaction['entry_datetime'])); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($transaction['exit_datetime'])): ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Hora de Salida</label>
                            <p class="text-base text-gray-900"><?php echo date('d/m/Y H:i:s', strtotime($transaction['exit_datetime'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Amount Summary -->
            <div class="mt-6 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-6 border-2 border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">Monto Total</h3>
                        <p class="text-sm text-gray-500"><?php echo number_format($transaction['liters_supplied']); ?> L × $<?php echo number_format($transaction['price_per_liter'], 2); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold text-gray-900">$<?php echo number_format($transaction['total_amount'], 2); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Notes -->
            <?php if (!empty($transaction['notes'])): ?>
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-sticky-note mr-2 text-blue-600"></i>
                    Notas
                </h3>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($transaction['notes'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="mt-6 flex justify-between items-center pt-4 border-t border-gray-200">
                <a href="<?php echo BASE_URL; ?>/transactions" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
                
                <?php if (Auth::hasRole(['admin', 'supervisor'])): ?>
                <a href="<?php echo BASE_URL; ?>/transactions/edit/<?php echo $transaction['id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center">
                    <i class="fas fa-edit mr-2"></i>Editar Transacción
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
