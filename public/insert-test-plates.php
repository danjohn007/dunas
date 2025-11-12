<?php
/**
 * Script para insertar datos de prueba en detected_plates
 */
require_once __DIR__ . '/../config/config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Insertar algunas detecciones de prueba
    $testPlates = [
        ['ABC123X', 85.5],
        ['XYZ789A', 92.0],
        ['DEF456B', 78.3],
        ['ABC-123-X', 88.7], // Misma placa pero con guiones
        ['abc123x', 91.2],   // Misma placa en minúsculas
    ];
    
    echo "<h1>Insertando datos de prueba</h1>";
    
    foreach ($testPlates as [$plate, $confidence]) {
        $stmt = $db->prepare("
            INSERT INTO detected_plates (plate_text, confidence, captured_at) 
            VALUES (?, ?, NOW() - INTERVAL FLOOR(RAND() * 60) MINUTE)
        ");
        $stmt->execute([$plate, $confidence]);
        echo "<p>✅ Insertada placa: $plate (confianza: $confidence%)</p>";
    }
    
    echo "<h2>Detecciones actuales en la base de datos:</h2>";
    $stmt = $db->query("
        SELECT id, plate_text, confidence, captured_at, is_match, unit_id 
        FROM detected_plates 
        ORDER BY captured_at DESC 
        LIMIT 10
    ");
    $plates = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Placa</th><th>Confianza</th><th>Capturada</th><th>Match</th><th>Unit ID</th></tr>";
    foreach ($plates as $plate) {
        echo "<tr>";
        echo "<td>{$plate['id']}</td>";
        echo "<td>{$plate['plate_text']}</td>";
        echo "<td>{$plate['confidence']}%</td>";
        echo "<td>{$plate['captured_at']}</td>";
        echo "<td>" . ($plate['is_match'] ? 'Sí' : 'No') . "</td>";
        echo "<td>{$plate['unit_id']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<h1>❌ Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>