<?php
/**
 * Script para agregar detecciones de prueba específicas
 */
require_once __DIR__ . '/../config/config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Obtener todas las unidades para generar detecciones
    $stmt = $db->query("SELECT id, plate_number FROM units WHERE status = 'active'");
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h1>Agregando detecciones de prueba</h1>";
    
    foreach ($units as $unit) {
        // Agregar detección exacta
        $stmt = $db->prepare("
            INSERT INTO detected_plates (plate_text, confidence, captured_at) 
            VALUES (?, ?, NOW() - INTERVAL FLOOR(RAND() * 30) MINUTE)
        ");
        $stmt->execute([$unit['plate_number'], rand(85, 98)]);
        echo "<p>✅ Detección exacta: {$unit['plate_number']} (Unit ID: {$unit['id']})</p>";
        
        // Agregar variaciones con guiones y espacios
        $variations = [
            str_replace('', '-', substr($unit['plate_number'], 0, 3) . '-' . substr($unit['plate_number'], 3)),
            strtolower($unit['plate_number']),
            $unit['plate_number'] . ' '
        ];
        
        foreach ($variations as $variation) {
            if ($variation !== $unit['plate_number']) {
                $stmt = $db->prepare("
                    INSERT INTO detected_plates (plate_text, confidence, captured_at) 
                    VALUES (?, ?, NOW() - INTERVAL FLOOR(RAND() * 60) MINUTE)
                ");
                $stmt->execute([$variation, rand(75, 90)]);
                echo "<p>✅ Variación: '$variation' → {$unit['plate_number']}</p>";
            }
        }
    }
    
    echo "<h2>Últimas 20 detecciones en la base de datos:</h2>";
    $stmt = $db->query("
        SELECT id, plate_text, confidence, captured_at, is_match, unit_id 
        FROM detected_plates 
        ORDER BY captured_at DESC 
        LIMIT 20
    ");
    $plates = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 12px;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Placa Detectada</th><th>Confianza</th><th>Capturada</th><th>Match</th><th>Unit ID</th></tr>";
    foreach ($plates as $plate) {
        $bgColor = $plate['is_match'] ? '#d4edda' : '#ffffff';
        echo "<tr style='background: $bgColor;'>";
        echo "<td>{$plate['id']}</td>";
        echo "<td><strong>{$plate['plate_text']}</strong></td>";
        echo "<td>{$plate['confidence']}%</td>";
        echo "<td>{$plate['captured_at']}</td>";
        echo "<td>" . ($plate['is_match'] ? '✅ Sí' : '❌ No') . "</td>";
        echo "<td>{$plate['unit_id']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>Prueba recomendada:</h2>";
    echo "<ol>";
    echo "<li>Ve a la página de registrar entrada</li>";
    echo "<li>Selecciona diferentes unidades</li>";
    echo "<li>Observa cómo ahora busca detecciones que coincidan con cada unidad</li>";
    echo "<li>Si seleccionas una unidad que tiene detecciones, debería mostrar 'Placas coinciden'</li>";
    echo "<li>Si seleccionas una unidad sin detecciones recientes, mostrará la última detección como referencia</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h1 style='color: red;'>❌ Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>