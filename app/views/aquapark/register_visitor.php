<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Encabezado -->
    <div class="mb-6">
        <div class="flex items-center mb-2">
            <a href="<?php echo BASE_URL; ?>/aquapark/visitors"
               class="text-gray-500 hover:text-gray-700 mr-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Registrar Visitante</h1>
        </div>
        <p class="text-gray-600">Registro manual de visitante al Parque Acuático</p>
    </div>

    <!-- Precio configurado -->
    <?php if ($unitPrice > 0): ?>
    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm text-blue-800">
            <i class="fas fa-tag mr-1"></i>
            Precio por boleto: <strong>$<?php echo number_format($unitPrice, 2); ?></strong>
        </p>
    </div>
    <?php endif; ?>

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/aquapark/registerVisitor">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                <!-- Nombre (opcional) -->
                <div>
                    <label for="visitor_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre del Visitante
                    </label>
                    <input type="text" id="visitor_name" name="visitor_name"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Nombre completo (opcional)">
                </div>

                <!-- Teléfono (opcional) -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                        Teléfono
                    </label>
                    <input type="tel" id="phone" name="phone"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="10 dígitos (opcional)">
                </div>

                <!-- Fecha de visita (default hoy) -->
                <div>
                    <label for="visit_date" class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de Visita
                    </label>
                    <input type="date" id="visit_date" name="visit_date"
                           value="<?php echo date('Y-m-d'); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Número de boletos (requerido) -->
                <div>
                    <label for="ticket_count" class="block text-sm font-medium text-gray-700 mb-1">
                        Número de Boletos <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="ticket_count" name="ticket_count" required min="1" value="1"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Monto calculado (solo info) -->
                <?php if ($unitPrice > 0): ?>
                <div class="sm:col-span-2">
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-600">
                            Total a cobrar:
                            <strong id="totalDisplay" class="text-lg text-blue-700">$<?php echo number_format($unitPrice, 2); ?></strong>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Notas (opcional) -->
                <div class="sm:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Notas
                    </label>
                    <textarea id="notes" name="notes" rows="2"
                              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Observaciones adicionales (opcional)"></textarea>
                </div>
            </div>

            <!-- Botones -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>/aquapark/visitors"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-ticket-alt mr-2"></i>Registrar e Imprimir
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var unitPrice = <?php echo (float)$unitPrice; ?>;
    var ticketInput = document.getElementById('ticket_count');
    var totalDisplay = document.getElementById('totalDisplay');

    if (ticketInput && totalDisplay && unitPrice > 0) {
        ticketInput.addEventListener('input', function () {
            var count = Math.max(0, parseInt(this.value, 10) || 0);
            totalDisplay.textContent = '$' + (unitPrice * count).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        });
    }
})();
</script>
