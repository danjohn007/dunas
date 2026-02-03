<?php
/**
 * Script para ejecutar la migración de la tabla de vales
 * Uso: php run_voucher_migration.php
 */

// Cargar configuración
require_once __DIR__ . '/config/config.php';

try {
    // Conectar a la base de datos
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "Conectado a la base de datos...\n";
    
    // Leer el archivo de migración
    $migrationFile = __DIR__ . '/migrations/add_vouchers_table.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Archivo de migración no encontrado: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    echo "Ejecutando migración...\n";
    
    // Dividir el SQL en declaraciones individuales
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Declaración ejecutada correctamente\n";
            } catch (PDOException $e) {
                // Si es un error de que la tabla ya existe, ignorarlo
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    echo "⚠ Advertencia: La tabla ya existe\n";
                } else {
                    throw $e;
                }
            }
        }
    }
    
    echo "\n✅ Migración completada exitosamente!\n";
    echo "La tabla 'vouchers' ha sido creada o ya existe.\n";
    
    // Verificar que la tabla existe
    $result = $pdo->query("SHOW TABLES LIKE 'vouchers'")->fetch();
    if ($result) {
        echo "\n✓ Tabla 'vouchers' verificada en la base de datos\n";
        
        // Mostrar estructura de la tabla
        echo "\nEstructura de la tabla:\n";
        $columns = $pdo->query("DESCRIBE vouchers")->fetchAll();
        foreach ($columns as $column) {
            echo "  - {$column['Field']} ({$column['Type']})\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
