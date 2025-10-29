<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Registrar Salida</h1>
        <p class="text-gray-600">Registrar salida de unidad</p>
    </div>
    
    <!-- Información del Acceso -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información del Acceso
        </h2>
        
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-500">Ticket</p>
                <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($access['ticket_code']); ?></p>
            </div>
            
            <div>
                <p class="text-sm text-gray-500">Cliente</p>
                <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($access['client_name']); ?></p>
            </div>
            
            <div>
                <p class="text-sm text-gray-500">Unidad</p>
                <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($access['plate_number']); ?></p>
            </div>
            
            <div>
                <p class="text-sm text-gray-500">Chofer</p>
                <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($access['driver_name']); ?></p>
            </div>
            
            <div>
                <p class="text-sm text-gray-500">Capacidad</p>
                <p class="text-lg font-semibold text-gray-900"><?php echo number_format($access['capacity_liters']); ?> L</p>
            </div>
            
            <div>
                <p class="text-sm text-gray-500">Hora de Entrada</p>
                <p class="text-lg font-semibold text-gray-900"><?php echo date('H:i', strtotime($access['entry_datetime'])); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Formulario de Salida -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-sign-out-alt text-red-600 mr-2"></i>Registro de Salida
        </h2>
        
        <form method="POST" action="<?php echo BASE_URL; ?>/access/registerExit/<?php echo $access['id']; ?>">
            <div class="mb-6">
                <label for="liters_supplied" class="block text-sm font-medium text-gray-700 mb-2">
                    Litros Suministrados <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" id="liters_supplied" name="liters_supplied" required
                           min="0" max="<?php echo $access['capacity_liters']; ?>" step="1"
                           class="w-full text-2xl font-bold rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 py-4"
                           placeholder="0">
                    <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-xl text-gray-500">
                        Litros
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Ingrese la cantidad de litros que se suministraron al cliente. 
                    Máximo: <?php echo number_format($access['capacity_liters']); ?> L
                </p>
            </div>
            
            <!-- Botones -->
            <div class="flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>/access" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-3 px-6 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" 
                        class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg">
                    <i class="fas fa-sign-out-alt mr-2"></i>Registrar Salida
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-focus en el campo de litros
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('liters_supplied').focus();
});
</script>
