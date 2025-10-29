<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Escanear Salida</h1>
        <p class="text-gray-600">Escanee el código de barras del ticket para registrar la salida</p>
    </div>
    
    <!-- Escáner de Código de Barras -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="text-center mb-6">
            <div class="inline-block bg-blue-100 rounded-full p-6 mb-4">
                <i class="fas fa-barcode text-blue-600 text-6xl"></i>
            </div>
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Escanee el Código de Barras</h2>
            <p class="text-gray-600">Use el lector de código de barras o ingrese el código manualmente</p>
        </div>
        
        <div class="max-w-md mx-auto">
            <form id="scanForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Código de Barras (4 dígitos) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="barcodeInput" 
                           class="w-full text-center text-2xl font-mono rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="0000"
                           maxlength="4"
                           pattern="[0-9]{4}"
                           autocomplete="off"
                           autofocus>
                    <p class="text-xs text-gray-500 mt-1">Ingrese o escanee el código de 4 dígitos del ticket</p>
                </div>
                
                <button type="submit" id="processBtn"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>Procesar Salida
                </button>
            </form>
        </div>
    </div>
    
    <!-- Resultado -->
    <div id="result" class="hidden"></div>
    
    <!-- Historial de Salidas Recientes -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-history text-gray-600 mr-2"></i>Salidas Recientes
        </h3>
        <div id="recentExits" class="space-y-3">
            <p class="text-gray-500 text-center py-4">Aún no hay salidas registradas</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scanForm = document.getElementById('scanForm');
    const barcodeInput = document.getElementById('barcodeInput');
    const processBtn = document.getElementById('processBtn');
    const resultDiv = document.getElementById('result');
    const recentExits = document.getElementById('recentExits');
    
    let recentExitsList = [];
    
    // Auto-submit cuando se ingresan 4 dígitos
    barcodeInput.addEventListener('input', function() {
        // Solo permitir números
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Auto-submit al completar 4 dígitos
        if (this.value.length === 4) {
            setTimeout(() => {
                scanForm.dispatchEvent(new Event('submit'));
            }, 300);
        }
    });
    
    scanForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const barcode = barcodeInput.value.trim();
        
        if (barcode.length !== 4) {
            showError('Por favor ingrese un código de 4 dígitos');
            return;
        }
        
        processBtn.disabled = true;
        processBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
        
        try {
            const formData = new FormData();
            formData.append('barcode', barcode);
            
            const response = await fetch('<?php echo BASE_URL; ?>/access/processExit', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess(data.message, data.access);
                addToRecentExits(data.access, barcode);
                barcodeInput.value = '';
                
                // Reproducir sonido de éxito (opcional)
                playSuccessSound();
            } else {
                showError(data.message);
            }
        } catch (error) {
            showError('Error al procesar la salida: ' + error.message);
        } finally {
            processBtn.disabled = false;
            processBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Procesar Salida';
            barcodeInput.focus();
        }
    });
    
    function showSuccess(message, access) {
        resultDiv.className = 'bg-green-50 border-2 border-green-500 rounded-lg p-6 mb-6 animate-pulse';
        resultDiv.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 text-4xl"></i>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-semibold text-green-800 mb-2">¡Salida Registrada Exitosamente!</h3>
                    <div class="text-green-700 space-y-1">
                        <p><strong>Unidad:</strong> ${access.plate_number}</p>
                        <p><strong>Cliente:</strong> ${access.client_name}</p>
                        <p><strong>Chofer:</strong> ${access.driver_name}</p>
                        <p><strong>Litros:</strong> ${parseInt(access.capacity_liters).toLocaleString()} L</p>
                        <p class="text-sm mt-2">${message}</p>
                    </div>
                </div>
            </div>
        `;
        resultDiv.classList.remove('hidden');
        
        // Ocultar después de 5 segundos
        setTimeout(() => {
            resultDiv.classList.add('hidden');
        }, 5000);
    }
    
    function showError(message) {
        resultDiv.className = 'bg-red-50 border-2 border-red-500 rounded-lg p-6 mb-6';
        resultDiv.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 text-4xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-red-800 mb-2">Error</h3>
                    <p class="text-red-700">${message}</p>
                </div>
            </div>
        `;
        resultDiv.classList.remove('hidden');
        
        // Ocultar después de 3 segundos
        setTimeout(() => {
            resultDiv.classList.add('hidden');
        }, 3000);
    }
    
    function addToRecentExits(access, barcode) {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
        
        recentExitsList.unshift({
            barcode: barcode,
            time: timeStr,
            plate: access.plate_number,
            client: access.client_name,
            liters: parseInt(access.capacity_liters).toLocaleString()
        });
        
        // Mantener solo los últimos 5
        if (recentExitsList.length > 5) {
            recentExitsList = recentExitsList.slice(0, 5);
        }
        
        updateRecentExitsList();
    }
    
    function updateRecentExitsList() {
        if (recentExitsList.length === 0) {
            recentExits.innerHTML = '<p class="text-gray-500 text-center py-4">Aún no hay salidas registradas</p>';
            return;
        }
        
        recentExits.innerHTML = recentExitsList.map(exit => `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="bg-green-100 rounded-full p-2">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">${exit.plate} - ${exit.client}</p>
                        <p class="text-sm text-gray-600">Código: ${exit.barcode} | ${exit.liters} L</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">${exit.time}</p>
                </div>
            </div>
        `).join('');
    }
    
    function playSuccessSound() {
        // Crear un tono de éxito simple
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        } catch (e) {
            // Silenciar errores de audio
        }
    }
    
    // Mantener el foco en el input
    setInterval(() => {
        if (document.activeElement !== barcodeInput && !processBtn.disabled) {
            barcodeInput.focus();
        }
    }, 1000);
});
</script>
