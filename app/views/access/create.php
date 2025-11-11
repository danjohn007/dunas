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
                <div id="plate-compare-box" class="bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-camera text-indigo-600 mr-2"></i>Comparación de Placas
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Placa Guardada -->
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <div class="text-xs text-gray-500 uppercase font-semibold mb-2">
                                Placa de Unidad Guardada
                            </div>
                            <div id="plate-saved-text" class="text-2xl font-bold text-gray-900 font-mono tracking-wider">
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
                            <div id="plate-detected-text" class="text-2xl font-bold text-gray-900 font-mono tracking-wider">
                                <span class="text-gray-400">Cargando...</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                <span id="detectionInfo">Consultando cámara LPR...</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estado de Comparación -->
                    <div id="comparisonResult" class="mt-4">
                        <div class="p-4 rounded-lg flex items-center bg-gray-50 border border-gray-200">
                            <i class="fas fa-info-circle text-2xl mr-3 text-gray-500"></i>
                            <div>
                                <div class="font-semibold text-gray-700">Estado de Comparación</div>
                                <div id="plate-compare-status" class="text-sm text-gray-600">Esperando comparación...</div>
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

<script src="<?php echo BASE_URL; ?>/assets/js/plate-compare.js"></script>
<script>
(function(){
  const compareUrl = "<?php echo BASE_URL; ?>/api/compare_plate.php";

  const unitSelect    = document.querySelector('#unitSelect');
  const detectedEl    = document.querySelector('#plate-detected-text');
  const statusEl      = document.querySelector('#plate-compare-status');
  const containerEl   = document.querySelector('#plate-compare-box');
  const savedPlateEl  = document.querySelector('#plate-saved-text');
  const detectionInfo = document.querySelector('#detectionInfo');
  const refreshBtn    = document.querySelector('#refreshDetectionBtn');
  const comparisonContainer = document.querySelector('#plateComparisonContainer');

  async function doCompare() {
    const unitId = unitSelect ? unitSelect.value : null;
    if (!unitId) return;

    try {
      const data = await PlateCompare.comparePlate({ unitId, compareUrl });
      if (data.success) {
        PlateCompare.renderPlateComparison({
          detected: data.detected,
          unitPlate: data.unit_plate,
          isMatch: data.is_match,
          detectedEl, statusEl, containerEl
        });
        
        // Update detection info
        if (detectionInfo && data.detected) {
          detectionInfo.textContent = 'Detectado por cámara LPR';
        } else if (detectionInfo) {
          detectionInfo.textContent = 'Sin detección reciente';
        }
      }
    } catch (e) {
      if (detectedEl) detectedEl.textContent = "Error";
      if (statusEl)   statusEl.textContent = "No se pudo comparar";
      console.warn(e);
    }
  }

  // Mostrar capacidad de la unidad seleccionada y cargar detección de placa
  unitSelect.addEventListener('change', async function() {
    const selectedOption = this.options[this.selectedIndex];
    const capacity = selectedOption.getAttribute('data-capacity');
    const plate = selectedOption.getAttribute('data-plate');
    
    if (capacity) {
      console.log('Capacidad de la unidad: ' + capacity + ' litros');
    }
    
    // Si se seleccionó una unidad, mostrar comparación y cargar detección
    if (plate && this.value) {
      comparisonContainer.classList.remove('hidden');
      savedPlateEl.textContent = plate;
      await doCompare();
    } else {
      comparisonContainer.classList.add('hidden');
      savedPlateEl.textContent = '---';
    }
  });

  // Botón refrescar detección
  if (refreshBtn) refreshBtn.addEventListener('click', doCompare);

  // Ejecuta cada 8 segundos
  setInterval(doCompare, 8000);
})();

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

<script>
// === Autoejecutar mover_ftp_a_public.php y register_new_plates.php cada 10 segundos ===

// URLs
const moverUrl = "https://fix360.app/dunas/Imagenes/mover_ftp_a_public.php";
const registrarUrl = "<?php echo BASE_URL; ?>/api/register_new_plates.php";

// función que llama al script de mover imágenes (sin interrumpir al usuario)
async function autoRunMoverFTP() {
  try {
    // hacemos una petición GET silenciosa
    const res = await fetch(moverUrl, { method: "GET", cache: "no-store" });
    if (!res.ok) {
      console.warn("⚠️ mover_ftp_a_public.php devolvió un error:", res.status);
      return;
    }
    console.log("🔁 mover_ftp_a_public.php ejecutado correctamente");
  } catch (err) {
    console.error("❌ Error ejecutando mover_ftp_a_public.php:", err);
  }
}

// función que llama al endpoint de registro de placas
async function autoRegisterNewPlates() {
  try {
    const res = await fetch(registrarUrl, { 
      method: "POST", 
      headers: { "Accept": "application/json" },
      cache: "no-store"
    });
    
    if (!res.ok) {
      console.warn("⚠️ register_new_plates.php devolvió un error:", res.status);
      return;
    }
    
    const data = await res.json();
    if (!data.success) {
      console.warn("⚠️ Error registrando placas:", data.error);
      return;
    }
    
    // Log opcional: solo mostrar cuando se inserten placas
    if (data.inserted > 0) {
      console.log(`✅ Detectadas/insertadas: ${data.inserted} placas`);
    }
  } catch (err) {
    console.error("❌ Error registrando placas:", err);
  }
}

// Ejecutar secuencialmente al cargar la página
(async () => {
  await autoRunMoverFTP();
  await autoRegisterNewPlates();
})();

// Repetir cada 10 segundos (10000 ms)
setInterval(async () => {
  await autoRunMoverFTP();
  await autoRegisterNewPlates();
}, 10000);
</script>
