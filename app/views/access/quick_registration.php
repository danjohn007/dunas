<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Registro Rápido</h1>
            <p class="text-gray-600">Registrar entrada de unidad de manera rápida</p>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/units/create" 
               class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-truck mr-2"></i>Nueva Unidad
            </a>
        </div>
    </div>
    
    <!-- Detección Automática de Placas -->
    <div id="step1" class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-camera text-blue-600 mr-2"></i>Detección Automática de Placas
        </h2>
        
        <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-4">
            <p class="text-sm text-blue-800">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>Sistema automático:</strong> Las placas detectadas por la cámara se comparan automáticamente con las unidades registradas. El botón de registro solo aparece cuando hay una coincidencia.
            </p>
        </div>
        
        <!-- Comparación de Placas - Siempre Visible -->
        <div id="plateComparisonQuick">
            <div id="plate-compare-box" class="bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">
                    <i class="fas fa-sync-alt text-indigo-600 mr-2"></i>Comparación Automática
                </h3>
                
                <div class="grid grid-cols-2 gap-3">
                    <!-- Placa Detectada (de detected_plates) -->
                    <div class="bg-white rounded p-3 border border-gray-200">
                        <div class="text-xs text-gray-500 uppercase font-semibold mb-1">
                            <i class="fas fa-camera text-gray-400 mr-1"></i>Última Detección
                        </div>
                        <div id="plate-detected-text" class="text-lg font-bold text-gray-900 font-mono">
                            <span class="text-gray-400">Esperando...</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Desde cámara
                        </div>
                    </div>
                    
                    <!-- Placa Encontrada en units.plate_number -->
                    <div class="bg-white rounded p-3 border border-gray-200">
                        <div class="text-xs text-gray-500 uppercase font-semibold mb-1">
                            <i class="fas fa-database text-gray-400 mr-1"></i>Placa en Sistema
                        </div>
                        <div id="plate-saved-text" class="text-lg font-bold text-gray-900 font-mono">
                            <span class="text-gray-400">---</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Tabla: units
                        </div>
                    </div>
                </div>
                
                <!-- Estado -->
                <div class="mt-3 p-2 rounded bg-gray-50 border border-gray-200">
                    <div class="text-xs font-semibold text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>Estado: <span id="plate-compare-status">Esperando detección...</span>
                    </div>
                </div>
                
                <!-- Botón Refrescar -->
                <div class="mt-3 text-center">
                    <button type="button" id="refreshDetectionBtnQuick" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg text-sm">
                        <i class="fas fa-sync-alt mr-2"></i>Actualizar Detección
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Botón Buscar (aparece cuando hay coincidencia) -->
        <div id="searchButtonContainer" class="mt-4 hidden">
            <button type="button" id="searchBtn" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg">
                <i class="fas fa-search mr-2"></i>Buscar y Registrar Entrada
            </button>
        </div>
        
        <!-- Sección de Registro Manual (siempre visible) -->
        <div id="manualRegistrationContainer" class="mt-6 border-t border-gray-200 pt-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">
                <i class="fas fa-keyboard text-gray-600 mr-2"></i>Registro Manual (Si la cámara no detecta)
            </h3>
            
            <div class="flex gap-3">
                <div class="flex-1 relative">
                    <input type="text" id="manualPlateInput" 
                           placeholder="Escriba la placa manualmente (ej: ABC1059)"
                           class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500 uppercase"
                           maxlength="10"
                           autocomplete="off">
                    <!-- Autocomplete dropdown -->
                    <div id="plateAutocomplete" class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 hidden max-h-60 overflow-y-auto">
                    </div>
                </div>
                <button type="button" id="manualRegistrationBtn" 
                        class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-6 rounded-lg whitespace-nowrap">
                    <i class="fas fa-edit mr-2"></i>Registrar
                </button>
            </div>
            <p class="mt-2 text-xs text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>Use esta opción solo si la cámara no detecta la placa automáticamente
            </p>
        </div>
        
        <div id="searchResult" class="mt-4 hidden"></div>
    </div>
    
    <!-- Formulario de Registro -->
    <form method="POST" action="<?php echo BASE_URL; ?>/access/quickEntry" id="registrationForm" class="hidden">
        <input type="hidden" name="plate_number" id="plateNumber">
        <input type="hidden" name="unit_id" id="unitId" value="">
        
        <!-- Método de Pago -->
        <div id="paymentMethodSection" class="bg-white rounded-lg shadow-md p-6 mb-6 hidden">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-credit-card text-blue-600 mr-2"></i>Método de Pago
            </h2>
            
            <div>
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
            
            <!-- Selección de Vale (solo visible cuando se selecciona Vales) -->
            <div id="voucherSelection" class="mt-4 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Código del Vale <span class="text-red-500">*</span>
                </label>
                <input type="text" name="voucher_code" id="voucherCode"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 uppercase"
                       placeholder="Ej: R-0001 o escanee el código QR"
                       autocomplete="off">
                <p class="mt-1 text-xs text-gray-500">
                    <i class="fas fa-qrcode mr-1"></i>Puede escanear el código QR del vale o escribir el código manualmente
                </p>
                <div id="voucherStatus" class="mt-2 hidden">
                    <!-- Se mostrará información del vale aquí -->
                </div>
            </div>
        </div>
        
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
                            Teléfono/WhatsApp <span class="text-red-500">*</span>
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
                            Teléfono/WhatsApp <span class="text-red-500">*</span>
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
    const searchButtonContainer = document.getElementById('searchButtonContainer');
    const paymentMethodSection = document.getElementById('paymentMethodSection');
    
    // Variable global para almacenar la última placa detectada
    window.lastDetectedPlate = null;
    
    // Autocomplete para placas
    const manualPlateInput = document.getElementById('manualPlateInput');
    const plateAutocomplete = document.getElementById('plateAutocomplete');
    let autocompleteTimeout = null;
    
    manualPlateInput.addEventListener('input', function() {
        const query = this.value.trim().toUpperCase();
        this.value = query;
        
        // Clear previous timeout
        if (autocompleteTimeout) {
            clearTimeout(autocompleteTimeout);
        }
        
        if (query.length < 2) {
            plateAutocomplete.classList.add('hidden');
            plateAutocomplete.innerHTML = '';
            return;
        }
        
        // Debounce the search
        autocompleteTimeout = setTimeout(async () => {
            try {
                const response = await fetch(`<?php echo BASE_URL; ?>/access/searchPlates?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                if (data.success && data.plates && data.plates.length > 0) {
                    plateAutocomplete.innerHTML = data.plates.map(plate => `
                        <div class="autocomplete-item px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                             data-plate="${plate.plate_number}"
                             data-unit-id="${plate.id}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-mono font-semibold text-gray-900">${plate.plate_number}</span>
                                    <span class="text-sm text-gray-500 ml-2">${plate.brand || ''} ${plate.model || ''}</span>
                                </div>
                                <span class="text-xs text-gray-400">${parseInt(plate.capacity_liters || 0).toLocaleString()} L</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-building mr-1"></i>${plate.client_name || 'Sin cliente'}
                            </div>
                        </div>
                    `).join('');
                    
                    // Add click handlers
                    plateAutocomplete.querySelectorAll('.autocomplete-item').forEach(item => {
                        item.addEventListener('click', function() {
                            manualPlateInput.value = this.dataset.plate;
                            plateAutocomplete.classList.add('hidden');
                            // Trigger the registration button click
                            document.getElementById('manualRegistrationBtn').click();
                        });
                    });
                    
                    plateAutocomplete.classList.remove('hidden');
                } else {
                    plateAutocomplete.classList.add('hidden');
                }
            } catch (error) {
                console.error('Autocomplete error:', error);
                plateAutocomplete.classList.add('hidden');
            }
        }, 300);
    });
    
    // Hide autocomplete when clicking outside
    document.addEventListener('click', function(e) {
        if (!manualPlateInput.contains(e.target) && !plateAutocomplete.contains(e.target)) {
            plateAutocomplete.classList.add('hidden');
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
        const plate = window.lastDetectedPlate;
        
        if (!plate) {
            alert('No hay ninguna placa detectada para buscar');
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
                    
                    // Show payment method section
                    paymentMethodSection.classList.remove('hidden');
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
                    
                    // Show payment method section
                    paymentMethodSection.classList.remove('hidden');
                }
                
                searchResult.classList.remove('hidden');
                registrationForm.classList.remove('hidden');
                actionButtons.classList.remove('hidden');
            }
        } catch (error) {
            alert('Error al buscar la unidad: ' + error.message);
        } finally {
            searchBtn.disabled = false;
            searchBtn.innerHTML = '<i class="fas fa-search mr-2"></i>Buscar y Registrar Entrada';
        }
    });
    
    // Evento para el botón de registro manual
    const manualRegistrationBtn = document.getElementById('manualRegistrationBtn');
    
    // Convertir a mayúsculas mientras escribe
    manualPlateInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
    
    manualRegistrationBtn.addEventListener('click', async function() {
        // Usar placa manual o placa detectada
        let plate = manualPlateInput.value.trim();
        
        if (!plate) {
            plate = window.lastDetectedPlate;
        }
        
        if (!plate) {
            alert('Por favor escriba una placa o espere a que la cámara detecte una');
            manualPlateInput.focus();
            return;
        }
        
        // Validar formato básico de placa
        if (plate.length < 3) {
            alert('La placa debe tener al menos 3 caracteres');
            manualPlateInput.focus();
            return;
        }
        
        // Deshabilitar botón mientras busca
        manualRegistrationBtn.disabled = true;
        manualRegistrationBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Buscando...';
        
        try {
            // Primero buscar si la placa existe en el sistema
            const response = await fetch(`<?php echo BASE_URL; ?>/access/searchUnit?plate=${encodeURIComponent(plate)}`);
            const data = await response.json();
            
            if (data.success && data.exists) {
                // La placa EXISTE - usar flujo normal (igual que el botón azul)
                document.getElementById('plateNumber').value = plate;
                
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
                
                // Show payment method section
                paymentMethodSection.classList.remove('hidden');
                
                // Limpiar el input manual
                manualPlateInput.value = '';
            } else {
                // La placa NO existe - registro manual para nueva unidad
                document.getElementById('plateNumber').value = plate;
                
                searchResult.className = 'mt-4 p-4 bg-orange-50 border border-orange-200 rounded-lg';
                searchResult.innerHTML = `
                    <div class="flex items-center text-orange-800">
                        <i class="fas fa-edit text-2xl mr-3"></i>
                        <div>
                            <p class="font-semibold">Registro Manual</p>
                            <p class="text-sm">Placa: <strong>${plate}</strong> - Complete los datos para registrar la unidad</p>
                        </div>
                    </div>
                `;
                
                document.getElementById('unitId').value = '';
                step2Unit.classList.remove('hidden');
                step2Driver.classList.add('hidden');
                step3.classList.remove('hidden');
                step4.classList.remove('hidden');
                document.getElementById('capacityLiters').setAttribute('required', 'required');
                
                // Mostrar campos de registro nuevo
                newClientCheck.checked = true;
                newClientFields.classList.remove('hidden');
                newDriverCheck.checked = true;
                newDriverFields.classList.remove('hidden');
                
                // Show payment method section
                paymentMethodSection.classList.remove('hidden');
                
                // Limpiar el input manual
                manualPlateInput.value = '';
            }
            
            searchResult.classList.remove('hidden');
            registrationForm.classList.remove('hidden');
            actionButtons.classList.remove('hidden');
            
            // Scroll al formulario
            registrationForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
        } catch (error) {
            alert('Error al buscar la unidad: ' + error.message);
        } finally {
            manualRegistrationBtn.disabled = false;
            manualRegistrationBtn.innerHTML = '<i class="fas fa-edit mr-2"></i>Registrar';
        }
    });
});
</script>

<script src="<?php echo BASE_URL; ?>/assets/js/plate-compare.js"></script>
<script>
(function(){
  const compareUrl = "<?php echo BASE_URL; ?>/api/compare_plate.php";
  const latestPlateUrl = "<?php echo BASE_URL; ?>/api/get_latest_plate.php";
  
  console.log('Quick Registration - Compare URL:', compareUrl);
  console.log('Quick Registration - Latest Plate URL:', latestPlateUrl);
  
  const plateInput = document.querySelector('#plateSearch');
  const detectedEl = document.querySelector('#plate-detected-text');
  const statusEl = document.querySelector('#plate-compare-status');
  const containerEl = document.querySelector('#plate-compare-box');
  const savedPlateEl = document.querySelector('#plate-saved-text');
  const comparisonBox = document.querySelector('#plateComparisonQuick');

  function normalize(plate) { 
    if (!plate) return '';
    return plate.toUpperCase().trim().replace(/[^A-Z0-9]/g, '');
  }
  
  function setCompareUI({detected, ok, msg}) {
    console.log('Quick Registration - Updating UI:', { detected, ok, msg });
    
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
  }

  async function doCompareQuick() {
    try {
      console.log('Quick Registration - Getting latest detected plate');
      
      // Primero obtener la última placa detectada
      const latestResponse = await fetch(latestPlateUrl, {
        method: 'GET',
        cache: 'no-cache',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      
      if (!latestResponse.ok) {
        throw new Error(`HTTP ${latestResponse.status}`);
      }
      
      const latestData = await latestResponse.json();
      console.log('Quick Registration - Latest Plate:', latestData);
      
      // Actualizar placa detectada
      if (latestData.success && latestData.plate) {
        if (detectedEl) {
          detectedEl.innerHTML = `<span class="text-gray-900">${latestData.plate}</span>`;
        }
        
        // Ahora buscar si esa placa coincide con alguna unidad registrada
        const compareResponse = await fetch(compareUrl, {
          method: 'POST',
          body: new URLSearchParams({ unit_plate: latestData.plate }),
          cache: 'no-cache',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
          }
        });
        
        if (!compareResponse.ok) {
          throw new Error(`HTTP ${compareResponse.status}`);
        }
        
        const data = await compareResponse.json();
        console.log('Quick Registration - Compare Result:', data);

      if (data.success) {
        const detected = data.detected || 'Sin detección';
        const matchedPlate = data.matched_plate || null;
        const isMatch = data.is_match || false;
        const unitId = data.unit_id || null;
        
        // Guardar la placa detectada y el unit_id globalmente
        if (detected && detected !== 'Sin detección' && isMatch) {
          window.lastDetectedPlate = detected;
          window.lastMatchedUnitId = unitId;
        } else {
          window.lastDetectedPlate = detected !== 'Sin detección' ? detected : null;
          window.lastMatchedUnitId = null;
        }
        
        // Actualizar placa detectada (de detected_plates)
        if (detectedEl) {
          if (detected === 'Sin detección') {
            detectedEl.innerHTML = '<span class="text-gray-400">Sin detección</span>';
          } else {
            detectedEl.innerHTML = `<span class="text-gray-900">${detected}</span>`;
          }
        }
        
        // Actualizar placa encontrada en el sistema (de units.plate_number)
        if (savedPlateEl) {
          if (matchedPlate && isMatch) {
            savedPlateEl.innerHTML = `<span class="text-green-700">${matchedPlate}</span>`;
          } else {
            savedPlateEl.innerHTML = '<span class="text-gray-400">No registrada</span>';
          }
        }
        
        // Actualizar estado y mostrar/ocultar botones
        const searchBtnContainer = document.getElementById('searchButtonContainer');
        
        if (detected === 'Sin detección') {
          if (statusEl) statusEl.innerHTML = '<span class="text-gray-600">Esperando detección de cámara...</span>';
          if (containerEl) {
            containerEl.classList.remove('match-ok', 'match-bad');
            containerEl.classList.add('match-bad');
          }
          if (searchBtnContainer) searchBtnContainer.classList.add('hidden');
        } else if (isMatch) {
          if (statusEl) statusEl.innerHTML = '<span class="text-green-700"><i class="fas fa-check-circle mr-1"></i>Coincidencia encontrada - Lista para registrar</span>';
          if (containerEl) {
            containerEl.classList.remove('match-ok', 'match-bad');
            containerEl.classList.add('match-ok');
          }
          // Mostrar botón de búsqueda cuando hay coincidencia
          if (searchBtnContainer) searchBtnContainer.classList.remove('hidden');
        } else {
          if (statusEl) statusEl.innerHTML = '<span class="text-orange-600"><i class="fas fa-exclamation-triangle mr-1"></i>Placa detectada no está registrada - Puede registrar manualmente</span>';
          if (containerEl) {
            containerEl.classList.remove('match-ok', 'match-bad');
            containerEl.classList.add('match-bad');
          }
          // Ocultar botón de búsqueda cuando NO hay coincidencia
          if (searchBtnContainer) searchBtnContainer.classList.add('hidden');
        }
      } else {
        console.error('Quick Registration - Compare error:', data.error);
        // La placa está detectada pero no coincide con ninguna unidad
        if (statusEl) {
          statusEl.innerHTML = '<span class="text-orange-600"><i class="fas fa-exclamation-triangle mr-1"></i>Placa detectada pero no registrada en el sistema</span>';
        }
        if (savedPlateEl) {
          savedPlateEl.innerHTML = '<span class="text-gray-400">No registrada</span>';
        }
        window.lastDetectedPlate = latestData.plate;
        window.lastMatchedUnitId = null;
        const searchBtnContainer = document.getElementById('searchButtonContainer');
        if (searchBtnContainer) searchBtnContainer.classList.add('hidden');
      }
      } else {
        // No hay placas detectadas recientemente
        if (detectedEl) {
          detectedEl.innerHTML = '<span class="text-gray-400">Sin detección</span>';
        }
        if (statusEl) {
          statusEl.innerHTML = '<span class="text-gray-600">Esperando detección de cámara...</span>';
        }
        if (savedPlateEl) {
          savedPlateEl.innerHTML = '<span class="text-gray-400">---</span>';
        }
        window.lastDetectedPlate = null;
        window.lastMatchedUnitId = null;
        const searchBtnContainer = document.getElementById('searchButtonContainer');
        if (searchBtnContainer) searchBtnContainer.classList.add('hidden');
      }
      
    } catch (error) {
      console.error('Quick Registration - Comparison failed:', error);
      if (detectedEl) {
        detectedEl.innerHTML = '<span class="text-red-500">Error conexión</span>';
      }
      if (statusEl) {
        statusEl.innerHTML = '<span class="text-red-600">No se pudo consultar las detecciones</span>';
      }
      window.lastDetectedPlate = null;
      window.lastMatchedUnitId = null;
    }
  }

  // Botón refrescar detección manual
  const refreshBtnQuick = document.querySelector('#refreshDetectionBtnQuick');
  if (refreshBtnQuick) {
    refreshBtnQuick.addEventListener('click', () => {
      console.log('Quick Registration - Manual refresh triggered');
      doCompareQuick();
    });
  }

  // Ejecutar comparación inicial automática
  setTimeout(() => {
    console.log('Quick Registration - Initial auto comparison');
    doCompareQuick();
  }, 1000);

  // Refrescar automáticamente cada 3 segundos para mostrar nuevas detecciones
  setInterval(() => {
    console.log('Quick Registration - Auto refresh comparison');
    doCompareQuick();
  }, 3000);
})();
</script>

<!-- Script para mover imágenes FTP y registrar placas automáticamente -->
<script>
// === Autoejecutar move_ftp_images.php y register_new_plates.php cada 10 segundos ===

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

<!-- Script para validación de vales -->
<script>
// Mostrar/ocultar selección de vale según método de pago
document.getElementById('paymentMethod').addEventListener('change', function() {
    const voucherSelection = document.getElementById('voucherSelection');
    const voucherCode = document.getElementById('voucherCode');
    
    if (this.value === 'voucher') {
        voucherSelection.classList.remove('hidden');
        voucherCode.required = true;
    } else {
        voucherSelection.classList.add('hidden');
        voucherCode.required = false;
        voucherCode.value = '';
        document.getElementById('voucherStatus').classList.add('hidden');
    }
});

// Validar vale cuando se ingresa código
let voucherValidationTimeout;
document.getElementById('voucherCode').addEventListener('input', function() {
    clearTimeout(voucherValidationTimeout);
    const code = this.value.trim().toUpperCase();
    
    if (code.length < 3) {
        document.getElementById('voucherStatus').classList.add('hidden');
        return;
    }
    
    // Debounce para evitar muchas peticiones
    voucherValidationTimeout = setTimeout(() => {
        validateVoucherCode(code);
    }, 500);
});

async function validateVoucherCode(code) {
    const statusDiv = document.getElementById('voucherStatus');
    statusDiv.innerHTML = '<p class="text-sm text-gray-600"><i class="fas fa-spinner fa-spin mr-2"></i>Validando vale...</p>';
    statusDiv.classList.remove('hidden');
    
    try {
        // Primero intentar con el código directo
        let qrCode = code;
        
        // Si el código escaneado viene con el formato VALE:CODE:LITERS, extraer solo el código
        if (code.startsWith('VALE:')) {
            const parts = code.split(':');
            qrCode = code; // Usar el código QR completo
        }
        
        const formData = new FormData();
        formData.append('qr_code', qrCode);
        
        const response = await fetch('<?php echo BASE_URL; ?>/vouchers/validate', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success && data.voucher) {
            statusDiv.innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded p-3">
                    <p class="text-sm font-semibold text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>Vale válido
                    </p>
                    <div class="text-xs text-green-700 mt-1">
                        <div>Código: <span class="font-mono">${data.voucher.voucher_code}</span></div>
                        <div>Capacidad: ${parseInt(data.voucher.capacity_liters).toLocaleString()} litros</div>
                    </div>
                </div>
            `;
            // Guardar el código del vale validado
            document.getElementById('voucherCode').value = data.voucher.voucher_code;
        } else {
            statusDiv.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded p-3">
                    <p class="text-sm font-semibold text-red-800">
                        <i class="fas fa-times-circle mr-1"></i>${data.message || 'Vale no válido'}
                    </p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error validando vale:', error);
        statusDiv.innerHTML = `
            <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-1"></i>Error al validar vale
                </p>
            </div>
        `;
    }
}
</script>

<!-- Configuración para limpieza periódica de registros de placas detectadas -->
<?php if (isset($systemSettings['auto_cleanup_enabled']) && $systemSettings['auto_cleanup_enabled'] === '1'): ?>
<script>
window.CLEANUP_CONFIG = {
    intervalMinutes: <?php echo (int)($systemSettings['auto_cleanup_minutes'] ?? 15); ?>,
    url: "<?php echo BASE_URL; ?>/api/cleanup_detected_plates.php",
    viewName: "Quick Registration"
};
</script>
<script src="<?php echo BASE_URL; ?>/assets/js/detected-plates-cleanup.js"></script>
<?php else: ?>
<!-- Limpieza automática desactivada en configuración -->
<?php endif; ?>