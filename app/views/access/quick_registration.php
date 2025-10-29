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
        
        <!-- Paso 3: Datos del Cliente -->
        <div id="step3" class="bg-white rounded-lg shadow-md p-6 mb-6 hidden">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-building text-blue-600 mr-2"></i>Paso 3: Datos del Cliente
            </h2>
            
            <input type="hidden" name="client_id" id="clientId" value="">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre de la Empresa <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="client_name" id="clientName" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Nombre del cliente">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Teléfono <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" name="client_phone" id="clientPhone" required
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
        
        <!-- Paso 4: Datos del Chofer -->
        <div id="step4" class="bg-white rounded-lg shadow-md p-6 mb-6 hidden">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-user text-blue-600 mr-2"></i>Paso 4: Datos del Chofer
            </h2>
            
            <input type="hidden" name="driver_id" id="driverId" value="">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="driver_name" id="driverName" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Nombre del chofer">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Teléfono <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" name="driver_phone" id="driverPhone" required
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const plateSearch = document.getElementById('plateSearch');
    const searchBtn = document.getElementById('searchBtn');
    const searchResult = document.getElementById('searchResult');
    const registrationForm = document.getElementById('registrationForm');
    const step2Unit = document.getElementById('step2Unit');
    const step3 = document.getElementById('step3');
    const step4 = document.getElementById('step4');
    const actionButtons = document.getElementById('actionButtons');
    
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
                    // Unidad existe
                    searchResult.className = 'mt-4 p-4 bg-green-50 border border-green-200 rounded-lg';
                    searchResult.innerHTML = `
                        <div class="flex items-center text-green-800">
                            <i class="fas fa-check-circle text-2xl mr-3"></i>
                            <div>
                                <p class="font-semibold">Unidad encontrada</p>
                                <p class="text-sm">Placa: ${data.unit.plate_number} - Capacidad: ${parseInt(data.unit.capacity_liters).toLocaleString()} L</p>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('unitId').value = data.unit.id;
                    document.getElementById('capacityLiters').value = data.unit.capacity_liters;
                    step2Unit.classList.add('hidden');
                } else {
                    // Unidad no existe - mostrar formulario
                    searchResult.className = 'mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg';
                    searchResult.innerHTML = `
                        <div class="flex items-center text-yellow-800">
                            <i class="fas fa-exclamation-triangle text-2xl mr-3"></i>
                            <div>
                                <p class="font-semibold">Unidad no encontrada</p>
                                <p class="text-sm">Complete los datos para dar de alta la unidad</p>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('unitId').value = '';
                    step2Unit.classList.remove('hidden');
                    document.getElementById('capacityLiters').setAttribute('required', 'required');
                }
                
                searchResult.classList.remove('hidden');
                registrationForm.classList.remove('hidden');
                step3.classList.remove('hidden');
                step4.classList.remove('hidden');
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
