<?php
/**
 * Test de construcción dinámica de URLs de Shelly
 * Este script prueba la lógica de construcción de URLs sin requerir conexión a BD
 */

echo "<h1>Test de Construcción Dinámica de URLs Shelly</h1>";
echo "<hr>";

// Simular diferentes escenarios
$scenarios = [
    [
        'name' => 'Escenario 1: Solo API URL base (construcción automática)',
        'settings' => [
            'shelly_api_url' => 'http://192.168.1.95',
            'shelly_relay_open' => '0',
            'shelly_relay_close' => '1',
        ],
        'expected_open' => 'http://192.168.1.95/rpc/Switch.Set?id=0&on=false',
        'expected_close' => 'http://192.168.1.95/rpc/Switch.Set?id=1&on=true',
    ],
    [
        'name' => 'Escenario 2: URLs completas definidas (debe respetar)',
        'settings' => [
            'shelly_api_url' => 'http://192.168.1.95',
            'shelly_open_url' => 'http://192.168.1.95/custom/open',
            'shelly_close_url' => 'http://192.168.1.95/custom/close',
        ],
        'expected_open' => 'http://192.168.1.95/custom/open',
        'expected_close' => 'http://192.168.1.95/custom/close',
    ],
    [
        'name' => 'Escenario 3: API URL con barra final (normalización)',
        'settings' => [
            'shelly_api_url' => 'http://192.168.1.95/',
            'shelly_relay_open' => '0',
            'shelly_relay_close' => '0', // Mismo canal para ambos
        ],
        'expected_open' => 'http://192.168.1.95/rpc/Switch.Set?id=0&on=false',
        'expected_close' => 'http://192.168.1.95/rpc/Switch.Set?id=0&on=true',
    ],
    [
        'name' => 'Escenario 4: Canales personalizados',
        'settings' => [
            'shelly_api_url' => 'http://192.168.1.95',
            'shelly_relay_open' => '2',
            'shelly_relay_close' => '3',
        ],
        'expected_open' => 'http://192.168.1.95/rpc/Switch.Set?id=2&on=false',
        'expected_close' => 'http://192.168.1.95/rpc/Switch.Set?id=3&on=true',
    ],
];

// Función para simular la lógica de getSettings()
function simulateGetSettings($allSettings) {
    $apiUrl   = $allSettings['shelly_api_url']   ?? null;
    $openUrl  = $allSettings['shelly_open_url']  ?? null;
    $closeUrl = $allSettings['shelly_close_url'] ?? null;
    
    // Si no hay URLs completas, construirlas con URL base + canal
    if (!$openUrl || !$closeUrl) {
        $relayOpen  = isset($allSettings['shelly_relay_open'])  ? (int)$allSettings['shelly_relay_open']  : 0;
        $relayClose = isset($allSettings['shelly_relay_close']) ? (int)$allSettings['shelly_relay_close'] : 1;
        
        // Normalizar base
        if ($apiUrl) {
            $base = rtrim($apiUrl, '/');
            // Convención por cableado típico: abrir = OFF, cerrar = ON.
            $openUrl  = $openUrl  ?: ($base . "/rpc/Switch.Set?id={$relayOpen}&on=false");
            $closeUrl = $closeUrl ?: ($base . "/rpc/Switch.Set?id={$relayClose}&on=true");
        }
    }
    
    return [
        'api_url'   => $apiUrl,
        'open_url'  => $openUrl,
        'close_url' => $closeUrl,
    ];
}

// Ejecutar tests
$passedTests = 0;
$totalTests = 0;

foreach ($scenarios as $scenario) {
    echo "<h2>{$scenario['name']}</h2>";
    echo "<strong>Input:</strong><pre>" . print_r($scenario['settings'], true) . "</pre>";
    
    $result = simulateGetSettings($scenario['settings']);
    
    echo "<strong>Resultado:</strong><pre>";
    echo "open_url:  {$result['open_url']}\n";
    echo "close_url: {$result['close_url']}\n";
    echo "</pre>";
    
    // Validar
    $openMatch = ($result['open_url'] === $scenario['expected_open']);
    $closeMatch = ($result['close_url'] === $scenario['expected_close']);
    
    $totalTests += 2;
    if ($openMatch) {
        echo "<div style='color: green;'>✓ open_url correcta</div>";
        $passedTests++;
    } else {
        echo "<div style='color: red;'>✗ open_url incorrecta. Esperado: {$scenario['expected_open']}</div>";
    }
    
    if ($closeMatch) {
        echo "<div style='color: green;'>✓ close_url correcta</div>";
        $passedTests++;
    } else {
        echo "<div style='color: red;'>✗ close_url incorrecta. Esperado: {$scenario['expected_close']}</div>";
    }
    
    echo "<hr>";
}

// Resumen
echo "<h2>Resumen</h2>";
echo "<div style='font-size: 1.2em; font-weight: bold;'>";
if ($passedTests === $totalTests) {
    echo "<div style='color: green;'>✓ Todos los tests pasaron: $passedTests/$totalTests</div>";
} else {
    echo "<div style='color: red;'>✗ Algunos tests fallaron: $passedTests/$totalTests pasados</div>";
}
echo "</div>";
?>
