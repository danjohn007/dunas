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
                <select name="client_id" id="clientSelect" required
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
                <select name="unit_id" id="unitSelect" required disabled
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100">
                    <option value="">-- Seleccione una unidad --</option>
                </select>
                <p id="unitHelpText" class="mt-1 text-xs text-gray-500">Primero seleccione un cliente para ver sus unidades</p>
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
                <select name="driver_id" id="driverSelect" required disabled
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100">
                    <option value="">-- Seleccione un chofer --</option>
                </select>
                <p id="driverHelpText" class="mt-1 text-xs text-gray-500">Primero seleccione un cliente para ver sus choferes</p>
            </div>
            
            <!-- Método de Pago -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Método de Pago <span class="text-red-500">*</span>
                </label>
                <select name="payment_method" id="paymentMethod" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="cash" selected>Efectivo</option>
                    <option value="voucher">Vales</option>
                    <option value="bank_transfer">Transferencia Bancaria</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">El método de pago se reflejará en el ticket y en el reporte financiero</p>
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
                    <li><i class="fas fa-check text-green-600 mr-2"></i>El costo se calculará automáticamente según la capacidad de la unidad</li>
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
                    <i class="fas fa-ticket-alt mr-2"></i>Generar Ticket
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

#plate-compare-box.match-ok {
    border-color: #16a34a !important;
    background: linear-gradient(to right, #dcfce7, #dbeafe) !important;
}

#plate-compare-box.match-bad {
    border-color: #9ca3af !important;
    background: linear-gradient(to right, #f3f4f6, #e5e7eb) !important;
}
</style>

<!-- Script to handle client selection and filter units/drivers -->
<script>
(function(){
    const clientSelect = document.getElementById('clientSelect');
    const unitSelect = document.getElementById('unitSelect');
    const driverSelect = document.getElementById('driverSelect');
    const unitHelpText = document.getElementById('unitHelpText');
    const driverHelpText = document.getElementById('driverHelpText');
    const baseUrl = "<?php echo BASE_URL; ?>";
    
    // Handle client selection change
    clientSelect.addEventListener('change', async function() {
        const clientId = this.value;
        
        // Reset and disable selects
        unitSelect.innerHTML = '<option value="">-- Cargando unidades... --</option>';
        driverSelect.innerHTML = '<option value="">-- Cargando choferes... --</option>';
        unitSelect.disabled = true;
        driverSelect.disabled = true;
        
        if (!clientId) {
            unitSelect.innerHTML = '<option value="">-- Seleccione una unidad --</option>';
            driverSelect.innerHTML = '<option value="">-- Seleccione un chofer --</option>';
            unitHelpText.textContent = 'Primero seleccione un cliente para ver sus unidades';
            driverHelpText.textContent = 'Primero seleccione un cliente para ver sus choferes';
            return;
        }
        
        try {
            const response = await fetch(`${baseUrl}/api/get_by_client.php?client_id=${clientId}&type=both`);
            const data = await response.json();
            
            if (data.success) {
                // Populate units
                unitSelect.innerHTML = '<option value="">-- Seleccione una unidad --</option>';
                if (data.units && data.units.length > 0) {
                    data.units.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.id;
                        option.textContent = `${unit.plate_number} (${unit.brand} ${unit.model} - ${parseInt(unit.capacity_liters).toLocaleString()} L)`;
                        option.dataset.capacity = unit.capacity_liters;
                        option.dataset.plate = unit.plate_number;
                        unitSelect.appendChild(option);
                    });
                    unitSelect.disabled = false;
                    unitHelpText.textContent = `${data.units.length} unidad(es) disponible(s)`;
                } else {
                    unitSelect.innerHTML = '<option value="">-- No hay unidades para este cliente --</option>';
                    unitHelpText.textContent = 'Este cliente no tiene unidades registradas';
                }
                
                // Populate drivers
                driverSelect.innerHTML = '<option value="">-- Seleccione un chofer --</option>';
                if (data.drivers && data.drivers.length > 0) {
                    data.drivers.forEach(driver => {
                        const option = document.createElement('option');
                        option.value = driver.id;
                        const license = driver.license_number ? `Lic: ${driver.license_number}` : 'Sin licencia';
                        option.textContent = `${driver.full_name} (${license})`;
                        driverSelect.appendChild(option);
                    });
                    driverSelect.disabled = false;
                    driverHelpText.textContent = `${data.drivers.length} chofer(es) disponible(s)`;
                } else {
                    driverSelect.innerHTML = '<option value="">-- No hay choferes para este cliente --</option>';
                    driverHelpText.textContent = 'Este cliente no tiene choferes registrados';
                }
            } else {
                console.error('Error fetching data:', data.error);
                unitHelpText.textContent = 'Error al cargar datos';
                driverHelpText.textContent = 'Error al cargar datos';
            }
        } catch (error) {
            console.error('Error:', error);
            unitSelect.innerHTML = '<option value="">-- Error al cargar --</option>';
            driverSelect.innerHTML = '<option value="">-- Error al cargar --</option>';
            unitHelpText.textContent = 'Error de conexión';
            driverHelpText.textContent = 'Error de conexión';
        }
    });
})();
</script>

<script src="<?php echo BASE_URL; ?>/assets/js/plate-compare.js"></script>
<script>
(function(){
  const compareUrl = "<?php echo BASE_URL; ?>/api/compare_plate.php";
  const latestPlateUrl = "<?php echo BASE_URL; ?>/api/get_latest_plate.php";
  
  console.log('Compare URL configured as:', compareUrl);
  console.log('Latest Plate URL:', latestPlateUrl);
  console.log('BASE_URL is:', "<?php echo BASE_URL; ?>");
  
  const unitSelect    = document.querySelector('#unitSelect');
  const detectedEl    = document.querySelector('#plate-detected-text');
  const statusEl      = document.querySelector('#plate-compare-status');
  const containerEl   = document.querySelector('#plate-compare-box');
  const savedPlateEl  = document.querySelector('#plate-saved-text');
  const detectionInfo = document.querySelector('#detectionInfo');
  const refreshBtn    = document.querySelector('#refreshDetectionBtn');
  const comparisonContainer = document.querySelector('#plateComparisonContainer');

  function setCompareUI({detected, ok, msg}) {
    console.log('Updating UI with:', { detected, ok, msg });
    
    if (detectedEl) {
      detectedEl.textContent = detected ?? 'Error';
    }
    
    if (statusEl) {
      statusEl.textContent = msg ?? (ok ? 'Coincide' : 'No coincide');
    }
    
    if (containerEl) {
      containerEl.classList.remove('match-ok', 'match-bad');
      containerEl.classList.add(ok ? 'match-ok' : 'match-bad');
    }
    
    // Agregar indicador visual adicional
    const comparisonResult = document.getElementById('comparisonResult');
    if (comparisonResult) {
      const resultDiv = comparisonResult.querySelector('.p-4');
      if (resultDiv) {
        resultDiv.className = `p-4 rounded-lg flex items-center ${
          ok ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'
        }`;
        
        const icon = resultDiv.querySelector('i');
        if (icon) {
          icon.className = `fas ${
            ok ? 'fa-check-circle text-green-600' : 'fa-exclamation-triangle text-red-600'
          } text-2xl mr-3`;
        }
      }
    }
  }

  // Función para obtener la última placa detectada
  async function getLatestPlate() {
    try {
      const response = await fetch(latestPlateUrl, {
        method: 'GET',
        cache: 'no-cache',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      
      if (!response.ok) return null;
      
      const data = await response.json();
      if (data.success && data.plate) {
        return {
          plate: data.plate,
          captured_at: data.captured_at,
          confidence: data.confidence
        };
      }
      return null;
    } catch (error) {
      console.error('Error getting latest plate:', error);
      return null;
    }
  }

  async function doCompare() {
    const unitId = unitSelect ? unitSelect.value : null;
    
    // Primero, obtener la última placa detectada
    const latestPlate = await getLatestPlate();
    
    // Actualizar UI con la última placa detectada
    if (latestPlate) {
      if (detectedEl) detectedEl.textContent = latestPlate.plate;
      if (detectionInfo) {
        const capturedAt = new Date(latestPlate.captured_at).toLocaleString('es-MX');
        detectionInfo.innerHTML = `Detectado: ${capturedAt}`;
        if (latestPlate.confidence) {
          detectionInfo.innerHTML += ` (${latestPlate.confidence}% confianza)`;
        }
      }
    } else {
      if (detectedEl) detectedEl.innerHTML = '<span class="text-gray-400">Sin detección</span>';
      if (detectionInfo) detectionInfo.textContent = 'Sin detecciones recientes';
    }
    
    // Si no hay unidad seleccionada, solo mostramos la placa detectada
    if (!unitId) {
      console.log('No unit selected, only showing latest plate');
      return;
    }

    try {
      console.log('Starting plate comparison for unit:', unitId);
      
      // Actualizar UI mientras se carga comparación
      if (statusEl) statusEl.textContent = 'Comparando...';
      
      const formData = new FormData();
      formData.append('unit_id', unitId);
      
      const response = await fetch(compareUrl, { 
        method: 'POST', 
        body: formData,
        cache: 'no-cache',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      console.log('Response status:', response.status);
      console.log('Response headers:', response.headers.get('content-type'));

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      // Verificar content-type
      const contentType = response.headers.get('content-type') || '';
      if (!contentType.includes('application/json')) {
        const text = await response.text();
        console.error('Non-JSON response received:', text.slice(0, 500));
        throw new Error('Respuesta no válida del servidor');
      }

      const data = await response.json();
      console.log('Comparison result:', data);

      if (data.success) {
        const detected = data.detected || 'Sin detección';
        const isMatch = data.is_match || false;
        
        // Personalizar el mensaje según el caso
        let message;
        if (detected === 'Placa no encontrada') {
          message = 'Placa de la unidad no encontrada';
        } else if (isMatch) {
          message = 'Las placas coinciden';
        } else {
          message = 'Las placas no coinciden';
        }
        
        console.log('API Response Details:', {
          detected: detected,
          unitPlate: data.unit_plate,
          isMatch: isMatch,
          normalizedDetected: data.normalized_detected,
          normalizedUnit: data.normalized_unit,
          message: message,
          apiMessage: data.message
        });
        
        setCompareUI({
          detected: detected,
          ok: isMatch,
          msg: message
        });
        
        // Actualizar información de detección
        if (detectionInfo) {
          if (data.detected === 'Placa no encontrada') {
            detectionInfo.innerHTML = `<span style="color: #dc3545;">Placa de la unidad no detectada recientemente</span>`;
          } else if (data.detected) {
            const capturedAt = data.captured_at ? new Date(data.captured_at).toLocaleString() : 'Fecha desconocida';
            detectionInfo.innerHTML = `Detectado: ${capturedAt}`;
            if (data.confidence) {
              detectionInfo.innerHTML += ` (${data.confidence}% confianza)`;
            }
          } else {
            detectionInfo.textContent = 'Sin detecciones recientes';
          }
        }
      } else {
        console.error('API returned error:', data.error);
        setCompareUI({
          detected: 'Error',
          ok: false,
          msg: data.error || 'Error en la comparación'
        });
        
        if (detectionInfo) {
          detectionInfo.textContent = 'Error al consultar detecciones';
        }
      }
      
    } catch (error) {
      console.error('Comparison failed:', error);
      setCompareUI({
        detected: 'Error',
        ok: false,
        msg: 'No se pudo consultar las detecciones'
      });
      
      if (detectionInfo) {
        detectionInfo.textContent = 'Error de conexión';
      }
    }
  }

  // Mostrar capacidad de la unidad seleccionada y cargar detección de placa
  unitSelect.addEventListener('change', async function() {
    const selectedOption = this.options[this.selectedIndex];
    const capacity = selectedOption.getAttribute('data-capacity');
    const plate = selectedOption.getAttribute('data-plate');
    
    console.log('Unit selection changed:', {
      unitId: this.value,
      plate: plate,
      capacity: capacity
    });
    
    if (capacity) {
      console.log('Capacidad de la unidad: ' + capacity + ' litros');
    }
    
    // Si se seleccionó una unidad, mostrar comparación y cargar detección
    if (plate && this.value) {
      comparisonContainer.classList.remove('hidden');
      savedPlateEl.textContent = plate;
      
      // Limpiar estado anterior
      if (detectedEl) detectedEl.textContent = 'Cargando...';
      if (statusEl) statusEl.textContent = 'Consultando...';
      if (detectionInfo) detectionInfo.textContent = 'Buscando detecciones...';
      
      // Realizar comparación
      await doCompare();
    } else {
      console.log('No plate or unit selected, hiding comparison');
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

// URLs - usar endpoint público en /api
const moverUrl = "<?php echo BASE_URL; ?>/api/move_ftp_images.php";
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
