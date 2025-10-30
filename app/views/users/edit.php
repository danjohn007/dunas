<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Editar Usuario</h1>
        <p class="text-gray-600">Actualizar información del usuario</p>
    </div>
    
    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/users/edit/<?php echo $user['id']; ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre de Usuario (readonly) -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre de Usuario
                    </label>
                    <input type="text" id="username" name="username" readonly
                           value="<?php echo htmlspecialchars($user['username']); ?>"
                           class="w-full rounded-lg border-gray-300 bg-gray-100 focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <!-- Nombre Completo -->
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="full_name" name="full_name" required
                           value="<?php echo htmlspecialchars($user['full_name']); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ingrese nombre completo">
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo Electrónico <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($user['email']); ?>"
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
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                        <option value="supervisor" <?php echo $user['role'] === 'supervisor' ? 'selected' : ''; ?>>Supervisor</option>
                        <option value="operator" <?php echo $user['role'] === 'operator' ? 'selected' : ''; ?>>Operador</option>
                        <option value="viewer" <?php echo $user['role'] === 'viewer' ? 'selected' : ''; ?>>Visualizador</option>
                        <option value="client" <?php echo $user['role'] === 'client' ? 'selected' : ''; ?>>Cliente</option>
                    </select>
                </div>
                
                <!-- Estado -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Estado <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
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
                    <i class="fas fa-save mr-2"></i>Actualizar Usuario
                </button>
            </div>
        </form>
    </div>
</div>
