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
                    <input type="number" id="capacity_liters" name="capacity_liters" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ej: 10000">
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
                        Año <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="year" name="year" required min="1900" max="2100"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ej: 2020">
                </div>
                
                <!-- Número de Serie -->
                <div>
                    <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-1">
                        Número de Serie <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="serial_number" name="serial_number" required
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
