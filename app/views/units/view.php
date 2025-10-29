<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalle de Unidad</h1>
            <p class="text-gray-600">Información de la unidad de transporte</p>
        </div>
        <div class="space-x-2">
            <a href="<?php echo BASE_URL; ?>/units/edit/<?php echo $unit['id']; ?>" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-block">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
            <a href="<?php echo BASE_URL; ?>/units" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>
    
    <!-- Información de la Unidad -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Detalles Principales -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-truck text-blue-600 mr-2"></i>Información General
            </h2>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Número de Placa</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($unit['plate_number']); ?></p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Capacidad</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo number_format($unit['capacity_liters']); ?> L</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Marca</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($unit['brand']); ?></p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Modelo</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($unit['model']); ?></p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Año</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($unit['year']); ?></p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Número de Serie</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($unit['serial_number']); ?></p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Estado</p>
                    <?php
                    $statusColors = [
                        'active' => 'bg-green-100 text-green-800',
                        'inactive' => 'bg-red-100 text-red-800',
                        'maintenance' => 'bg-yellow-100 text-yellow-800'
                    ];
                    $statusLabels = [
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'maintenance' => 'En Mantenimiento'
                    ];
                    ?>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusColors[$unit['status']]; ?>">
                        <?php echo $statusLabels[$unit['status']]; ?>
                    </span>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Fecha de Registro</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo date('d/m/Y', strtotime($unit['created_at'])); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Fotografía -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-image text-blue-600 mr-2"></i>Fotografía
            </h2>
            <?php if (!empty($unit['photo'])): ?>
                <img src="<?php echo BASE_URL; ?>/uploads/units/<?php echo htmlspecialchars($unit['photo']); ?>" 
                     alt="Foto de la unidad" class="w-full rounded-lg">
            <?php else: ?>
                <div class="flex items-center justify-center h-48 bg-gray-100 rounded-lg">
                    <div class="text-center">
                        <i class="fas fa-camera text-gray-400 text-4xl mb-2"></i>
                        <p class="text-gray-500">Sin fotografía</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Historial de Mantenimiento -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900">
                <i class="fas fa-wrench text-blue-600 mr-2"></i>Historial de Mantenimiento
            </h2>
            <button onclick="document.getElementById('maintenanceForm').classList.toggle('hidden')"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg text-sm">
                <i class="fas fa-plus mr-2"></i>Agregar Mantenimiento
            </button>
        </div>
        
        <!-- Formulario de Mantenimiento (oculto por defecto) -->
        <div id="maintenanceForm" class="hidden px-6 py-4 bg-gray-50 border-b border-gray-200">
            <form method="POST" action="<?php echo BASE_URL; ?>/units/addMaintenance/<?php echo $unit['id']; ?>">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                        <input type="date" name="maintenance_date" required
                               value="<?php echo date('Y-m-d'); ?>"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Costo</label>
                        <input type="number" name="cost" step="0.01" min="0"
                               placeholder="0.00"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Realizado Por</label>
                        <input type="text" name="performed_by" required
                               placeholder="Nombre del taller o mecánico"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="description" rows="2" required
                                  placeholder="Descripción del mantenimiento realizado"
                                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="md:col-span-3 flex justify-end space-x-2">
                        <button type="button" onclick="document.getElementById('maintenanceForm').classList.add('hidden')"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                            Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Lista de Mantenimientos -->
        <?php if (empty($maintenance)): ?>
            <div class="px-6 py-8 text-center text-gray-500">
                <i class="fas fa-wrench text-4xl mb-2"></i>
                <p>No hay registros de mantenimiento</p>
            </div>
        <?php else: ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Realizado Por</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Costo</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($maintenance as $m): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('d/m/Y', strtotime($m['maintenance_date'])); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo htmlspecialchars($m['description']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($m['performed_by']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                <?php echo $m['cost'] ? '$' . number_format($m['cost'], 2) : '-'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
