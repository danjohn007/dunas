<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Nueva Unidad</h1>
        <p class="text-gray-600">Registrar nueva unidad de transporte</p>
    </div>
    
    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/units/create" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Cliente -->
                <div class="md:col-span-2">
                    <label for="client_search" class="block text-sm font-medium text-gray-700 mb-1">
                        Cliente <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="client_search" autocomplete="off"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Buscar cliente por nombre...">
                        <input type="hidden" id="client_id" name="client_id">
                        <div id="client_results"
                             class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 hidden"
                             style="max-height:200px;overflow-y:auto;"></div>
                    </div>
                </div>
                
                <!-- Chofer -->
                <div class="md:col-span-2">
                    <label for="driver_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Chofer
                    </label>
                    <select id="driver_id" name="driver_id"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Seleccione un chofer</option>
                        <?php foreach ($data['drivers'] as $driver): ?>
                            <option value="<?php echo $driver['id']; ?>">
                                <?php echo htmlspecialchars($driver['full_name']); ?>
                                <?php if (!empty($driver['client_name'])): ?>
                                    - <?php echo htmlspecialchars($driver['client_name']); ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Número de Placa -->
                <div>
                    <label for="plate_number" class="block text-sm font-medium text-gray-700 mb-1">
                        Número de Placa
                    </label>
                    <input type="text" id="plate_number" name="plate_number"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ej: ABC-123-XYZ">
                </div>
                
                <!-- Capacidad -->
                <div>
                    <label for="capacity_liters" class="block text-sm font-medium text-gray-700 mb-1">
                        Capacidad (Litros) <span class="text-red-500">*</span>
                    </label>
                    <select id="capacity_liters" name="capacity_liters" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Seleccione una capacidad</option>
                        <?php if (!empty($data['capacities'])): ?>
                            <?php foreach ($data['capacities'] as $capacity): ?>
                                <option value="<?php echo $capacity['capacity_liters']; ?>" 
                                        data-cost="<?php echo $capacity['cost']; ?>">
                                    <?php echo number_format($capacity['capacity_liters']); ?> L 
                                    - $<?php echo number_format($capacity['cost'], 2); ?>
                                    <?php if (!empty($capacity['description'])): ?>
                                        (<?php echo htmlspecialchars($capacity['description']); ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="5000">5,000 L</option>
                            <option value="10000">10,000 L</option>
                            <option value="12000">12,000 L</option>
                            <option value="15000">15,000 L</option>
                            <option value="20000">20,000 L</option>
                        <?php endif; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">El costo se asignará automáticamente según la capacidad</p>
                </div>
                
                <!-- Marca -->
                <div>
                    <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">
                        Marca
                    </label>
                    <input type="text" id="brand" name="brand"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ej: Kenworth">
                </div>
                
                <!-- Modelo -->
                <div>
                    <label for="model" class="block text-sm font-medium text-gray-700 mb-1">
                        Modelo
                    </label>
                    <input type="text" id="model" name="model"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ej: T800">
                </div>
                
                <!-- Año -->
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">
                        Año
                    </label>
                    <input type="number" id="year" name="year" min="1900" max="2100"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ej: 2020">
                </div>
                
                <!-- Número de Serie -->
                <div>
                    <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-1">
                        Número de Serie
                    </label>
                    <input type="text" id="serial_number" name="serial_number"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Número de serie del vehículo">
                </div>
                
                <!-- Foto -->
                <div class="md:col-span-2">
                    <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">
                        Fotografía de la Unidad
                    </label>
                    <input type="file" id="photo" name="photo" accept="image/*"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Formatos aceptados: JPG, PNG. Tamaño máximo: 5MB</p>
                </div>
                
                <!-- Estado -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Estado <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="active">Activo</option>
                        <option value="inactive">Inactivo</option>
                        <option value="maintenance">En Mantenimiento</option>
                    </select>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>/units" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Guardar Unidad
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var clientsData = <?php echo json_encode(array_values($data['clients']), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var searchInput  = document.getElementById('client_search');
    var hiddenInput  = document.getElementById('client_id');
    var resultsBox   = document.getElementById('client_results');

    function renderResults(matches) {
        if (matches.length === 0) {
            resultsBox.innerHTML = '<div class="p-3 text-gray-500 text-sm">No se encontraron clientes</div>';
        } else {
            resultsBox.innerHTML = matches.map(function (c) {
                var name = c.business_name.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
                return '<div class="p-3 cursor-pointer hover:bg-blue-50 text-sm border-b border-gray-100 last:border-0" data-id="' + c.id + '" data-name="' + name + '">' + name + '</div>';
            }).join('');

            resultsBox.querySelectorAll('div[data-id]').forEach(function (item) {
                item.addEventListener('click', function () {
                    hiddenInput.value  = this.getAttribute('data-id');
                    searchInput.value  = this.getAttribute('data-name');
                    resultsBox.classList.add('hidden');
                });
            });
        }
        resultsBox.classList.remove('hidden');
    }

    searchInput.addEventListener('input', function () {
        hiddenInput.value = '';
        searchInput.classList.remove('border-red-500');
        var q = this.value.toLowerCase().trim();
        if (q.length === 0) { resultsBox.classList.add('hidden'); return; }
        var matches = clientsData.filter(function (c) {
            return c.business_name.toLowerCase().indexOf(q) !== -1;
        });
        renderResults(matches);
    });

    searchInput.addEventListener('focus', function () {
        if (this.value.trim().length > 0 && !hiddenInput.value) {
            searchInput.dispatchEvent(new Event('input'));
        }
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.classList.add('hidden');
        }
    });

    // Validate client_id before submit
    searchInput.closest('form').addEventListener('submit', function (e) {
        if (!hiddenInput.value) {
            e.preventDefault();
            searchInput.focus();
            searchInput.classList.add('border-red-500');
            alert('Por favor seleccione un cliente de la lista.');
        }
    });
}());
</script>
