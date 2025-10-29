<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Usuarios</h1>
            <p class="text-gray-600">Administración de usuarios del sistema</p>
        </div>
        <?php if (Auth::hasRole(['admin'])): ?>
        <a href="<?php echo BASE_URL; ?>/users/create" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Nuevo Usuario
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/users" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                <select name="role" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="admin" <?php echo ($filters['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                    <option value="supervisor" <?php echo ($filters['role'] ?? '') === 'supervisor' ? 'selected' : ''; ?>>Supervisor</option>
                    <option value="operator" <?php echo ($filters['role'] ?? '') === 'operator' ? 'selected' : ''; ?>>Operador</option>
                    <option value="viewer" <?php echo ($filters['role'] ?? '') === 'viewer' ? 'selected' : ''; ?>>Visualizador</option>
                    <option value="client" <?php echo ($filters['role'] ?? '') === 'client' ? 'selected' : ''; ?>>Cliente</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactive" <?php echo ($filters['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg mr-2">
                    <i class="fas fa-filter mr-2"></i>Filtrar
                </button>
                <a href="<?php echo BASE_URL; ?>/users" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Limpiar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Tabla de Usuarios -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Completo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No se encontraron usuarios
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['username']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $roleColors = [
                                    'admin' => 'bg-red-100 text-red-800',
                                    'supervisor' => 'bg-blue-100 text-blue-800',
                                    'operator' => 'bg-green-100 text-green-800',
                                    'viewer' => 'bg-gray-100 text-gray-800',
                                    'client' => 'bg-purple-100 text-purple-800'
                                ];
                                $roleLabels = [
                                    'admin' => 'Administrador',
                                    'supervisor' => 'Supervisor',
                                    'operator' => 'Operador',
                                    'viewer' => 'Visualizador',
                                    'client' => 'Cliente'
                                ];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $roleColors[$user['role']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $roleLabels[$user['role']] ?? ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($user['status'] === 'active'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Activo
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if (Auth::hasRole(['admin'])): ?>
                                <a href="<?php echo BASE_URL; ?>/users/edit/<?php echo $user['id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($user['id'] != Auth::user()['id']): ?>
                                <a href="<?php echo BASE_URL; ?>/users/delete/<?php echo $user['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Está seguro de eliminar este usuario?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
