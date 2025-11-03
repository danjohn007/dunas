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
        
        <form method="POST" action="<?php echo BASE_URL; ?>/access/registerExit/<?php echo $access['id']; ?>" id="exitForm">
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
            
            <!-- Área de estado de la barrera -->
            <div id="barrierStatus" class="hidden mb-6 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="loading-spinner mr-3"></div>
                    <span id="barrierStatusText">Cerrando barrera...</span>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>/access" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-3 px-6 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" id="submitBtn"
                        class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg">
                    <i class="fas fa-sign-out-alt mr-2"></i>Registrar Salida
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.loading-spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
// Auto-focus on the liters field
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('liters_supplied').focus();
});

// Interceptar el envío del formulario para cerrar la barrera
document.getElementById('exitForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const barrierStatus = document.getElementById('barrierStatus');
    const barrierStatusText = document.getElementById('barrierStatusText');
    
    // Deshabilitar botón y mostrar estado
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
    barrierStatus.classList.remove('hidden');
    barrierStatus.className = 'mb-6 p-4 rounded-lg bg-blue-50 border border-blue-200';
    barrierStatusText.textContent = 'Cerrando barrera...';
    
    try {
        // Intentar cerrar la barrera usando JavaScript
        console.log('🔒 Intentando cerrar barrera antes de enviar formulario...');
        const result = await window.shellyControl.closeBarrier();
        
        if (result.success) {
            barrierStatus.className = 'mb-6 p-4 rounded-lg bg-green-50 border border-green-200';
            barrierStatusText.innerHTML = '<i class="fas fa-check text-green-600 mr-2"></i>Barrera cerrada exitosamente';
            console.log('✅ Barrera cerrada, enviando formulario...');
        } else {
            barrierStatus.className = 'mb-6 p-4 rounded-lg bg-yellow-50 border border-yellow-200';
            barrierStatusText.innerHTML = '<i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>No se pudo cerrar barrera automáticamente, se registrará la salida';
            console.log('⚠️ Error en barrera:', result.error || result.message || 'Error desconocido');
        }
        
        // Esperar un momento para que el usuario vea el resultado
        await new Promise(resolve => setTimeout(resolve, 1000));
        
    } catch (error) {
        console.error('❌ Error al controlar barrera:', error);
        barrierStatus.className = 'mb-6 p-4 rounded-lg bg-yellow-50 border border-yellow-200';
        barrierStatusText.innerHTML = '<i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>Error de comunicación con la barrera, se registrará la salida';
        
        // Esperar un momento antes de continuar
        await new Promise(resolve => setTimeout(resolve, 1000));
    }
    
    // Ahora sí enviar el formulario
    barrierStatusText.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-600 mr-2"></i>Registrando en el sistema...';
    this.submit();
});
</script>
