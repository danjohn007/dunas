<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="<?php echo BASE_URL; ?>/vouchers" class="text-blue-600 hover:text-blue-800 inline-flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver a Vales
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Imprenta de Vales</h1>
        <p class="text-gray-600">Genere vales con código de acceso (letra + 4 dígitos) sin relación inicial de cliente</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/vouchers/storeImprenta" id="imprentaVouchersForm">
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Serie (1 letra) <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="serie"
                           id="serie"
                           required
                           maxlength="1"
                           pattern="[A-Za-z]"
                           placeholder="Ejemplo: A"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 uppercase"
                           value="<?php echo isset($_POST['serie']) ? htmlspecialchars($_POST['serie']) : ''; ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        PIN Inicial (4 dígitos) <span class="text-red-500">*</span>
                    </label>
                    <input type="number"
                           name="start_pin"
                           id="start_pin"
                           required
                           min="0"
                           max="9999"
                           placeholder="Ejemplo: 1000"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           value="<?php echo isset($_POST['start_pin']) ? htmlspecialchars($_POST['start_pin']) : '1000'; ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Cantidad de Vales <span class="text-red-500">*</span>
                    </label>
                    <input type="number"
                           name="quantity"
                           id="quantity"
                           required
                           min="1"
                           max="1000"
                           placeholder="Ejemplo: 100"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Capacidad (Litros) <span class="text-red-500">*</span>
                    </label>
                    <input type="number"
                           name="capacity"
                           id="capacity"
                           required
                           min="1"
                           max="100000"
                           placeholder="Ejemplo: 30000"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           value="<?php echo isset($_POST['capacity']) ? htmlspecialchars($_POST['capacity']) : ''; ?>">
                </div>

                <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4">
                    <p class="text-sm text-indigo-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Los vales de imprenta se generan en estado <strong>Pendiente de Relación</strong> y se activan con el botón <strong>RELACIONAR VALES</strong>.
                    </p>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-8">
                <a href="<?php echo BASE_URL; ?>/vouchers"
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors inline-flex items-center">
                    <i class="fas fa-print mr-2"></i>
                    Generar e Imprimir
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('serie').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
