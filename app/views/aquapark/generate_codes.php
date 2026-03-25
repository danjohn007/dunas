<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Encabezado -->
    <div class="mb-6">
        <div class="flex items-center mb-2">
            <a href="<?php echo BASE_URL; ?>/aquapark/codes"
               class="text-gray-500 hover:text-gray-700 mr-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Generar Códigos de Acceso</h1>
        </div>
        <p class="text-gray-600">Genera una serie de códigos QR para las pulseras del parque acuático</p>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/aquapark/generateCodes" id="generateForm">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                <!-- Número inicial -->
                <div>
                    <label for="start_number" class="block text-sm font-medium text-gray-700 mb-1">
                        Número inicial de serie <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="start_number" name="start_number" required min="1"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="p. ej. 1451">
                </div>

                <!-- Número final -->
                <div>
                    <label for="end_number" class="block text-sm font-medium text-gray-700 mb-1">
                        Número final de serie <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="end_number" name="end_number" required min="1"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="p. ej. 1461">
                </div>

                <!-- Fecha de validez -->
                <div class="sm:col-span-2">
                    <label for="valid_date" class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de validez <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="valid_date" name="valid_date" required
                           value="<?php echo date('Y-m-d'); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Los códigos sólo serán válidos durante este día.
                    </p>
                </div>
            </div>

            <!-- Resumen dinámico -->
            <div id="summary" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg hidden">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Se generarán <strong id="countLabel">0</strong> códigos (del <strong id="startLabel">—</strong> al <strong id="endLabel">—</strong>)
                    válidos para el <strong id="dateLabel">—</strong>.
                </p>
            </div>

            <!-- Botones -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>/aquapark/codes"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-qrcode mr-2"></i>Generar e Imprimir
                </button>
            </div>
        </form>
    </div>

    <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <p class="text-sm text-yellow-800">
            <i class="fas fa-print mr-1"></i>
            Al generar los códigos, se abrirá automáticamente la vista de impresión de pulseras (11 por hoja carta).
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const startInput = document.getElementById('start_number');
    const endInput   = document.getElementById('end_number');
    const dateInput  = document.getElementById('valid_date');
    const summary    = document.getElementById('summary');

    function updateSummary() {
        const start = parseInt(startInput.value, 10);
        const end   = parseInt(endInput.value, 10);
        const date  = dateInput.value;

        if (!isNaN(start) && !isNaN(end) && start > 0 && end >= start && date) {
            const count = end - start + 1;
            document.getElementById('countLabel').textContent = count;
            document.getElementById('startLabel').textContent = start;
            document.getElementById('endLabel').textContent   = end;
            const parts = date.split('-');
            document.getElementById('dateLabel').textContent  =
                parts[2] + '/' + parts[1] + '/' + parts[0];
            summary.classList.remove('hidden');
        } else {
            summary.classList.add('hidden');
        }
    }

    startInput.addEventListener('input', updateSummary);
    endInput.addEventListener('input', updateSummary);
    dateInput.addEventListener('input', updateSummary);
    updateSummary();
});
</script>
