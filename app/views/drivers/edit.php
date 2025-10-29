<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Editar Chofer</h1>
        <p class="text-gray-600">Actualizar información del chofer</p>
    </div>
    
    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/drivers/edit/<?php echo $driver['id']; ?>" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre Completo -->
                <div class="md:col-span-2">
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="full_name" name="full_name" required
                           value="<?php echo htmlspecialchars($driver['full_name']); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ingrese el nombre completo">
                </div>
                
                <!-- Número de Licencia -->
                <div>
                    <label for="license_number" class="block text-sm font-medium text-gray-700 mb-1">
                        Número de Licencia <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="license_number" name="license_number" required
                           value="<?php echo htmlspecialchars($driver['license_number']); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Número de licencia de conducir">
                </div>
                
                <!-- Fecha de Vencimiento de Licencia -->
                <div>
                    <label for="license_expiry" class="block text-sm font-medium text-gray-700 mb-1">
                        Vencimiento de Licencia <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="license_expiry" name="license_expiry" required
                           value="<?php echo htmlspecialchars($driver['license_expiry']); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <!-- Teléfono -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                        Teléfono <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" id="phone" name="phone" required maxlength="10"
                           pattern="[0-9]{10}"
                           value="<?php echo htmlspecialchars($driver['phone']); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="10 dígitos">
                    <p class="mt-1 text-xs text-gray-500">Ingrese 10 dígitos sin espacios ni guiones</p>
                </div>
                
                <!-- Estado -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Estado <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="active" <?php echo $driver['status'] === 'active' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactive" <?php echo $driver['status'] === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                
                <!-- Foto Actual -->
                <?php if (!empty($driver['photo'])): ?>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto Actual</label>
                    <img src="<?php echo BASE_URL; ?>/uploads/drivers/<?php echo htmlspecialchars($driver['photo']); ?>" 
                         alt="Foto del chofer" class="h-32 rounded-lg">
                </div>
                <?php endif; ?>
                
                <!-- Nueva Foto -->
                <div class="md:col-span-2">
                    <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">
                        Nueva Fotografía (opcional)
                    </label>
                    <input type="file" id="photo" name="photo" accept="image/*"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Formatos aceptados: JPG, PNG. Tamaño máximo: 5MB</p>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>/drivers" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Actualizar Chofer
                </button>
            </div>
        </form>
    </div>
</div>
