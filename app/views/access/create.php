<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Registrar Entrada</h1>
        <p class="text-gray-600">Registrar nueva entrada de unidad al sistema</p>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/access/create" id="accessForm">
            
            <!-- Selección de Cliente -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Cliente <span class="text-red-500">*</span>
                </label>
                <select name="client_id" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Seleccione un cliente --</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['id']; ?>">
                            <?php echo htmlspecialchars($client['business_name']); ?> 
                            (<?php echo ucfirst($client['client_type']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Selección de Unidad -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Unidad (Pipa) <span class="text-red-500">*</span>
                </label>
                <select name="unit_id" id="unitSelect" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Seleccione una unidad --</option>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?php echo $unit['id']; ?>" 
                                data-capacity="<?php echo $unit['capacity_liters']; ?>">
                            <?php echo htmlspecialchars($unit['plate_number']); ?> 
                            (<?php echo $unit['brand']; ?> <?php echo $unit['model']; ?> - 
                            <?php echo number_format($unit['capacity_liters']); ?> L)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Selección de Chofer -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Chofer <span class="text-red-500">*</span>
                </label>
                <select name="driver_id" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Seleccione un chofer --</option>
                    <?php foreach ($drivers as $driver): ?>
                        <option value="<?php echo $driver['id']; ?>">
                            <?php echo htmlspecialchars($driver['full_name']); ?> 
                            (Lic: <?php echo htmlspecialchars($driver['license_number']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Información Adicional -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información
                </h3>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li><i class="fas fa-check text-green-600 mr-2"></i>Se generará automáticamente un código de ticket único</li>
                    <li><i class="fas fa-check text-green-600 mr-2"></i>Se registrará la fecha y hora de entrada actual</li>
                    <li><i class="fas fa-check text-green-600 mr-2"></i>El sistema abrirá la barrera automáticamente</li>
                    <li><i class="fas fa-check text-green-600 mr-2"></i>El estado inicial será "En Progreso"</li>
                </ul>
            </div>
            
            <!-- Área de estado de la barrera -->
            <div id="barrierStatus" class="hidden mb-6 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="loading-spinner mr-3"></div>
                    <span id="barrierStatusText">Abriendo barrera...</span>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/access" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" id="submitBtn"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-door-open mr-2"></i>Registrar Entrada
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
// Mostrar capacidad de la unidad seleccionada
document.getElementById('unitSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const capacity = selectedOption.getAttribute('data-capacity');
    if (capacity) {
        console.log('Capacidad de la unidad: ' + capacity + ' litros');
    }
});

// Interceptar el envío del formulario para abrir la barrera
document.getElementById('accessForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const barrierStatus = document.getElementById('barrierStatus');
    const barrierStatusText = document.getElementById('barrierStatusText');
    
    // Deshabilitar botón y mostrar estado
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
    barrierStatus.classList.remove('hidden');
    barrierStatus.className = 'mb-6 p-4 rounded-lg bg-blue-50 border border-blue-200';
    barrierStatusText.textContent = 'Abriendo barrera...';
    
    try {
        // Intentar abrir la barrera usando JavaScript
        console.log('🔓 Intentando abrir barrera antes de enviar formulario...');
        const result = await window.shellyControl.openBarrier();
        
        if (result.success) {
            barrierStatus.className = 'mb-6 p-4 rounded-lg bg-green-50 border border-green-200';
            barrierStatusText.innerHTML = '<i class="fas fa-check text-green-600 mr-2"></i>Barrera abierta exitosamente';
            console.log('✅ Barrera abierta, enviando formulario...');
        } else {
            barrierStatus.className = 'mb-6 p-4 rounded-lg bg-yellow-50 border border-yellow-200';
            barrierStatusText.innerHTML = '<i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>No se pudo abrir barrera automáticamente, pero se registrará el acceso';
            console.log('⚠️ Error en barrera, pero continuando con registro...');
        }
        
        // Esperar un momento para que el usuario vea el resultado
        await new Promise(resolve => setTimeout(resolve, 1000));
        
    } catch (error) {
        console.error('❌ Error al controlar barrera:', error);
        barrierStatus.className = 'mb-6 p-4 rounded-lg bg-yellow-50 border border-yellow-200';
        barrierStatusText.innerHTML = '<i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>Error de conexión con la barrera, se registrará el acceso';
        
        // Esperar un momento antes de continuar
        await new Promise(resolve => setTimeout(resolve, 1000));
    }
    
    // Ahora sí enviar el formulario
    barrierStatusText.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-600 mr-2"></i>Registrando en el sistema...';
    this.submit();
});
</script>
