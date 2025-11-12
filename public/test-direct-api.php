<?php
/**
 * Test directo de la API con datos conocidos
 */
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Test Directo API Compare Plate</h1>";

// Simular llamada POST directa
echo "<h2>Simulando llamada POST con unit_id</h2>";

// Buscar una unidad que tenga la placa ABC123X
require_once __DIR__ . '/../config/config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Buscar unidad con placa ABC123X
    $stmt = $db->prepare("SELECT id, plate_number FROM units WHERE plate_number = 'ABC123X'");
    $stmt->execute();
    $unit = $stmt->fetch();
    
    if ($unit) {
        echo "<p>✅ Unidad encontrada: ID={$unit['id']}, Placa={$unit['plate_number']}</p>";
        $unitId = $unit['id'];
    } else {
        echo "<p>❌ No se encontró unidad con placa ABC123X</p>";
        // Tomar la primera unidad disponible
        $stmt = $db->query("SELECT id, plate_number FROM units LIMIT 1");
        $unit = $stmt->fetch();
        if ($unit) {
            $unitId = $unit['id'];
            echo "<p>📝 Usando primera unidad disponible: ID={$unit['id']}, Placa={$unit['plate_number']}</p>";
        } else {
            echo "<p>❌ No hay unidades en la base de datos</p>";
            exit;
        }
    }
    
    // Insertar una detección de prueba para esta placa
    $stmt = $db->prepare("INSERT INTO detected_plates (plate_text, confidence, captured_at) VALUES (?, ?, NOW())");
    $stmt->execute([$unit['plate_number'], 95.5]);
    echo "<p>✅ Detección de prueba insertada: {$unit['plate_number']}</p>";
    
    // Simular $_POST y $_SERVER
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST['unit_id'] = $unitId;
    
    echo "<h3>Llamada a la API:</h3>";
    echo "<p>POST unit_id = $unitId</p>";
    
    // Llamar a la API y capturar resultado
    ob_start();
    try {
        include __DIR__ . '/api/compare_plate.php';
        $apiResponse = ob_get_contents();
    } catch (Exception $e) {
        $apiResponse = json_encode(['error' => 'Exception: ' . $e->getMessage()]);
    } finally {
        ob_end_clean();
    }
    
    echo "<h3>Respuesta de la API:</h3>";
    echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($apiResponse) . "</pre>";
    
    // Decodificar y mostrar resultado
    $decoded = json_decode($apiResponse, true);
    if ($decoded) {
        echo "<h3>Resultado decodificado:</h3>";
        echo "<ul>";
        foreach ($decoded as $key => $value) {
            echo "<li><strong>$key:</strong> " . (is_bool($value) ? ($value ? 'true' : 'false') : htmlspecialchars($value)) . "</li>";
        }
        echo "</ul>";
        
        if ($decoded['success']) {
            echo "<p style='color: green; font-weight: bold;'>🎉 ¡API funcionando correctamente!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ Error en API: " . ($decoded['error'] ?? 'Error desconocido') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No se pudo decodificar JSON</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>