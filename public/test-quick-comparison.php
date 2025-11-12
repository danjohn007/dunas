<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Registro Rápido - Comparación de Placas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .test-section { 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            padding: 20px; 
            margin: 20px 0; 
            background: #f9f9f9;
        }
        .plate-input { 
            padding: 10px; 
            font-size: 16px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            text-transform: uppercase;
            width: 200px;
        }
        .comparison-box {
            margin: 20px 0;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: linear-gradient(to right, #f0f0f0, #f8f8f8);
        }
        .comparison-box.match-ok {
            border-color: #16a34a;
            background: linear-gradient(to right, #dcfce7, #dbeafe);
        }
        .comparison-box.match-bad {
            border-color: #ef4444;
            background: linear-gradient(to right, #fecaca, #fed7d7);
        }
        .plate-display {
            font-family: monospace;
            font-weight: bold;
            font-size: 18px;
            padding: 8px;
            margin: 5px;
            background: white;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .status { font-weight: bold; margin-top: 10px; }
        .success { color: #16a34a; }
        .error { color: #ef4444; }
        .info { color: #3b82f6; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test: Registro Rápido - Comparación de Placas</h1>
        
        <div class="test-section">
            <h2>Simular entrada de placa</h2>
            <p>Ingresa una placa para probar la comparación en tiempo real:</p>
            
            <label for="plateTest">Número de Placa:</label>
            <input type="text" id="plateTest" class="plate-input" placeholder="Ej: ABC123X" maxlength="10">
            
            <div id="comparisonTest" class="comparison-box" style="display: none;">
                <h3>🔍 Comparación de Placas</h3>
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <div style="font-size: 12px; color: #666;">PLACA INGRESADA</div>
                        <div id="plateIngresada" class="plate-display">---</div>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 12px; color: #666;">PLACA DETECTADA</div>
                        <div id="plateDetectada" class="plate-display">Cargando...</div>
                    </div>
                </div>
                <div class="status" id="statusComparacion">Consultando...</div>
            </div>
        </div>
        
        <div class="test-section">
            <h2>Casos de prueba recomendados</h2>
            <ul>
                <li><strong>ABC123X</strong> - Debería mostrar coincidencia si existe</li>
                <li><strong>GHI789Z</strong> - Debería mostrar coincidencia si existe</li>
                <li><strong>TEST999X</strong> - Debería mostrar "Placa no encontrada"</li>
                <li><strong>XYZ</strong> - Muy corto, no debería activar comparación</li>
            </ul>
        </div>
        
        <div class="test-section">
            <h2>Log de actividad</h2>
            <div id="logContainer" style="max-height: 300px; overflow-y: auto; background: white; padding: 10px; border: 1px solid #ddd;">
                <div class="info">Esperando entrada de placa...</div>
            </div>
        </div>
        
        <div class="test-section">
            <h2>Respuesta de la API</h2>
            <pre id="apiResponse">Sin datos</pre>
        </div>
    </div>

    <script>
        const compareUrl = "<?php echo 'https://' . $_SERVER['HTTP_HOST'] . str_replace('test-quick-comparison.php', '', $_SERVER['REQUEST_URI']); ?>api/compare_plate.php";
        
        const plateInput = document.getElementById('plateTest');
        const plateIngresada = document.getElementById('plateIngresada');
        const plateDetectada = document.getElementById('plateDetectada');
        const statusComparacion = document.getElementById('statusComparacion');
        const comparisonBox = document.getElementById('comparisonTest');
        const logContainer = document.getElementById('logContainer');
        const apiResponse = document.getElementById('apiResponse');
        
        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const div = document.createElement('div');
            div.className = type;
            div.innerHTML = `[${timestamp}] ${message}`;
            logContainer.appendChild(div);
            logContainer.scrollTop = logContainer.scrollHeight;
        }
        
        function setCompareUI({detected, ok, msg}) {
            plateDetectada.textContent = detected ?? 'Error';
            statusComparacion.textContent = msg ?? (ok ? 'Coincide' : 'No coincide');
            statusComparacion.className = `status ${ok ? 'success' : 'error'}`;
            
            comparisonBox.classList.remove('match-ok', 'match-bad');
            comparisonBox.classList.add(ok ? 'match-ok' : 'match-bad');
        }
        
        async function doCompare(plateValue) {
            if (!plateValue || plateValue.length < 3) return;
            
            try {
                log(`Iniciando comparación para placa: ${plateValue}`, 'info');
                
                setCompareUI({detected: 'Cargando...', ok: false, msg: 'Consultando...'});
                
                const formData = new FormData();
                formData.append('unit_plate', plateValue);
                
                const response = await fetch(compareUrl, {
                    method: 'POST',
                    body: formData,
                    cache: 'no-cache',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                log(`Respuesta HTTP: ${response.status} ${response.statusText}`, 'info');
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    log(`Error: Respuesta no es JSON: ${text.slice(0, 200)}`, 'error');
                    throw new Error('Respuesta no válida del servidor');
                }
                
                const data = await response.json();
                apiResponse.textContent = JSON.stringify(data, null, 2);
                
                log(`API Response: ${JSON.stringify(data)}`, 'info');
                
                if (data.success) {
                    const detected = data.detected || 'Sin detección';
                    const isMatch = data.is_match || false;
                    
                    let message;
                    if (detected === 'Placa no encontrada') {
                        message = 'Placa no encontrada';
                    } else if (isMatch) {
                        message = 'Las placas coinciden ✅';
                    } else {
                        message = 'Las placas no coinciden ❌';
                    }
                    
                    setCompareUI({
                        detected: detected,
                        ok: isMatch,
                        msg: message
                    });
                    
                    log(`Comparación completada: ${message}`, isMatch ? 'success' : 'error');
                } else {
                    log(`Error de API: ${data.error}`, 'error');
                    setCompareUI({
                        detected: 'Error',
                        ok: false,
                        msg: data.error || 'Error en la comparación'
                    });
                }
                
            } catch (error) {
                log(`Error en comparación: ${error.message}`, 'error');
                setCompareUI({
                    detected: 'Error',
                    ok: false,
                    msg: 'No se pudo consultar las detecciones'
                });
            }
        }
        
        plateInput.addEventListener('input', function() {
            const plateValue = this.value.trim().toUpperCase();
            this.value = plateValue; // Forzar mayúsculas
            
            plateIngresada.textContent = plateValue || '---';
            
            if (plateValue && plateValue.length >= 3) {
                comparisonBox.style.display = 'block';
                log(`Placa ingresada: ${plateValue}`, 'info');
                
                clearTimeout(window.compareTimeout);
                window.compareTimeout = setTimeout(() => doCompare(plateValue), 600);
            } else {
                comparisonBox.style.display = 'none';
                clearTimeout(window.compareTimeout);
            }
        });
        
        log(`URL de comparación configurada: ${compareUrl}`, 'info');
    </script>
</body>
</html>