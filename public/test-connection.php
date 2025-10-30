<?php
/**
 * Test de conexión a base de datos y verificación de URL Base
 */

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Test de Conexión del Sistema</h1>
            
            <!-- Test de URL Base -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                <h2 class="text-xl font-semibold text-gray-700 mb-3 flex items-center">
                    <span class="text-2xl mr-2">🌐</span>
                    URL Base
                </h2>
                <div class="bg-green-50 border border-green-200 rounded p-3">
                    <p class="text-sm text-gray-600">URL Base detectada:</p>
                    <p class="text-lg font-mono text-green-700"><?php echo BASE_URL; ?></p>
                </div>
            </div>
            
            <!-- Test de Conexión a Base de Datos -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                <h2 class="text-xl font-semibold text-gray-700 mb-3 flex items-center">
                    <span class="text-2xl mr-2">💾</span>
                    Conexión a Base de Datos
                </h2>
                <?php
                try {
                    $db = Database::getInstance();
                    $conn = $db->getConnection();
                    
                    // Test de conexión
                    $sql = "SELECT VERSION() as version, DATABASE() as database_name";
                    $result = $db->fetchOne($sql);
                    
                    echo '<div class="bg-green-50 border border-green-200 rounded p-3 mb-3">';
                    echo '<p class="text-green-700 font-semibold mb-2">✅ Conexión exitosa</p>';
                    echo '<p class="text-sm text-gray-600">Base de datos: <span class="font-mono">' . htmlspecialchars($result['database_name']) . '</span></p>';
                    echo '<p class="text-sm text-gray-600">Versión MySQL: <span class="font-mono">' . htmlspecialchars($result['version']) . '</span></p>';
                    echo '</div>';
                    
                    // Verificar tablas
                    $tables = $db->fetchAll("SHOW TABLES");
                    echo '<div class="bg-blue-50 border border-blue-200 rounded p-3">';
                    echo '<p class="text-blue-700 font-semibold mb-2">📊 Tablas encontradas: ' . count($tables) . '</p>';
                    echo '<ul class="text-sm text-gray-600 list-disc list-inside">';
                    foreach ($tables as $table) {
                        $tableName = array_values($table)[0];
                        echo '<li>' . htmlspecialchars($tableName) . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                    
                } catch (Exception $e) {
                    echo '<div class="bg-red-50 border border-red-200 rounded p-3">';
                    echo '<p class="text-red-700 font-semibold mb-2">❌ Error de conexión</p>';
                    echo '<p class="text-sm text-gray-600">' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '</div>';
                }
                ?>
            </div>
            
            <!-- Información del Sistema -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                <h2 class="text-xl font-semibold text-gray-700 mb-3 flex items-center">
                    <span class="text-2xl mr-2">⚙️</span>
                    Información del Sistema
                </h2>
                <div class="space-y-2 text-sm">
                    <p><span class="font-semibold">Versión PHP:</span> <span class="font-mono"><?php echo PHP_VERSION; ?></span></p>
                    <p><span class="font-semibold">Sistema Operativo:</span> <span class="font-mono"><?php echo PHP_OS; ?></span></p>
                    <p><span class="font-semibold">Servidor Web:</span> <span class="font-mono"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span></p>
                    <p><span class="font-semibold">Ruta del proyecto:</span> <span class="font-mono text-xs"><?php echo ROOT_PATH; ?></span></p>
                </div>
            </div>
            
            <!-- Verificación de Directorios -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                <h2 class="text-xl font-semibold text-gray-700 mb-3 flex items-center">
                    <span class="text-2xl mr-2">📁</span>
                    Verificación de Directorios
                </h2>
                <div class="space-y-2 text-sm">
                    <?php
                    $directories = [
                        'Uploads' => UPLOAD_PATH,
                        'Uploads/Units' => UPLOAD_PATH . '/units',
                        'Uploads/Drivers' => UPLOAD_PATH . '/drivers',
                        'Logs' => ROOT_PATH . '/logs'
                    ];
                    
                    foreach ($directories as $name => $path) {
                        $exists = is_dir($path);
                        $writable = $exists && is_writable($path);
                        
                        if ($exists && $writable) {
                            echo '<p class="text-green-600">✅ ' . $name . ': Existe y es escribible</p>';
                        } elseif ($exists) {
                            echo '<p class="text-yellow-600">⚠️ ' . $name . ': Existe pero no es escribible</p>';
                        } else {
                            echo '<p class="text-red-600">❌ ' . $name . ': No existe</p>';
                        }
                    }
                    ?>
                </div>
            </div>
            
            <!-- Acciones -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex gap-3">
                    <a href="<?php echo BASE_URL; ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                        Ir al Sistema
                    </a>
                    <a href="<?php echo BASE_URL; ?>/login" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded">
                        Iniciar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
