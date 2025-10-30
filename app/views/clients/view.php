<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Detalle de Cliente</h1>
        <p class="text-gray-600">Información completa del cliente</p>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500">Razón Social / Nombre</label>
                <p class="mt-1 text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($client['business_name']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">RFC / CURP</label>
                <p class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars($client['rfc_curp']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Tipo de Cliente</label>
                <p class="mt-1 text-lg text-gray-900">
                    <?php 
                    $types = ['residential' => 'Residencial', 'commercial' => 'Comercial', 'industrial' => 'Industrial'];
                    echo $types[$client['client_type']];
                    ?>
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Estado</label>
                <p class="mt-1">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $client['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo $client['status'] === 'active' ? 'Activo' : 'Inactivo'; ?>
                    </span>
                </p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-500">Dirección</label>
                <p class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars($client['address']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Teléfono</label>
                <p class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars($client['phone']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Email</label>
                <p class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars($client['email']); ?></p>
            </div>
        </div>
        
        <div class="mt-6 flex justify-end space-x-3">
            <a href="<?php echo BASE_URL; ?>/clients" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
            <?php if (Auth::hasRole(['admin', 'supervisor', 'operator'])): ?>
            <a href="<?php echo BASE_URL; ?>/clients/edit/<?php echo $client['id']; ?>" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($transactions)): ?>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Historial de Transacciones</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Litros</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td class="px-4 py-2 text-sm"><?php echo date('d/m/Y', strtotime($transaction['transaction_date'])); ?></td>
                        <td class="px-4 py-2 text-sm"><?php echo number_format($transaction['liters_supplied']); ?> L</td>
                        <td class="px-4 py-2 text-sm">$<?php echo number_format($transaction['total_amount'], 2); ?></td>
                        <td class="px-4 py-2 text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $transaction['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo $transaction['payment_status'] === 'paid' ? 'Pagado' : 'Pendiente'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
