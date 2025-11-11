<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Registro Rápido</h1>
        <p class="text-gray-600">Registrar entrada de unidad de manera rápida</p>
    </div>
    
    <!-- Paso 1: Buscar Unidad -->
    <div id="step1" class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-search text-blue-600 mr-2"></i>Paso 1: Buscar Unidad
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Número de Placa <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <input type="text" id="plateSearch" 
                           class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 uppercase"
                           placeholder="Ej: ABC-123"
                           autocomplete="off">
                    <button type="button" id="searchBtn" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">
                        <i class="fas fa-search mr-2"></i>Buscar
                    </button>
                </div>
            </div>
        </div>
        
        <div id="searchResult" class="mt-4 hidden"></div>
        
        <!-- Comparación de Placas -->
        <div id="plateComparisonQuick" class="mt-4 hidden">
            <div id="plate-compare-box" class="bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">
                    <i class="fas fa-camera text-indigo-600 mr-2"></i>Comparación de Placas
                </h3>
                
                <div class="grid grid-cols-2 gap-3">
                    <!-- Placa Ingresada -->
                    <div class="bg-white rounded p-3 border border-gray-200">
                        <div class="text-xs text-gray-500 uppercase font-semibold mb-1">
                            Placa Ingresada
                        </div>
                        <div id="plate-saved-text" class="text-lg font-bold text-gray-900 font-mono">
                            ---
                        </div>
                    </div>
                    
                    <!-- Placa Detectada -->
                    <div class="bg-white rounded p-3 border border-gray-200">
                        <div class="text-xs text-gray-500 uppercase font-semibold mb-1">
                            Placa Detectada
                        </div>
                        <div id="plate-detected-text" class="text-lg font-bold text-gray-900 font-mono">
                            <span class="text-gray-400">...</span>
                        </div>
                    </div>
                </div>
                
                <!-- Estado -->
                <div class="mt-3 p-2 rounded bg-gray-50 border border-gray-200">
                    <div class="text-xs font-semibold text-gray-600">
                        Estado: <span id="plate-compare-status">Esperando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Formulario de Registro -->
    <form method="POST" action="<?php echo BASE_URL; ?>/access/quickEntry" id="registrationForm" class="hidden">
        <input type="hidden" name="plate_number" id="plateNumber">
        <input type="hidden" name="unit_id" id="unitId" value="">
        
        <!-- Paso 2: Datos de la Unidad (si no existe) -->
        <div id="step2Unit" class="bg-white rounded-lg shadow-md p-6 mb-6 hidden">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-truck text-blue-600 mr-2"></i>Paso 2: Datos de la Unidad
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Capacidad (Litros) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="capacity_liters" id="capacityLiters" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ej: 20000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Marca
                    </label>
                    <input type="text" name="brand" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ej: Kenworth">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Modelo
                    </label>
                    <input type="text" name="model" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Ej: T800">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Año
                    </label>
                    <input type="number" name="year" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           value="<?php echo date('Y'); ?>"
                           placeholder="<?php echo date('Y'); ?>">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Número de Serie
                    </label>
                    <input type="text" name="serial_number" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Número de serie del vehículo">
                </div>
            </div>
        </div>
        
        <!-- Paso 2b: Selección de Chofer (cuando la unidad existe) -->
        <div id="step2Driver" class="bg-white rounded-lg shadow-md p-6 mb-6 hidden">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-user text-blue-600 mr-2"></i>Paso 2: Seleccionar Chofer
            </h2>
            
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Chofer <span class="text-red-500">*</span>
                    </label>
                    <select name="driver_id_existing" id="driverIdExisting"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Seleccione un chofer</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Seleccione el chofer para esta entrada</p>
                </div>
            </div>
        </div>
        
        <!-- Paso 3: Datos del Cliente -->
        <div id="step3" class="bg-white rounded-lg shadow-md p-6 mb-6 hidden">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-building text-blue-600 mr-2"></i>Paso 3: Datos del Cliente
            </h2>
            
            <input type="hidden" name="client_id" id="clientId" value="">
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" id="newClientCheck" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Registrar nuevo cliente</span>
                </label>
            </div>
            
            <div id="newClientFields" class="hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre de la Empresa <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="client_name" id="clientName"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Nombre del cliente">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Teléfono <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="client_phone" id="clientPhone"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Número de teléfono">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            RFC/CURP
                        </label>
                        <input type="text" name="client_rfc" id="clientRfc"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="RFC o CURP">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tipo de Cliente
                        </label>
                        <select name="client_type" id="clientType"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="commercial">Comercial</option>
                            <option value="residential">Residencial</option>
                            <option value="industrial">Industrial</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Dirección
                        </label>
                        <input type="text" name="client_address" id="clientAddress"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Dirección del cliente">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Paso 4: Datos del Chofer -->
        <div id="step4" class="bg-white rounded-lg shadow-md p-6 mb-6 hidden">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-user text-blue-600 mr-2"></i>Paso 4: Datos del Chofer
            </h2>
            
            <input type="hidden" name="driver_id" id="driverId" value="">
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" id="newDriverCheck" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Registrar nuevo chofer</span>
                </label>
            </div>
            
            <div id="newDriverFields" class="hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="driver_name" id="driverName"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Nombre del chofer">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Teléfono <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="driver_phone" id="driverPhone"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Número de teléfono">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Número de Licencia
                        </label>
                        <input type="text" name="driver_license" id="driverLicense"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Número de licencia">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Vigencia de Licencia
                        </label>
                        <input type="date" name="driver_license_expiry" id="driverLicenseExpiry"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botones de Acción -->
        <div id="actionButtons" class="flex justify-end space-x-4 hidden">
            <a href="<?php echo BASE_URL; ?>/access" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            <button type="submit" 
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-door-open mr-2"></i>Registrar Entrada
            </button>
        </div>
    </form>
</div>

<style>
#plate-compare-box.match-ok {
    border-color: #16a34a !important;
    background: linear-gradient(to right, #dcfce7, #dbeafe) !important;
}

#plate-compare-box.match-bad {
    border-color: #9ca3af !important;
    background: linear-gradient(to right, #f3f4f6, #e5e7eb) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const plateSearch = document.getElementById('plateSearch');
    const searchBtn = document.getElementById('searchBtn');
    const searchResult = document.getElementById('searchResult');
    const registrationForm = document.getElementById('registrationForm');
    const step2Unit = document.getElementById('step2Unit');
    const step2Driver = document.getElementById('step2Driver');
    const step3 = document.getElementById('step3');
    const step4 = document.getElementById('step4');
    const actionButtons = document.getElementById('actionButtons');
    const newClientCheck = document.getElementById('newClientCheck');
    const newClientFields = document.getElementById('newClientFields');
    const newDriverCheck = document.getElementById('newDriverCheck');
    const newDriverFields = document.getElementById('newDriverFields');
    
    // Convertir a mayúsculas automáticamente
    plateSearch.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
    
    // Buscar al presionar Enter
    plateSearch.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchBtn.click();
        }
    });
    
    // Toggle para nuevo cliente
    newClientCheck.addEventListener('change', function() {
        if (this.checked) {
            newClientFields.classList.remove('hidden');
            document.getElementById('clientName').setAttribute('required', 'required');
            document.getElementById('clientPhone').setAttribute('required', 'required');
            document.getElementById('clientId').value = '';
        } else {
            newClientFields.classList.add('hidden');
            document.getElementById('clientName').removeAttribute('required');
            document.getElementById('clientPhone').removeAttribute('required');
        }
    });
    
    // Toggle para nuevo chofer
    newDriverCheck.addEventListener('change', function() {
        if (this.checked) {
            newDriverFields.classList.remove('hidden');
            document.getElementById('driverName').setAttribute('required', 'required');
            document.getElementById('driverPhone').setAttribute('required', 'required');
            document.getElementById('driverId').value = '';
        } else {
            newDriverFields.classList.add('hidden');
            document.getElementById('driverName').removeAttribute('required');
            document.getElementById('driverPhone').removeAttribute('required');
        }
    });
    
    searchBtn.addEventListener('click', async function() {
        const plate = plateSearch.value.trim();
        
        if (!plate) {
            alert('Por favor ingrese un número de placa');
            return;
        }
        
        searchBtn.disabled = true;
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Buscando...';
        
        try {
            const response = await fetch(`<?php echo BASE_URL; ?>/access/searchUnit?plate=${encodeURIComponent(plate)}`);
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('plateNumber').value = plate;
                
                if (data.exists) {
                    // Unidad existe - precargar datos del último registro
                    let infoHtml = `
                        <div class="flex items-center text-green-800">
                            <i class="fas fa-check-circle text-2xl mr-3"></i>
                            <div>
                                <p class="font-semibold">Unidad encontrada</p>
                                <p class="text-sm">Placa: ${data.unit.plate_number} - Capacidad: ${parseInt(data.unit.capacity_liters).toLocaleString()} L</p>
                    `;
                    
                    if (data.lastEntry) {
                        infoHtml += `
                                <p class="text-sm mt-1">Cliente: ${data.lastEntry.client_name || 'N/A'}</p>
                                <p class="text-sm">Último chofer: ${data.lastEntry.driver_name || 'N/A'}</p>
                        `;
                    }
                    
                    infoHtml += `
                            </div>
                        </div>
                    `;
                    
                    searchResult.className = 'mt-4 p-4 bg-green-50 border border-green-200 rounded-lg';
                    searchResult.innerHTML = infoHtml;
                    
                    document.getElementById('unitId').value = data.unit.id;
                    document.getElementById('capacityLiters').value = data.unit.capacity_liters;
                    step2Unit.classList.add('hidden');
                    
                    // Precargar cliente del último registro
                    if (data.lastEntry && data.lastEntry.client_id) {
                        document.getElementById('clientId').value = data.lastEntry.client_id;
                        newClientCheck.checked = false;
                        newClientFields.classList.add('hidden');
                    }
                    
                    // Mostrar selector de choferes
                    if (data.drivers && data.drivers.length > 0) {
                        const driverSelect = document.getElementById('driverIdExisting');
                        driverSelect.innerHTML = '<option value="">Seleccione un chofer</option>';
                        
                        data.drivers.forEach(driver => {
                            const option = document.createElement('option');
                            option.value = driver.id;
                            option.textContent = driver.full_name;
                            
                            // Preseleccionar el último chofer usado
                            if (data.lastEntry && parseInt(driver.id) === parseInt(data.lastEntry.driver_id)) {
                                option.selected = true;
                                document.getElementById('driverId').value = driver.id;
                            }
                            
                            driverSelect.appendChild(option);
                        });
                        
                        // Manejar cambio de chofer
                        driverSelect.addEventListener('change', function() {
                            document.getElementById('driverId').value = this.value;
                        });
                        
                        step2Driver.classList.remove('hidden');
                        step3.classList.add('hidden');
                        step4.classList.add('hidden');
                    } else {
                        step2Driver.classList.add('hidden');
                        step3.classList.remove('hidden');
                        step4.classList.remove('hidden');
                        newClientCheck.checked = true;
                        newClientFields.classList.remove('hidden');
                        newDriverCheck.checked = true;
                        newDriverFields.classList.remove('hidden');
                    }
                } else {
                    // Unidad no existe - mostrar formulario completo
                    searchResult.className = 'mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg';
                    searchResult.innerHTML = `
                        <div class="flex items-center text-yellow-800">
                            <i class="fas fa-exclamation-triangle text-2xl mr-3"></i>
                            <div>
                                <p class="font-semibold">Unidad no encontrada</p>
                                <p class="text-sm">Complete los datos para dar de alta la unidad, cliente y chofer</p>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('unitId').value = '';
                    step2Unit.classList.remove('hidden');
                    step2Driver.classList.add('hidden');
                    step3.classList.remove('hidden');
                    step4.classList.remove('hidden');
                    document.getElementById('capacityLiters').setAttribute('required', 'required');
                    
                    // Mostrar campos de registro
                    newClientCheck.checked = true;
                    newClientFields.classList.remove('hidden');
                    newDriverCheck.checked = true;
                    newDriverFields.classList.remove('hidden');
                }
                
                searchResult.classList.remove('hidden');
                registrationForm.classList.remove('hidden');
                actionButtons.classList.remove('hidden');
            }
        } catch (error) {
            alert('Error al buscar la unidad: ' + error.message);
        } finally {
            searchBtn.disabled = false;
            searchBtn.innerHTML = '<i class="fas fa-search mr-2"></i>Buscar';
        }
    });
});
</script>

<script src="<?php echo BASE_URL; ?>/assets/js/plate-compare.js"></script>
<script>
(function(){
  // Use absolute URL with module number (adjust '5' if using different instance)
  const COMPARE_URL = "https://fix360.app/dunas/dunasshelly/5/api/compare_plate.php";
  const plateInput   = document.querySelector('#plateSearch');
  const detectedEl   = document.querySelector('#plate-detected-text');
  const statusEl     = document.querySelector('#plate-compare-status');
  const containerEl  = document.querySelector('#plate-compare-box');
  const savedPlateEl = document.querySelector('#plate-saved-text');
  const comparisonBox = document.querySelector('#plateComparisonQuick');

  function normalize(p){ return (p||'').toUpperCase().replace(/[^A-Z0-9]/g,''); }
  
  function setUI({detected, ok, msg}) {
    if (detectedEl) detectedEl.textContent = detected ?? 'Error';
    if (statusEl)   statusEl.textContent   = msg ?? (ok ? 'Coincide' : 'No coincide');
    if (containerEl){
      containerEl.classList.remove('match-ok','match-bad');
      containerEl.classList.add(ok ? 'match-ok' : 'match-bad');
    }
  }

  async function doCompareQuick() {
    const plate = normalize(plateInput?.value);
    if (!plate) return;

    try {
      const fd = new FormData();
      fd.append('unit_plate', plate);

      const res = await fetch(COMPARE_URL, { method:'POST', body: fd, cache:'no-store' });
      
      // Check content-type before parsing JSON to detect if server returned HTML
      const ct = res.headers.get('content-type') || '';
      if (!ct.includes('application/json')) {
        const text = await res.text();
        console.warn('COMPARE non-JSON:', { url: COMPARE_URL, status: res.status, ct, sample: text.slice(0, 400) });
        setUI({detected:'Error', ok:false, msg:'No se pudo comparar (respuesta no JSON)'});
        return;
      }

      const data = await res.json();
      if (data.success) {
        const ok = normalize(data.detected) === plate && !!data.detected;
        setUI({detected: data.detected, ok});
      } else {
        setUI({detected:'Error', ok:false, msg:'No se pudo comparar'});
      }
    } catch (e) {
      console.warn(e);
      setUI({detected:'Error', ok:false, msg:'No se pudo comparar'});
    }
  }

  // 1) Ejecuta cuando el usuario escribe la placa
  if (plateInput) {
    plateInput.addEventListener('input', () => {
      const plate = normalize(plateInput.value);
      
      // Show comparison box if plate is entered
      if (plate && plate.length >= 3) {
        comparisonBox.classList.remove('hidden');
        savedPlateEl.textContent = plateInput.value.toUpperCase();
        
        // Pequeño debounce
        clearTimeout(window.__qr_t);
        window.__qr_t = setTimeout(doCompareQuick, 400);
      } else {
        comparisonBox.classList.add('hidden');
      }
    });
  }

  // 2) Ejecuta al cargar (por si ya viene prellenado)
  if (plateInput && plateInput.value) {
    const plate = normalize(plateInput.value);
    if (plate && plate.length >= 3) {
      comparisonBox.classList.remove('hidden');
      savedPlateEl.textContent = plateInput.value.toUpperCase();
      doCompareQuick();
    }
  }

  // 3) Opcional: refrescar cada 8 segundos
  setInterval(() => {
    const plate = normalize(plateInput ? plateInput.value : '');
    if (plate && plate.length >= 3) {
      doCompareQuick();
    }
  }, 8000);
})();
</script>
