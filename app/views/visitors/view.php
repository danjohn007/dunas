<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalle de Visitante</h1>
            <p class="text-gray-600">Información del registro #<?php echo $visitor['id']; ?></p>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/visitors" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>
    
    <!-- Estado del Visitante -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <?php
                $statusClasses = [
                    'in' => 'bg-green-100 text-green-800',
                    'out' => 'bg-blue-100 text-blue-800',
                    'cancelled' => 'bg-red-100 text-red-800'
                ];
                $statusLabels = [
                    'in' => 'Dentro',
                    'out' => 'Salió',
                    'cancelled' => 'Cancelado'
                ];
                $statusIcons = [
                    'in' => 'fas fa-door-open',
                    'out' => 'fas fa-door-closed',
                    'cancelled' => 'fas fa-ban'
                ];
                ?>
                <span class="<?php echo $statusClasses[$visitor['status']]; ?> px-4 py-2 rounded-full text-lg font-semibold">
                    <i class="<?php echo $statusIcons[$visitor['status']]; ?> mr-2"></i>
                    <?php echo $statusLabels[$visitor['status']]; ?>
                </span>
            </div>
            
            <?php if ($visitor['status'] === 'in'): ?>
            <div class="flex space-x-2">
                <a href="<?php echo BASE_URL; ?>/visitors/exit/<?php echo $visitor['id']; ?>" 
                   class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg"
                   onclick="return confirm('¿Registrar salida de este visitante?')">
                    <i class="fas fa-sign-out-alt mr-2"></i>Registrar Salida
                </a>
                <?php if (Auth::hasRole(['admin', 'supervisor'])): ?>
                <a href="<?php echo BASE_URL; ?>/visitors/cancel/<?php echo $visitor['id']; ?>" 
                   class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg"
                   onclick="return confirm('¿Cancelar este registro de visitante?')">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Información del Visitante -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Datos Personales -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-user text-blue-600 mr-2"></i>Datos del Visitante
            </h2>
            
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Nombre:</dt>
                    <dd class="font-medium text-gray-900">
                        <?php echo htmlspecialchars($visitor['visitor_name'] ?: 'No proporcionado'); ?>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Placa:</dt>
                    <dd class="font-medium font-mono text-gray-900">
                        <?php echo htmlspecialchars($visitor['plate_number'] ?: 'No proporcionado'); ?>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Teléfono:</dt>
                    <dd class="font-medium text-gray-900">
                        <?php echo htmlspecialchars($visitor['phone'] ?: 'No proporcionado'); ?>
                    </dd>
                </div>
            </dl>
        </div>
        
        <!-- Fechas -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-clock text-blue-600 mr-2"></i>Tiempos
            </h2>
            
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Entrada:</dt>
                    <dd class="font-medium text-gray-900">
                        <?php echo date('d/m/Y H:i:s', strtotime($visitor['entry_datetime'])); ?>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Salida:</dt>
                    <dd class="font-medium text-gray-900">
                        <?php echo $visitor['exit_datetime'] 
                            ? date('d/m/Y H:i:s', strtotime($visitor['exit_datetime'])) 
                            : '-'; ?>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Tiempo en sitio:</dt>
                    <dd class="font-medium text-gray-900">
                        <?php
                        if ($visitor['exit_datetime']) {
                            $entry = new DateTime($visitor['entry_datetime']);
                            $exit = new DateTime($visitor['exit_datetime']);
                            $diff = $entry->diff($exit);
                            echo $diff->format('%H:%I:%S');
                        } elseif ($visitor['status'] === 'in') {
                            $entry = new DateTime($visitor['entry_datetime']);
                            $now = new DateTime();
                            $diff = $entry->diff($now);
                            echo $diff->format('%H:%I:%S') . ' (en curso)';
                        } else {
                            echo '-';
                        }
                        ?>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
    
    <!-- Fotografías -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-images text-blue-600 mr-2"></i>Fotografías
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Foto de Identificación -->
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Identificación</h3>
                <?php if (!empty($visitor['id_photo'])): ?>
                <a href="<?php echo BASE_URL . $visitor['id_photo']; ?>" target="_blank">
                    <img src="<?php echo BASE_URL . $visitor['id_photo']; ?>" 
                         alt="Foto de Identificación"
                         class="w-full h-48 object-cover rounded-lg border border-gray-200 hover:opacity-80 transition">
                </a>
                <?php else: ?>
                <div class="w-full h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                    <span class="text-gray-400">No disponible</span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Foto de Placas -->
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Placas</h3>
                <?php if (!empty($visitor['plate_photo'])): ?>
                <a href="<?php echo BASE_URL . $visitor['plate_photo']; ?>" target="_blank">
                    <img src="<?php echo BASE_URL . $visitor['plate_photo']; ?>" 
                         alt="Foto de Placas"
                         class="w-full h-48 object-cover rounded-lg border border-gray-200 hover:opacity-80 transition">
                </a>
                <?php else: ?>
                <div class="w-full h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                    <span class="text-gray-400">No disponible</span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Foto de Gafete -->
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Gafete</h3>
                <?php if (!empty($visitor['badge_photo'])): ?>
                <a href="<?php echo BASE_URL . $visitor['badge_photo']; ?>" target="_blank">
                    <img src="<?php echo BASE_URL . $visitor['badge_photo']; ?>" 
                         alt="Foto de Gafete"
                         class="w-full h-48 object-cover rounded-lg border border-gray-200 hover:opacity-80 transition">
                </a>
                <?php else: ?>
                <div class="w-full h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                    <span class="text-gray-400">No proporcionado</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Notas -->
    <?php if (!empty($visitor['notes'])): ?>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-sticky-note text-blue-600 mr-2"></i>Notas
        </h2>
        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($visitor['notes'])); ?></p>
    </div>
    <?php endif; ?>
</div>
