<?php
/**
 * cleanup_detected_plates.php - API para limpiar la tabla detected_plates
 * 
 * Este endpoint elimina todos los registros de la tabla detected_plates
 * para permitir que lleguen nuevas placas al sistema.
 * 
 * Se ejecuta periódicamente desde las vistas /access y /access/quickRegistration
 */

// Headers para asegurar respuesta JSON limpia
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Configuración de errores - log pero no mostrar
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Limpiar buffer de salida
ob_start();

try {
    // Incluir archivos necesarios con manejo de errores
    $configPath = __DIR__ . '/../../config/config.php';
    if (!file_exists($configPath)) {
        throw new Exception('Archivo de configuración no encontrado');
    }
    require_once $configPath;
    
    // Incluir helper de base de datos
    $dbHelperPath = __DIR__ . '/../../app/helpers/Database.php';
    if (!file_exists($dbHelperPath)) {
        throw new Exception('Helper Database no encontrado');
    }
    require_once $dbHelperPath;

    // Verificar método de solicitud
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Solo se permiten solicitudes POST');
    }
    
    // Obtener instancia de base de datos
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    // Contar registros antes de eliminar (para información)
    $countStmt = $db->query("SELECT COUNT(*) as total FROM detected_plates");
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalBefore = $countResult['total'] ?? 0;

    // Eliminar todos los registros de la tabla detected_plates
    $stmt = $db->prepare("DELETE FROM detected_plates");
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception('Error al limpiar la tabla detected_plates');
    }

    // Registrar en log la limpieza
    error_log("cleanup_detected_plates.php - Se eliminaron $totalBefore registros de la tabla detected_plates");

    // Limpiar buffer y enviar respuesta exitosa
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Tabla detected_plates limpiada correctamente',
        'deleted_count' => $totalBefore,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (PDOException $e) {
    // Error de base de datos
    error_log("cleanup_detected_plates.php - Database error: " . $e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos'
    ]);
    
} catch (Exception $e) {
    // Error general
    error_log("cleanup_detected_plates.php - General error: " . $e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    
} catch (Throwable $e) {
    // Error crítico
    error_log("cleanup_detected_plates.php - Critical error: " . $e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor'
    ]);
}

// Finalizar buffer
exit;
