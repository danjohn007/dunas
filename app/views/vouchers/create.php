<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="<?php echo BASE_URL; ?>/vouchers" class="text-blue-600 hover:text-blue-800 inline-flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver a Vales
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Generar Vales</h1>
        <p class="text-gray-600">Complete el formulario para generar un lote de vales consecutivos</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/vouchers/store" id="generateVouchersForm">
            
            <div class="space-y-6">
                <!-- Cliente -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Seleccionar Cliente <span class="text-red-500">*</span>
                    </label>
                    <select name="client_id" 
                            id="client_id"
                            required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Seleccione un cliente --</option>
                        <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['id']; ?>"
                                <?php echo (isset($_POST['client_id']) && $_POST['client_id'] == $client['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($client['business_name']); ?>
                            <?php if (!empty($client['phone'])): ?>
                                - <?php echo htmlspecialchars($client['phone']); ?>
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">
                        <i class="fas fa-info-circle"></i>
                        Cliente al que se asignarán los vales generados
                    </p>
                </div>
                
                <!-- Serie -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Serie <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="serie" 
                           id="serie"
                           required
                           maxlength="10"
                           pattern="[A-Za-z]+"
                           placeholder="Ejemplo: R, A, B, ABC"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 uppercase"
                           value="<?php echo isset($_POST['serie']) ? htmlspecialchars($_POST['serie']) : ''; ?>">
                    <p class="mt-1 text-sm text-gray-500">
                        <i class="fas fa-info-circle"></i>
                        Solo letras (A-Z), máximo 10 caracteres
                    </p>
                </div>

                <!-- Folio Inicial -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Folio Inicial <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="start_folio" 
                           id="start_folio"
                           required
                           min="1"
                           max="999999"
                           placeholder="Ejemplo: 1, 501, 1001"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           value="<?php echo isset($_POST['start_folio']) ? htmlspecialchars($_POST['start_folio']) : '1'; ?>">
                    <p class="mt-1 text-sm text-gray-500">
                        <i class="fas fa-info-circle"></i>
                        Número inicial del folio (mínimo 1)
                    </p>
                </div>

                <!-- Cantidad de Vales -->
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
                           placeholder="Ejemplo: 10, 50, 100"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>">
                    <p class="mt-1 text-sm text-gray-500">
                        <i class="fas fa-info-circle"></i>
                        Número de vales a generar (máximo 1000 por lote)
                    </p>
                </div>

                <!-- Capacidad en Litros -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Capacidad en Litros <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="capacity" 
                           id="capacity"
                           required
                           min="1"
                           max="100000"
                           placeholder="Ejemplo: 1000, 5000, 10000"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           value="<?php echo isset($_POST['capacity']) ? htmlspecialchars($_POST['capacity']) : ''; ?>">
                    <p class="mt-1 text-sm text-gray-500">
                        <i class="fas fa-info-circle"></i>
                        Capacidad en litros de cada vale
                    </p>
                </div>

                <!-- Preview -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Vista Previa</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p id="preview">
                                    Se generarán vales desde <strong><span id="previewSerie">-</span>-<span id="previewFolioStart">0</span></strong> 
                                    hasta <strong><span id="previewSerieEnd">-</span>-<span id="previewFolioEnd">0</span></strong>
                                    con capacidad de <strong><span id="previewCapacity">0</span> litros</strong> cada uno.
                                </p>
                                <p class="mt-2">
                                    Total: <strong><span id="previewQuantity">0</span> vales</strong> | 
                                    Capacidad total: <strong><span id="previewTotalCapacity">0</span> litros</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-8">
                <a href="<?php echo BASE_URL; ?>/vouchers" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors inline-flex items-center">
                    <i class="fas fa-check mr-2"></i>
                    Generar Vales
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Update preview when form inputs change
function updatePreview() {
    const serie = document.getElementById('serie').value.toUpperCase() || '-';
    const startFolio = parseInt(document.getElementById('start_folio').value) || 0;
    const quantity = parseInt(document.getElementById('quantity').value) || 0;
    const capacity = parseInt(document.getElementById('capacity').value) || 0;
    
    const endFolio = startFolio + quantity - 1;
    const totalCapacity = quantity * capacity;
    
    document.getElementById('previewSerie').textContent = serie;
    document.getElementById('previewSerieEnd').textContent = serie;
    document.getElementById('previewFolioStart').textContent = String(startFolio).padStart(4, '0');
    document.getElementById('previewFolioEnd').textContent = String(endFolio).padStart(4, '0');
    document.getElementById('previewCapacity').textContent = capacity.toLocaleString();
    document.getElementById('previewQuantity').textContent = quantity;
    document.getElementById('previewTotalCapacity').textContent = totalCapacity.toLocaleString();
}

// Add event listeners
document.getElementById('serie').addEventListener('input', updatePreview);
document.getElementById('start_folio').addEventListener('input', updatePreview);
document.getElementById('quantity').addEventListener('input', updatePreview);
document.getElementById('capacity').addEventListener('input', updatePreview);

// Auto-uppercase serie input
document.getElementById('serie').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase();
});

// Initial preview update
updatePreview();
</script>
