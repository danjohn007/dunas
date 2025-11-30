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
                    <label for="client_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Cliente <span class="text-red-500">*</span>
                    </label>
                    <select id="client_id" name="client_id" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Seleccione un cliente</option>
                        <?php foreach ($data['clients'] as $client): ?>
                            <option value="<?php echo $client['id']; ?>">
                                <?php echo htmlspecialchars($client['business_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Chofer -->
                <div class="md:col-span-2">
                    <label for="driver_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Chofer <span class="text-red-500">*</span>
                    </label>
                    <select id="driver_id" name="driver_id" required
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
                        Número de Placa <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="plate_number" name="plate_number" required
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
                        Marca <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="brand" name="brand" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ej: Kenworth">
                </div>
                
                <!-- Modelo -->
                <div>
                    <label for="model" class="block text-sm font-medium text-gray-700 mb-1">
                        Modelo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="model" name="model" required
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
