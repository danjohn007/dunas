<?php
/**
 * Test simple para verificar la API de comparación de placas
 */
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Test API Compare Plate</h1>";

// Test 1: Verificar que el archivo existe y se puede incluir
echo "<h2>Test 1: Verificar archivo</h2>";
$apiFile = __DIR__ . '/api/compare_plate.php';
if (file_exists($apiFile)) {
    echo "<p>✅ Archivo existe: $apiFile</p>";
} else {
    echo "<p>❌ Archivo NO existe: $apiFile</p>";
    exit;
}

// Test 2: Simular llamada POST
echo "<h2>Test 2: Simular llamada API</h2>";

// Simular POST data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['unit_id'] = 1; // Usar ID de unidad existente

// Capturar output
ob_start();
try {
    include $apiFile;
    $output = ob_get_contents();
} catch (Exception $e) {
    $output = json_encode(['error' => $e->getMessage()]);
} finally {
    ob_end_clean();
}

echo "<h3>Respuesta de la API:</h3>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Test 3: Verificar JSON válido
echo "<h2>Test 3: Verificar formato JSON</h2>";
$decoded = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "<p>✅ JSON válido</p>";
    echo "<pre>" . print_r($decoded, true) . "</pre>";
} else {
    echo "<p>❌ JSON inválido. Error: " . json_last_error_msg() . "</p>";
}

// Test 4: Verificar base de datos
echo "<h2>Test 4: Verificar conexión a base de datos</h2>";
try {
    require_once __DIR__ . '/../config/config.php';
    $db = Database::getInstance()->getConnection();
    
    // Verificar tabla units
    $stmt = $db->query("SELECT COUNT(*) as count FROM units");
    $unitsCount = $stmt->fetch()['count'];
    echo "<p>✅ Conexión DB exitosa. Unidades en DB: $unitsCount</p>";
    
    // Verificar tabla detected_plates
    $stmt = $db->query("SELECT COUNT(*) as count FROM detected_plates");
    $platesCount = $stmt->fetch()['count'];
    echo "<p>✅ Tabla detected_plates existe. Registros: $platesCount</p>";
    
    // Mostrar algunas unidades
    $stmt = $db->query("SELECT id, plate_number FROM units LIMIT 5");
    $units = $stmt->fetchAll();
    echo "<h3>Unidades disponibles:</h3>";
    echo "<ul>";
    foreach ($units as $unit) {
        echo "<li>ID: {$unit['id']}, Placa: {$unit['plate_number']}</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Error de base de datos: " . $e->getMessage() . "</p>";
}
?>