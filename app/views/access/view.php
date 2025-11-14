<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalle de Acceso</h1>
            <p class="text-gray-600">Información del registro de acceso</p>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/access" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>
    
    <!-- Tarjeta de Estado -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">
                <i class="fas fa-ticket-alt text-blue-600 mr-2"></i>Ticket: <?php echo htmlspecialchars($access['ticket_code']); ?>
            </h2>
            <?php
            $statusColors = [
                'in_progress' => 'bg-yellow-100 text-yellow-800',
                'completed' => 'bg-green-100 text-green-800',
                'cancelled' => 'bg-red-100 text-red-800'
            ];
            $statusLabels = [
                'in_progress' => 'En Progreso',
                'completed' => 'Completado',
                'cancelled' => 'Cancelado'
            ];
            ?>
            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?php echo $statusColors[$access['status']]; ?>">
                <?php echo $statusLabels[$access['status']]; ?>
            </span>
        </div>
    </div>
    
    <!-- Información del Acceso -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Información de Entrada -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-door-open text-green-600 mr-2"></i>Información de Entrada
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Fecha y Hora de Entrada</label>
                    <p class="text-gray-900"><?php echo date('d/m/Y H:i:s', strtotime($access['entry_datetime'])); ?></p>
                </div>
                <?php if ($access['exit_datetime']): ?>
                <div>
                    <label class="text-sm font-medium text-gray-500">Fecha y Hora de Salida</label>
                    <p class="text-gray-900"><?php echo date('d/m/Y H:i:s', strtotime($access['exit_datetime'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Información del Cliente -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-building text-blue-600 mr-2"></i>Información del Cliente
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Razón Social</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($access['client_name']); ?></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Teléfono</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($access['client_phone']); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Información del Chofer y Unidad -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Información del Chofer -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-id-card text-purple-600 mr-2"></i>Información del Chofer
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Nombre Completo</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($access['driver_name']); ?></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Teléfono</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($access['driver_phone']); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Información de la Unidad -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-truck text-orange-600 mr-2"></i>Información de la Unidad
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Placas</label>
                    <p class="text-gray-900 font-semibold"><?php echo htmlspecialchars($access['plate_number']); ?></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Capacidad</label>
                    <p class="text-gray-900"><?php echo number_format($access['capacity_liters']); ?> Litros</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Información de Suministro -->
    <?php if ($access['liters_supplied']): ?>
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-tint text-blue-600 mr-2"></i>Información de Suministro
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <label class="text-sm font-medium text-gray-500">Litros Suministrados</label>
                <p class="text-2xl font-bold text-blue-600"><?php echo number_format($access['liters_supplied']); ?> L</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Acciones -->
    <?php if ($access['status'] === 'in_progress' && Auth::hasRole(['admin', 'supervisor', 'operator'])): ?>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones</h3>
        <div class="flex space-x-3">
            <a href="<?php echo BASE_URL; ?>/access/printTicket/<?php echo $access['id']; ?>" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-print mr-2"></i>Imprimir Ticket
            </a>
            <a href="<?php echo BASE_URL; ?>/access/cancel/<?php echo $access['id']; ?>" 
               class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg"
               onclick="return confirm('¿Está seguro de cancelar este acceso?')">
                <i class="fas fa-times mr-2"></i>Cancelar Acceso
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>
