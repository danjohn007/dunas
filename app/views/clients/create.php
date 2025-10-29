<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Nuevo Cliente</h1>
        <p class="text-gray-600">Registrar nuevo cliente en el sistema</p>
    </div>
    
    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/clients/create">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Razón Social -->
                <div class="md:col-span-2">
                    <label for="business_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Razón Social / Nombre <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="business_name" name="business_name" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ingrese la razón social o nombre">
                </div>
                
                <!-- RFC/CURP -->
                <div>
                    <label for="rfc_curp" class="block text-sm font-medium text-gray-700 mb-1">
                        RFC / CURP <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="rfc_curp" name="rfc_curp" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ingrese RFC o CURP">
                </div>
                
                <!-- Tipo de Cliente -->
                <div>
                    <label for="client_type" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de Cliente <span class="text-red-500">*</span>
                    </label>
                    <select id="client_type" name="client_type" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Seleccione tipo</option>
                        <option value="residential">Residencial</option>
                        <option value="commercial">Comercial</option>
                        <option value="industrial">Industrial</option>
                    </select>
                </div>
                
                <!-- Dirección -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                        Dirección <span class="text-red-500">*</span>
                    </label>
                    <textarea id="address" name="address" rows="3" required
                              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Ingrese la dirección completa"></textarea>
                </div>
                
                <!-- Teléfono -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                        Teléfono <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" id="phone" name="phone" required maxlength="10"
                           pattern="[0-9]{10}"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="10 dígitos">
                    <p class="mt-1 text-xs text-gray-500">Ingrese 10 dígitos sin espacios ni guiones</p>
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo Electrónico <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ingrese correo electrónico">
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
                    </select>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>/clients" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</div>
