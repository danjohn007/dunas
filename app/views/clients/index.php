<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestión de Clientes</h1>
            <p class="text-gray-600">Administración de clientes del sistema</p>
        </div>
        <?php if (Auth::hasRole(['admin', 'supervisor', 'operator'])): ?>
        <a href="<?php echo BASE_URL; ?>/clients/create" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Nuevo Cliente
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="<?php echo BASE_URL; ?>/clients" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Cliente</label>
                <select name="client_type" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="residential" <?php echo ($filters['client_type'] ?? '') === 'residential' ? 'selected' : ''; ?>>Residencial</option>
                    <option value="commercial" <?php echo ($filters['client_type'] ?? '') === 'commercial' ? 'selected' : ''; ?>>Comercial</option>
                    <option value="industrial" <?php echo ($filters['client_type'] ?? '') === 'industrial' ? 'selected' : ''; ?>>Industrial</option>
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
                <a href="<?php echo BASE_URL; ?>/clients" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Limpiar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Tabla de Clientes -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razón Social</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RFC/CURP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($clients)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No se encontraron clientes
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clients as $client): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($client['business_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($client['rfc_curp']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $typeColors = [
                                    'residential' => 'bg-blue-100 text-blue-800',
                                    'commercial' => 'bg-green-100 text-green-800',
                                    'industrial' => 'bg-purple-100 text-purple-800'
                                ];
                                $typeLabels = [
                                    'residential' => 'Residencial',
                                    'commercial' => 'Comercial',
                                    'industrial' => 'Industrial'
                                ];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $typeColors[$client['client_type']]; ?>">
                                    <?php echo $typeLabels[$client['client_type']]; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($client['phone']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($client['email']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($client['status'] === 'active'): ?>
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
                                <a href="<?php echo BASE_URL; ?>/clients/detail/<?php echo $client['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (Auth::hasRole(['admin', 'supervisor', 'operator'])): ?>
                                <a href="<?php echo BASE_URL; ?>/clients/edit/<?php echo $client['id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (Auth::hasRole(['admin'])): ?>
                                <a href="<?php echo BASE_URL; ?>/clients/delete/<?php echo $client['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Está seguro de eliminar este cliente?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
