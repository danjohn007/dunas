<?php
/**
 * Test script for Shelly Cloud API
 * Este script permite probar la conexión con el Shelly Cloud API
 */

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

// Verificar que estemos autenticados como admin (opcional para pruebas)
// Auth::requireRole(['admin']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Shelly Cloud API</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold mb-6 text-gray-800">
                <i class="fas fa-cloud text-blue-600 mr-2"></i>
                Test Shelly Cloud API
            </h1>
            
            <!-- Configuración actual -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <h2 class="text-xl font-semibold mb-3 text-blue-800">
                    <i class="fas fa-cog mr-2"></i>Configuración Actual
                </h2>
                <div class="space-y-2 text-sm">
                    <p><strong>Servidor:</strong> <?php echo SHELLY_SERVER; ?></p>
                    <p><strong>Device ID:</strong> <?php echo SHELLY_DEVICE_ID; ?></p>
                    <p><strong>Auth Token:</strong> <?php echo substr(SHELLY_AUTH_TOKEN, 0, 20) . '...'; ?></p>
                    <p><strong>Switch ID:</strong> <?php echo SHELLY_SWITCH_ID; ?></p>
                    <p><strong>Estado:</strong> 
                        <?php if (SHELLY_ENABLED): ?>
                            <span class="text-green-600 font-semibold">✓ Habilitado</span>
                        <?php else: ?>
                            <span class="text-red-600 font-semibold">✗ Deshabilitado</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Botones de prueba -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <button onclick="testStatus()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition">
                    <i class="fas fa-info-circle mr-2"></i>
                    Estado del Dispositivo
                </button>
                
                <button onclick="testOpen()" 
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition">
                    <i class="fas fa-door-open mr-2"></i>
                    Abrir Barrera
                </button>
                
                <button onclick="testClose()" 
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition">
                    <i class="fas fa-door-closed mr-2"></i>
                    Cerrar Barrera
                </button>
            </div>

            <!-- Área de resultados -->
            <div id="results" class="bg-gray-50 border border-gray-300 rounded-lg p-4 min-h-[200px]">
                <p class="text-gray-500 italic">Haga clic en un botón para probar la API...</p>
            </div>

            <!-- Información adicional -->
            <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-500 p-4">
                <h3 class="font-semibold text-yellow-800 mb-2">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Nota Importante
                </h3>
                <p class="text-sm text-yellow-800">
                    Este script utiliza el Shelly Cloud API. Asegúrese de que:
                </p>
                <ul class="list-disc list-inside text-sm text-yellow-800 mt-2 space-y-1">
                    <li>El dispositivo Shelly esté conectado a Internet</li>
                    <li>El dispositivo esté registrado en Shelly Cloud</li>
                    <li>Las credenciales (Auth Token, Device ID) sean correctas</li>
                    <li>El servidor tenga acceso a Internet (conexiones HTTPS salientes)</li>
                </ul>
            </div>

            <!-- Volver al sistema -->
            <div class="mt-6 text-center">
                <a href="<?php echo BASE_URL; ?>" 
                   class="inline-block bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al Sistema
                </a>
            </div>
        </div>
    </div>

    <script>
        const baseUrl = '<?php echo BASE_URL; ?>';
        
        function showResult(title, data, success = true) {
            const resultsDiv = document.getElementById('results');
            const iconClass = success ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600';
            const bgClass = success ? 'bg-green-50' : 'bg-red-50';
            
            resultsDiv.innerHTML = `
                <div class="${bgClass} border-l-4 ${success ? 'border-green-500' : 'border-red-500'} p-4">
                    <h3 class="font-semibold text-lg mb-2">
                        <i class="fas ${iconClass} mr-2"></i>
                        ${title}
                    </h3>
                    <pre class="text-sm bg-white p-3 rounded overflow-auto max-h-96">${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;
        }

        function showLoading(message) {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = `
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    <span class="ml-3 text-gray-700 font-medium">${message}</span>
                </div>
            `;
        }

        async function testStatus() {
            showLoading('Consultando estado del dispositivo...');
            
            try {
                const response = await fetch(`${baseUrl}/test-cloud-api-action.php?action=status`);
                const data = await response.json();
                
                if (data.success) {
                    showResult('Estado del Dispositivo', data.data, true);
                } else {
                    showResult('Error al consultar estado', {error: data.error, details: data}, false);
                }
            } catch (error) {
                showResult('Error de Conexión', {message: error.message}, false);
            }
        }

        async function testOpen() {
            showLoading('Abriendo barrera...');
            
            try {
                const response = await fetch(`${baseUrl}/test-cloud-api-action.php?action=open`);
                const data = await response.json();
                
                if (data.success) {
                    showResult('Barrera Abierta', data.data, true);
                } else {
                    showResult('Error al abrir barrera', {error: data.error, details: data}, false);
                }
            } catch (error) {
                showResult('Error de Conexión', {message: error.message}, false);
            }
        }

        async function testClose() {
            showLoading('Cerrando barrera...');
            
            try {
                const response = await fetch(`${baseUrl}/test-cloud-api-action.php?action=close`);
                const data = await response.json();
                
                if (data.success) {
                    showResult('Barrera Cerrada', data.data, true);
                } else {
                    showResult('Error al cerrar barrera', {error: data.error, details: data}, false);
                }
            } catch (error) {
                showResult('Error de Conexión', {message: error.message}, false);
            }
        }
    </script>
</body>
</html>
