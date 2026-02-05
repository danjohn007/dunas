<?php
/**
 * Script de prueba para validar cambios en módulo de vales
 * Ejecutar desde línea de comandos: php test_voucher_changes.php
 */

// Configurar paths
define('APP_PATH', __DIR__ . '/app');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Voucher.php';

echo "=== INICIANDO PRUEBAS DE VALES ===\n\n";

$testResults = [];
$voucherModel = new Voucher();

// Test 1: Verificar formato corto de QR
echo "Test 1: Validar formato corto de códigos QR\n";
try {
    $reflection = new ReflectionClass('Voucher');
    $method = $reflection->getMethod('generateUniqueQRCode');
    $method->setAccessible(true);
    
    $testQR = $method->invoke($voucherModel, 'TEST', 123);
    
    if (preg_match('/^[A-Z]+-\d+$/', $testQR)) {
        echo "✓ Formato QR correcto: $testQR\n";
        $testResults['qr_format'] = 'PASS';
    } else {
        echo "✗ Formato QR incorrecto: $testQR\n";
        $testResults['qr_format'] = 'FAIL';
    }
} catch (Exception $e) {
    echo "✗ Error en test: " . $e->getMessage() . "\n";
    $testResults['qr_format'] = 'ERROR';
}
echo "\n";

// Test 2: Verificar método getByCode
echo "Test 2: Validar método getByCode con formato corto\n";
try {
    $method = $reflection->getMethod('getByCode');
    $method->setAccessible(true);
    
    // Este test solo valida que el método existe y acepta el parámetro
    echo "✓ Método getByCode disponible\n";
    $testResults['get_by_code'] = 'PASS';
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    $testResults['get_by_code'] = 'FAIL';
}
echo "\n";

// Test 3: Verificar método markAsRegistered
echo "Test 3: Validar método markAsRegistered\n";
try {
    $hasMethod = method_exists($voucherModel, 'markAsRegistered');
    if ($hasMethod) {
        echo "✓ Método markAsRegistered disponible\n";
        $testResults['mark_registered'] = 'PASS';
    } else {
        echo "✗ Método markAsRegistered no encontrado\n";
        $testResults['mark_registered'] = 'FAIL';
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    $testResults['mark_registered'] = 'ERROR';
}
echo "\n";

// Test 4: Verificar método getFinancialStats
echo "Test 4: Validar método getFinancialStats\n";
try {
    $hasMethod = method_exists($voucherModel, 'getFinancialStats');
    if ($hasMethod) {
        echo "✓ Método getFinancialStats disponible\n";
        $testResults['financial_stats'] = 'PASS';
    } else {
        echo "✗ Método getFinancialStats no encontrado\n";
        $testResults['financial_stats'] = 'FAIL';
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    $testResults['financial_stats'] = 'ERROR';
}
echo "\n";

// Test 5: Verificar campos en base de datos
echo "Test 5: Validar estructura de tabla vouchers\n";
try {
    $db = Database::getInstance();
    $result = $db->fetchAll("DESCRIBE vouchers");
    
    $requiredFields = ['cost', 'payment_status'];
    $existingFields = array_column($result, 'Field');
    
    $allFieldsExist = true;
    foreach ($requiredFields as $field) {
        if (in_array($field, $existingFields)) {
            echo "✓ Campo '$field' existe\n";
        } else {
            echo "✗ Campo '$field' no existe\n";
            $allFieldsExist = false;
        }
    }
    
    // Verificar enum de status incluye 'registered'
    $statusField = array_filter($result, function($f) { return $f['Field'] === 'status'; });
    $statusField = reset($statusField);
    if ($statusField && strpos($statusField['Type'], 'registered') !== false) {
        echo "✓ Estado 'registered' incluido en enum\n";
    } else {
        echo "✗ Estado 'registered' no encontrado en enum\n";
        $allFieldsExist = false;
    }
    
    $testResults['db_structure'] = $allFieldsExist ? 'PASS' : 'FAIL';
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    $testResults['db_structure'] = 'ERROR';
}
echo "\n";

// Resumen
echo "=== RESUMEN DE PRUEBAS ===\n";
$passed = 0;
$failed = 0;
$errors = 0;

foreach ($testResults as $test => $result) {
    $symbol = $result === 'PASS' ? '✓' : ($result === 'FAIL' ? '✗' : '⚠');
    echo "$symbol $test: $result\n";
    
    if ($result === 'PASS') $passed++;
    elseif ($result === 'FAIL') $failed++;
    else $errors++;
}

echo "\nTotal: " . count($testResults) . " pruebas\n";
echo "Exitosas: $passed\n";
echo "Fallidas: $failed\n";
echo "Errores: $errors\n";

exit($failed > 0 || $errors > 0 ? 1 : 0);
