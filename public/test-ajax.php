<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test AJAX Compare Plate</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .info { background-color: #d1ecf1; border: 1px solid #bee5eb; }
        button { padding: 10px 20px; margin: 5px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Test AJAX Compare Plate</h1>
    
    <div class="info">
        <h3>Información del Test</h3>
        <p><strong>URL de la API:</strong> <span id="apiUrl"></span></p>
        <p><strong>Método:</strong> POST con FormData</p>
    </div>
    
    <div>
        <label for="unitSelect">Seleccionar Unit ID:</label>
        <select id="unitSelect">
            <option value="">-- Cargando unidades --</option>
        </select>
        <button onclick="testCompare()">Probar Comparación</button>
        <button onclick="loadUnits()">Recargar Unidades</button>
    </div>
    
    <div id="results"></div>
    
    <script>
        // Configurar URL base igual que en la página real
        const baseUrl = "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . str_replace('test-ajax.php', '', $_SERVER['REQUEST_URI']); ?>";
        const compareUrl = baseUrl + "api/compare_plate.php";
        
        document.getElementById('apiUrl').textContent = compareUrl;
        
        function addResult(message, type = 'info') {
            const div = document.createElement('div');
            div.className = `result ${type}`;
            div.innerHTML = message;
            document.getElementById('results').appendChild(div);
        }
        
        async function loadUnits() {
            try {
                addResult('Cargando unidades...', 'info');
                
                const response = await fetch(baseUrl + 'get-units.php');
                const units = await response.json();
                
                const select = document.getElementById('unitSelect');
                select.innerHTML = '<option value="">-- Seleccione una unidad --</option>';
                
                units.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = `${unit.id}: ${unit.plate_number}`;
                    select.appendChild(option);
                });
                
                addResult(`✅ Cargadas ${units.length} unidades`, 'success');
                
            } catch (error) {
                addResult(`❌ Error cargando unidades: ${error.message}`, 'error');
            }
        }
        
        async function testCompare() {
            const unitId = document.getElementById('unitSelect').value;
            if (!unitId) {
                addResult('❌ Seleccione una unidad primero', 'error');
                return;
            }
            
            try {
                addResult(`🔄 Probando comparación para Unit ID: ${unitId}`, 'info');
                
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
                
                addResult(`📡 Response Status: ${response.status} ${response.statusText}`, 'info');
                addResult(`📋 Content-Type: ${response.headers.get('content-type')}`, 'info');
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    addResult(`❌ Respuesta no es JSON:\n<pre>${text}</pre>`, 'error');
                    return;
                }
                
                const data = await response.json();
                
                if (data.success) {
                    const message = `
                        <h4>✅ Comparación exitosa</h4>
                        <ul>
                            <li><strong>Placa detectada:</strong> ${data.detected || 'N/A'}</li>
                            <li><strong>Placa de unidad:</strong> ${data.unit_plate || 'N/A'}</li>
                            <li><strong>¿Coincide?:</strong> ${data.is_match ? 'SÍ' : 'NO'}</li>
                            <li><strong>Capturada:</strong> ${data.captured_at || 'N/A'}</li>
                            <li><strong>Confianza:</strong> ${data.confidence || 'N/A'}%</li>
                            <li><strong>Mensaje:</strong> ${data.message || 'N/A'}</li>
                        </ul>
                        <details>
                            <summary>Ver respuesta completa</summary>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </details>
                    `;
                    addResult(message, data.is_match ? 'success' : 'info');
                } else {
                    addResult(`❌ Error en API: ${data.error}\n<pre>${JSON.stringify(data, null, 2)}</pre>`, 'error');
                }
                
            } catch (error) {
                addResult(`❌ Error en la prueba: ${error.message}`, 'error');
                console.error('Test error:', error);
            }
        }
        
        // Cargar unidades al iniciar
        loadUnits();
    </script>
</body>
</html>