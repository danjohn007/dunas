<!-- Vista de Generación de Vales -->
<div class="container mx-auto px-4 py-6">
    
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-ticket-alt text-blue-600 mr-2"></i>
            Generar Vales
        </h1>
        
        <a href="<?php echo BASE_URL; ?>/vouchers" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
    
    <!-- Descripción -->
    <div class="bg-blue-50 border-l-4 border-blue-600 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-600 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Instrucciones para generar vales</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Complete el formulario para generar vales consecutivos. Cada vale tendrá un código QR único que puede ser escaneado por el dispositivo HikVision DS-K1T502DBWX.</p>
                    <ul class="list-disc list-inside mt-2">
                        <li><strong>Serie:</strong> Identificador alfabético (Ej: R, A, B)</li>
                        <li><strong>Folio Inicial:</strong> Número del primer vale</li>
                        <li><strong>Cantidad:</strong> Cuántos vales generar (máximo 1000)</li>
                        <li><strong>Capacidad:</strong> Litros de agua por vale</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Formulario de generación -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form id="generateVouchersForm" class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Serie -->
                <div>
                    <label for="serie" class="block text-sm font-medium text-gray-700 mb-2">
                        Serie <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="serie" 
                           name="serie" 
                           required 
                           maxlength="10"
                           pattern="[A-Za-z]+"
                           placeholder="Ej: R, A, B"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase">
                    <p class="mt-1 text-sm text-gray-500">Solo letras, se convertirá a mayúsculas</p>
                </div>
                
                <!-- Folio Inicial -->
                <div>
                    <label for="folio_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                        Folio Inicial <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="folio_inicio" 
                           name="folio_inicio" 
                           required 
                           min="1"
                           value="1"
                           placeholder="Ej: 1, 100, 500"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="mt-1 text-sm text-gray-500">Número del primer vale</p>
                </div>
                
                <!-- Cantidad -->
                <div>
                    <label for="cantidad" class="block text-sm font-medium text-gray-700 mb-2">
                        Cantidad de Vales <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="cantidad" 
                           name="cantidad" 
                           required 
                           min="1"
                           max="1000"
                           value="1"
                           placeholder="Ej: 10, 50, 100"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="mt-1 text-sm text-gray-500">Máximo 1000 vales por generación</p>
                </div>
                
                <!-- Capacidad en Litros -->
                <div>
                    <label for="capacity_liters" class="block text-sm font-medium text-gray-700 mb-2">
                        Capacidad (Litros) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="capacity_liters" 
                           name="capacity_liters" 
                           required 
                           min="1"
                           placeholder="Ej: 10000, 20000"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="mt-1 text-sm text-gray-500">Litros de agua por vale</p>
                </div>
                
            </div>
            
            <!-- Vista previa -->
            <div id="preview" class="hidden bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Vista Previa</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Rango de vales:</span>
                        <span id="previewRange" class="font-semibold text-gray-900 ml-2"></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Total litros:</span>
                        <span id="previewTotalLiters" class="font-semibold text-gray-900 ml-2"></span>
                    </div>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/vouchers" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                    Cancelar
                </a>
                <button type="submit" 
                        id="generateBtn"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200 shadow-md">
                    <i class="fas fa-plus-circle mr-2"></i>Generar Vales
                </button>
            </div>
            
        </form>
    </div>
    
</div>

<!-- Modal de progreso -->
<div id="progressModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-8 border w-96 shadow-lg rounded-lg bg-white">
        <div class="text-center">
            <i class="fas fa-spinner fa-spin text-blue-600 text-5xl mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Generando Vales</h3>
            <p class="text-gray-600">Por favor espere...</p>
        </div>
    </div>
</div>

<script>
// Vista previa dinámica
document.querySelectorAll('#serie, #folio_inicio, #cantidad, #capacity_liters').forEach(input => {
    input.addEventListener('input', updatePreview);
});

function updatePreview() {
    const serie = document.getElementById('serie').value.toUpperCase();
    const folioInicio = parseInt(document.getElementById('folio_inicio').value) || 0;
    const cantidad = parseInt(document.getElementById('cantidad').value) || 0;
    const capacityLiters = parseInt(document.getElementById('capacity_liters').value) || 0;
    
    if (serie && folioInicio > 0 && cantidad > 0 && capacityLiters > 0) {
        const folioFin = folioInicio + cantidad - 1;
        const totalLiters = cantidad * capacityLiters;
        
        document.getElementById('previewRange').textContent = 
            `${serie}-${String(folioInicio).padStart(4, '0')} a ${serie}-${String(folioFin).padStart(4, '0')} (${cantidad} vales)`;
        document.getElementById('previewTotalLiters').textContent = 
            `${totalLiters.toLocaleString()} litros`;
        document.getElementById('preview').classList.remove('hidden');
    } else {
        document.getElementById('preview').classList.add('hidden');
    }
}

// Enviar formulario
document.getElementById('generateVouchersForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Mostrar modal de progreso
    document.getElementById('progressModal').classList.remove('hidden');
    document.getElementById('generateBtn').disabled = true;
    
    fetch('<?php echo BASE_URL; ?>/vouchers/generate', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Ocultar modal de progreso
        document.getElementById('progressModal').classList.add('hidden');
        document.getElementById('generateBtn').disabled = false;
        
        if (data.success) {
            // Construir IDs para imprimir
            const voucherIds = data.vouchers.map(v => v.id).join(',');
            
            // Preguntar si desea imprimir
            if (confirm(`✅ ${data.message}\n\n¿Desea imprimir los vales ahora?`)) {
                window.open('<?php echo BASE_URL; ?>/vouchers/print?ids=' + voucherIds, '_blank');
            }
            
            // Redirigir a la lista de vales
            setTimeout(() => {
                window.location.href = '<?php echo BASE_URL; ?>/vouchers';
            }, 1000);
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        document.getElementById('progressModal').classList.add('hidden');
        document.getElementById('generateBtn').disabled = false;
        console.error('Error:', error);
        alert('Error al generar los vales. Por favor intente nuevamente.');
    });
});

// Convertir serie a mayúsculas automáticamente
document.getElementById('serie').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});
</script>
