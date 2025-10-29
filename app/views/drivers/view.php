<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalle de Chofer</h1>
            <p class="text-gray-600">Información del chofer</p>
        </div>
        <div class="space-x-2">
            <a href="<?php echo BASE_URL; ?>/drivers/edit/<?php echo $driver['id']; ?>" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-block">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
            <a href="<?php echo BASE_URL; ?>/drivers" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>
    
    <!-- Información del Chofer -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Detalles Principales -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-id-card text-blue-600 mr-2"></i>Información General
            </h2>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <p class="text-sm text-gray-500">Nombre Completo</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($driver['full_name']); ?></p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Número de Licencia</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($driver['license_number']); ?></p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Vencimiento de Licencia</p>
                    <?php
                    $expiryDate = strtotime($driver['license_expiry']);
                    $today = time();
                    $daysUntilExpiry = floor(($expiryDate - $today) / (60 * 60 * 24));
                    $isExpiringSoon = $daysUntilExpiry <= 30 && $daysUntilExpiry > 0;
                    $isExpired = $daysUntilExpiry < 0;
                    ?>
                    <p class="text-lg font-semibold <?php echo $isExpired ? 'text-red-600' : ($isExpiringSoon ? 'text-yellow-600' : 'text-gray-900'); ?>">
                        <?php echo date('d/m/Y', $expiryDate); ?>
                        <?php if ($isExpired): ?>
                            <span class="text-xs ml-2">(Vencida)</span>
                        <?php elseif ($isExpiringSoon): ?>
                            <span class="text-xs ml-2">(<?php echo $daysUntilExpiry; ?> días)</span>
                        <?php endif; ?>
                    </p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Teléfono</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($driver['phone']); ?></p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Estado</p>
                    <?php
                    $statusColors = [
                        'active' => 'bg-green-100 text-green-800',
                        'inactive' => 'bg-red-100 text-red-800'
                    ];
                    $statusLabels = [
                        'active' => 'Activo',
                        'inactive' => 'Inactivo'
                    ];
                    ?>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusColors[$driver['status']]; ?>">
                        <?php echo $statusLabels[$driver['status']]; ?>
                    </span>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Fecha de Registro</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo date('d/m/Y', strtotime($driver['created_at'])); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Fotografía -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-image text-blue-600 mr-2"></i>Fotografía
            </h2>
            <?php if (!empty($driver['photo'])): ?>
                <img src="<?php echo BASE_URL; ?>/uploads/drivers/<?php echo htmlspecialchars($driver['photo']); ?>" 
                     alt="Foto del chofer" class="w-full rounded-lg">
            <?php else: ?>
                <div class="flex items-center justify-center h-48 bg-gray-100 rounded-lg">
                    <div class="text-center">
                        <i class="fas fa-user text-gray-400 text-4xl mb-2"></i>
                        <p class="text-gray-500">Sin fotografía</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Unidad Asignada -->
    <?php if (!empty($assignedUnit)): ?>
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-truck text-blue-600 mr-2"></i>Unidad Asignada
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-500">Placa</p>
                <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($assignedUnit['plate_number']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Marca</p>
                <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($assignedUnit['brand']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Modelo</p>
                <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($assignedUnit['model']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Desde</p>
                <p class="text-lg font-semibold text-gray-900"><?php echo date('d/m/Y', strtotime($assignedUnit['assigned_date'])); ?></p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-truck text-blue-600 mr-2"></i>Unidad Asignada
        </h2>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-truck text-4xl mb-2"></i>
            <p>No tiene unidad asignada actualmente</p>
        </div>
    </div>
    <?php endif; ?>
</div>
