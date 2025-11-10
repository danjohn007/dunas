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
                                data-capacity="<?php echo $unit['capacity_liters']; ?>"
                                data-plate="<?php echo htmlspecialchars($unit['plate_number']); ?>">
                            <?php echo htmlspecialchars($unit['plate_number']); ?> 
                            (<?php echo $unit['brand']; ?> <?php echo $unit['model']; ?> - 
                            <?php echo number_format($unit['capacity_liters']); ?> L)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Comparación de Placas (ANPR) -->
            <div id="plateComparisonContainer" class="mb-6 hidden">
                <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-camera text-indigo-600 mr-2"></i>Comparación de Placas
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Placa Guardada -->
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <div class="text-xs text-gray-500 uppercase font-semibold mb-2">
                                Placa de Unidad Guardada
                            </div>
                            <div id="savedPlate" class="text-2xl font-bold text-gray-900 font-mono tracking-wider">
                                ---
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Según registro del sistema
                            </div>
                        </div>
                        
                        <!-- Placa Detectada -->
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <div class="text-xs text-gray-500 uppercase font-semibold mb-2">
                                Placa de Unidad Detectada
                            </div>
                            <div id="detectedPlate" class="text-2xl font-bold text-gray-900 font-mono tracking-wider">
                                <span class="text-gray-400">Cargando...</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                <span id="detectionInfo">Consultando cámara LPR...</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estado de Comparación -->
                    <div id="comparisonResult" class="mt-4 hidden">
                        <div id="matchResult" class="p-4 rounded-lg flex items-center">
                            <i id="matchIcon" class="fas fa-circle-check text-3xl mr-3"></i>
                            <div>
                                <div id="matchTitle" class="font-semibold text-lg"></div>
                                <div id="matchMessage" class="text-sm"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botón Refrescar -->
                    <div class="mt-4 text-center">
                        <button type="button" id="refreshDetectionBtn" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg">
                            <i class="fas fa-sync-alt mr-2"></i>Detectar Placa Nuevamente
                        </button>
                    </div>
                </div>
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
// Variables globales
let currentDetection = null;

// Mostrar capacidad de la unidad seleccionada y cargar detección de placa
document.getElementById('unitSelect').addEventListener('change', async function() {
    const selectedOption = this.options[this.selectedIndex];
    const capacity = selectedOption.getAttribute('data-capacity');
    const plate = selectedOption.getAttribute('data-plate');
    
    if (capacity) {
        console.log('Capacidad de la unidad: ' + capacity + ' litros');
    }
    
    // Si se seleccionó una unidad, mostrar comparación y cargar detección
    if (plate && this.value) {
        document.getElementById('plateComparisonContainer').classList.remove('hidden');
        document.getElementById('savedPlate').textContent = plate;
        await loadPlateDetection();
    } else {
        document.getElementById('plateComparisonContainer').classList.add('hidden');
        document.getElementById('savedPlate').textContent = '---';
    }
});

// Botón refrescar detección
document.getElementById('refreshDetectionBtn').addEventListener('click', async function(e) {
    e.preventDefault();
    const btn = this;
    const originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Ejecutando...';
    
    try {
        // 1) Ejecutar el mover del FTP
        const ftpResponse = await fetch(<?php echo json_encode(BASE_URL); ?> + '/api/run_mover_ftp.php', {
            method: 'POST',
            headers: { 'Accept': 'application/json' }
        });
        const ftpData = await ftpResponse.json();
        
        if (!ftpData.success) {
            throw new Error(ftpData.error || 'Fallo al mover imágenes');
        }
        
        // 2) Lanzar la validación que ya teníamos
        await loadPlateDetection();
        
    } catch (err) {
        console.error('Error en el proceso:', err);
        const detectedPlateEl = document.getElementById('detectedPlate');
        const detectionInfoEl = document.getElementById('detectionInfo');
        if (detectedPlateEl) {
            detectedPlateEl.innerHTML = '<span class="text-red-500">Error</span>';
        }
        if (detectionInfoEl) {
            detectionInfoEl.textContent = err.message || 'No se pudo completar la operación';
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
});

// Función para cargar detección de placa
async function loadPlateDetection() {
    const detectedPlateEl = document.getElementById('detectedPlate');
    const detectionInfoEl = document.getElementById('detectionInfo');
    const comparisonResultEl = document.getElementById('comparisonResult');
    
    // Mostrar estado de carga
    detectedPlateEl.innerHTML = '<span class="text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Detectando...</span>';
    detectionInfoEl.textContent = 'Consultando cámara LPR...';
    comparisonResultEl.classList.add('hidden');
    
    try {
        // Llamar a la API
        const response = await fetch(<?php echo json_encode(BASE_URL); ?> + '/api/anpr/latest.php');
        
        if (!response.ok) {
            throw new Error('Error al consultar API: ' + response.status);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Error desconocido');
        }
        
        currentDetection = data.detection;
        
        // Actualizar UI
        if (currentDetection && currentDetection.plate_text) {
            detectedPlateEl.innerHTML = `<span class="text-gray-900">${currentDetection.plate_text}</span>`;
            
            // Información de confianza
            let confidenceText = '';
            if (currentDetection.confidence) {
                const confidencePct = Math.round(currentDetection.confidence);
                confidenceText = ` (${confidencePct}% confianza)`;
            }
            detectionInfoEl.innerHTML = `Detectado por cámara LPR${confidenceText}`;
            
            // Comparar placas
            const savedPlate = document.getElementById('savedPlate').textContent;
            const isMatch = currentDetection.is_match || (savedPlate === currentDetection.plate_text);
            
            showComparisonResult(isMatch);
            
        } else {
            detectedPlateEl.innerHTML = '<span class="text-gray-400">Sin detección</span>';
            detectionInfoEl.textContent = 'No se detectó placa en los últimos segundos';
            comparisonResultEl.classList.add('hidden');
        }
        
    } catch (error) {
        console.error('Error al cargar detección:', error);
        detectedPlateEl.innerHTML = '<span class="text-red-500">Error</span>';
        detectionInfoEl.textContent = 'No se pudo consultar la cámara LPR';
        comparisonResultEl.classList.add('hidden');
    }
}

// Función para mostrar resultado de comparación
function showComparisonResult(isMatch) {
    const comparisonResultEl = document.getElementById('comparisonResult');
    const matchResultEl = document.getElementById('matchResult');
    const matchIconEl = document.getElementById('matchIcon');
    const matchTitleEl = document.getElementById('matchTitle');
    const matchMessageEl = document.getElementById('matchMessage');
    
    comparisonResultEl.classList.remove('hidden');
    
    if (isMatch) {
        // Match - verde
        matchResultEl.className = 'p-4 rounded-lg flex items-center bg-green-50 border-2 border-green-500';
        matchIconEl.className = 'fas fa-circle-check text-3xl mr-3 text-green-600';
        matchTitleEl.className = 'font-semibold text-lg text-green-800';
        matchTitleEl.textContent = '¡Placas coinciden!';
        matchMessageEl.className = 'text-sm text-green-700';
        matchMessageEl.textContent = 'La placa detectada por la cámara coincide con la unidad seleccionada.';
    } else {
        // No match - amarillo/gris
        matchResultEl.className = 'p-4 rounded-lg flex items-center bg-yellow-50 border-2 border-yellow-500';
        matchIconEl.className = 'fas fa-circle-exclamation text-3xl mr-3 text-yellow-600';
        matchTitleEl.className = 'font-semibold text-lg text-yellow-800';
        matchTitleEl.textContent = 'Las placas no coinciden';
        matchMessageEl.className = 'text-sm text-yellow-700';
        matchMessageEl.textContent = 'La placa detectada difiere de la unidad seleccionada. Verifique la información.';
    }
}

// Interceptar el envío del formulario para deshabilitar botón y evitar doble clic
document.getElementById('accessForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const barrierStatus = document.getElementById('barrierStatus');
    const barrierStatusText = document.getElementById('barrierStatusText');
    
    // Deshabilitar botón inmediatamente para evitar doble clic
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
    barrierStatus.classList.remove('hidden');
    barrierStatus.className = 'mb-6 p-4 rounded-lg bg-blue-50 border border-blue-200';
    barrierStatusText.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-600 mr-2"></i>Registrando entrada y abriendo barrera...';
    
    // Permitir que el formulario se envíe normalmente
    // El servidor manejará la apertura de la barrera con idempotencia
});
</script>
