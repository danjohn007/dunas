<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Mi Perfil</h1>
        <p class="text-gray-600">Administre su información personal y contraseña</p>
    </div>
    
    <!-- Información del Usuario -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-user text-blue-600 mr-2"></i>Información Personal
        </h2>
        
        <form method="POST" action="<?php echo BASE_URL; ?>/profile/update">
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre Completo
                    </label>
                    <input type="text" name="full_name" required
                           value="<?php echo htmlspecialchars($user['full_name']); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Correo Electrónico
                    </label>
                    <input type="email" name="email" required
                           value="<?php echo htmlspecialchars($user['email']); ?>"
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre de Usuario
                    </label>
                    <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" 
                           disabled
                           class="w-full rounded-lg border-gray-300 bg-gray-100">
                    <p class="mt-1 text-xs text-gray-500">El nombre de usuario no puede ser modificado</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Rol
                    </label>
                    <input type="text" value="<?php echo ucfirst($user['role']); ?>" 
                           disabled
                           class="w-full rounded-lg border-gray-300 bg-gray-100">
                </div>
            </div>
            
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-key text-green-600 mr-2"></i>Cambiar Contraseña
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña Actual
                        </label>
                        <input type="password" name="current_password"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Deje en blanco si no desea cambiar su contraseña</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nueva Contraseña
                        </label>
                        <input type="password" name="new_password"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmar Nueva Contraseña
                        </label>
                        <input type="password" name="confirm_password"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-4 mt-6">
                <a href="<?php echo BASE_URL; ?>/dashboard" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
    
    <!-- Información Adicional -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-gray-900 mb-2">
            <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información de la Cuenta
        </h3>
        <div class="text-sm text-gray-700 space-y-1">
            <p><strong>Fecha de Registro:</strong> <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></p>
            <p><strong>Última Actualización:</strong> <?php echo date('d/m/Y H:i', strtotime($user['updated_at'])); ?></p>
            <p><strong>Estado:</strong> <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800"><?php echo ucfirst($user['status']); ?></span></p>
        </div>
    </div>
</div>
