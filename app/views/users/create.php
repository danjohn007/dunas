<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Nuevo Usuario</h1>
        <p class="text-gray-600">Registrar nuevo usuario en el sistema</p>
    </div>
    
    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/users/create">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre de Usuario -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre de Usuario <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="username" name="username" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ingrese nombre de usuario">
                </div>
                
                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password" name="password" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ingrese contraseña">
                </div>
                
                <!-- Nombre Completo -->
                <div class="md:col-span-2">
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="full_name" name="full_name" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ingrese nombre completo">
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
                
                <!-- Rol -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                        Rol <span class="text-red-500">*</span>
                    </label>
                    <select id="role" name="role" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Seleccione rol</option>
                        <option value="admin">Administrador</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="operator">Operador</option>
                        <option value="viewer">Visualizador</option>
                        <option value="client">Cliente</option>
                    </select>
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
                <a href="<?php echo BASE_URL; ?>/users" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>
