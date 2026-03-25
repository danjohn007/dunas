<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Editar Usuario</h1>
        <p class="text-gray-600">Actualizar información del usuario</p>
    </div>
    
    <!-- Formulario de Datos -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-user-edit text-blue-600 mr-2"></i>Información del Usuario
        </h2>
        
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
                        <option value="cajero_parque" <?php echo $user['role'] === 'cajero_parque' ? 'selected' : ''; ?>>Cajero Parque</option>
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
    
    <!-- Formulario para Cambiar Contraseña -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-key text-yellow-600 mr-2"></i>Cambiar Contraseña
        </h2>
        
        <form method="POST" action="<?php echo BASE_URL; ?>/users/changePassword/<?php echo $user['id']; ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nueva Contraseña -->
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">
                        Nueva Contraseña <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" id="new_password" name="new_password" required
                               minlength="6"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 pr-10"
                               placeholder="Mínimo 6 caracteres">
                        <button type="button" onclick="togglePassword('new_password', this)" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Confirmar Contraseña -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar Contraseña <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" id="confirm_password" name="confirm_password" required
                               minlength="6"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 pr-10"
                               placeholder="Repita la contraseña">
                        <button type="button" onclick="togglePassword('confirm_password', this)" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    La contraseña debe tener al menos 6 caracteres. Al cambiar la contraseña, el usuario deberá usar la nueva contraseña en su próximo inicio de sesión.
                </p>
            </div>
            
            <!-- Botón -->
            <div class="mt-6 flex justify-end">
                <button type="submit" 
                        class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-key mr-2"></i>Cambiar Contraseña
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
