<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Nueva Transacción</h1>
        <p class="text-gray-600">Registrar nueva transacción de pago</p>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/transactions/create" id="transactionForm">
            
            <?php if (!empty($access)): ?>
                <!-- Información del Acceso -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información del Acceso
                    </h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Ticket:</span>
                            <span class="font-semibold ml-2"><?php echo htmlspecialchars($access['ticket_code']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Cliente:</span>
                            <span class="font-semibold ml-2"><?php echo htmlspecialchars($access['client_name']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Unidad:</span>
                            <span class="font-semibold ml-2"><?php echo htmlspecialchars($access['plate_number']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Litros:</span>
                            <span class="font-semibold ml-2"><?php echo number_format($access['liters_supplied']); ?> L</span>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="access_log_id" value="<?php echo $access['id']; ?>">
                <input type="hidden" name="client_id" value="<?php echo $access['client_id']; ?>">
                <input type="hidden" name="liters_supplied" value="<?php echo $access['liters_supplied']; ?>">
            <?php else: ?>
                <!-- Seleccionar Acceso Completado sin Transacción -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Seleccionar Acceso Completado <span class="text-red-500">*</span>
                    </label>
                    <select name="access_log_id" id="accessSelect" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Seleccione un acceso --</option>
                        <!-- Nota: Esta opción requeriría cargar accesos completados sin transacción -->
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Solo se muestran accesos completados sin transacción asociada
                    </p>
                </div>
            <?php endif; ?>
            
            <!-- Información de Pago -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Precio por Litro <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                        <input type="number" name="price_per_liter" id="pricePerLiter" 
                               step="0.01" min="0" required
                               value="5.00"
                               class="pl-7 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               oninput="calculateTotal()">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Monto Total
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                        <input type="text" id="totalAmount" readonly
                               class="pl-7 w-full rounded-lg border-gray-300 bg-gray-100 focus:border-blue-500 focus:ring-blue-500"
                               value="0.00">
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Método de Pago <span class="text-red-500">*</span>
                    </label>
                    <select name="payment_method" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Seleccione --</option>
                        <option value="cash">Efectivo</option>
                        <option value="voucher">Vales</option>
                        <option value="bank_transfer">Transferencia Bancaria</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Estado de Pago <span class="text-red-500">*</span>
                    </label>
                    <select name="payment_status" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="pending">Pendiente</option>
                        <option value="paid" selected>Pagado</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Notas Adicionales
                </label>
                <textarea name="notes" rows="3"
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Ingrese notas adicionales sobre la transacción (opcional)"></textarea>
            </div>
            
            <!-- Botones -->
            <div class="flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/transactions" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Guardar Transacción
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function calculateTotal() {
    <?php if (!empty($access)): ?>
    const liters = <?php echo $access['liters_supplied']; ?>;
    <?php else: ?>
    const liters = 0; // Se actualizará cuando se seleccione el acceso
    <?php endif; ?>
    
    const pricePerLiter = parseFloat(document.getElementById('pricePerLiter').value) || 0;
    const total = liters * pricePerLiter;
    document.getElementById('totalAmount').value = total.toFixed(2);
}

// Calcular total al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
});
</script>
