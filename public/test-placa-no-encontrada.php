<?php
/**
 * Test para verificar el nuevo mensaje "Placa no encontrada"
 */
require_once __DIR__ . '/../config/config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h1>Prueba: Mensaje 'Placa no encontrada'</h1>";
    
    // 1. Buscar una unidad que NO tenga detecciones recientes
    $stmt = $db->query("
        SELECT u.id, u.plate_number, 
               (SELECT COUNT(*) FROM detected_plates dp 
                WHERE UPPER(REGEXP_REPLACE(dp.plate_text, '[^A-Z0-9]', '')) = 
                      UPPER(REGEXP_REPLACE(u.plate_number, '[^A-Z0-9]', ''))
                AND dp.captured_at > NOW() - INTERVAL 2 HOUR
               ) as recent_detections
        FROM units u 
        WHERE u.status = 'active'
        HAVING recent_detections = 0
        LIMIT 1
    ");
    
    $unitWithoutDetection = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($unitWithoutDetection) {
        echo "<p>✅ Unidad sin detecciones recientes encontrada: <strong>{$unitWithoutDetection['plate_number']}</strong> (ID: {$unitWithoutDetection['id']})</p>";
        
        // Simular llamada a la API
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['unit_id'] = $unitWithoutDetection['id'];
        
        ob_start();
        include __DIR__ . '/api/compare_plate.php';
        $apiResponse = ob_get_contents();
        ob_end_clean();
        
        $decoded = json_decode($apiResponse, true);
        
        echo "<h3>Respuesta de la API:</h3>";
        echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>" . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        
        if ($decoded && $decoded['detected'] === 'Placa no encontrada') {
            echo "<p style='color: green; font-weight: bold;'>🎉 ¡Perfecto! La API devuelve 'Placa no encontrada' correctamente</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ Error: La API no está devolviendo 'Placa no encontrada'</p>";
        }
        
        echo "<h3>Prueba manual recomendada:</h3>";
        echo "<ol>";
        echo "<li>Ve a: <a href='../access/create' target='_blank'>Registrar Entrada</a></li>";
        echo "<li>Selecciona la unidad: <strong>{$unitWithoutDetection['plate_number']}</strong></li>";
        echo "<li>Deberías ver 'Placa no encontrada' en lugar de una placa específica</li>";
        echo "</ol>";
        
    } else {
        echo "<p>ℹ️ Todas las unidades tienen detecciones recientes. Vamos a crear una unidad de prueba...</p>";
        
        // Crear una unidad temporal para prueba
        $testPlate = 'TEST' . rand(100, 999) . 'X';
        $stmt = $db->prepare("
            INSERT INTO units (plate_number, brand, model, capacity_liters, status) 
            VALUES (?, 'TEST', 'TEST', 1000, 'active')
        ");
        $stmt->execute([$testPlate]);
        $testUnitId = $db->lastInsertId();
        
        echo "<p>✅ Unidad de prueba creada: <strong>$testPlate</strong> (ID: $testUnitId)</p>";
        
        // Probar con la unidad de prueba
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['unit_id'] = $testUnitId;
        
        ob_start();
        include __DIR__ . '/api/compare_plate.php';
        $apiResponse = ob_get_contents();
        ob_end_clean();
        
        $decoded = json_decode($apiResponse, true);
        
        echo "<h3>Respuesta de la API para unidad de prueba:</h3>";
        echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>" . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        
        if ($decoded && $decoded['detected'] === 'Placa no encontrada') {
            echo "<p style='color: green; font-weight: bold;'>🎉 ¡Perfecto! La API devuelve 'Placa no encontrada' correctamente</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ Error: La API no está devolviendo 'Placa no encontrada'</p>";
        }
        
        // Limpiar unidad de prueba
        $stmt = $db->prepare("DELETE FROM units WHERE id = ?");
        $stmt->execute([$testUnitId]);
        echo "<p>🧹 Unidad de prueba eliminada</p>";
    }
    
    echo "<h2>Estados posibles de la comparación:</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Caso</th><th>Placa Detectada</th><th>Estado</th><th>Mensaje</th></tr>";
    echo "<tr><td>Coincidencia exacta</td><td>ABC123X</td><td>✅ Coincide</td><td>Las placas coinciden</td></tr>";
    echo "<tr><td>No hay coincidencia</td><td>Placa no encontrada</td><td>❌ No coincide</td><td>Placa de la unidad no encontrada</td></tr>";
    echo "<tr><td>Sin detecciones</td><td>Placa no encontrada</td><td>❌ No coincide</td><td>No hay detecciones de placas recientes</td></tr>";
    echo "</table>";
    
} catch (Exception $e) {
    echo "<h1 style='color: red;'>❌ Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>