<?php
/**
 * ShellyLockHelper - Funciones auxiliares para idempotencia y locking
 * 
 * Proporciona mecanismos de bloqueo y registro de pulsos para evitar duplicados.
 */
class ShellyLockHelper {
    
    /**
     * Ejecuta una función con lock de MySQL a nivel de puerto
     * Esto serializa las operaciones en el mismo relay para evitar dobles pulsos.
     * 
     * @param Database $db Instancia de base de datos
     * @param int $relayId ID del relay a bloquear
     * @param callable $fn Función a ejecutar dentro del lock
     * @param int $timeoutSec Tiempo máximo de espera por el lock (default: 2s)
     * @return array Resultado de la función o error de lock
     */
    public static function withPortLock($db, int $relayId, callable $fn, int $timeoutSec = 2): array {
        $lockName = "shelly_port_{$relayId}";
        
        try {
            // Intentar obtener el lock
            $stmt = $db->query("SELECT GET_LOCK('{$lockName}', {$timeoutSec}) AS got");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $got = (int)($result['got'] ?? 0);
            
            if ($got !== 1) {
                error_log("ShellyLockHelper - Failed to acquire lock '{$lockName}'");
                return ['ok' => false, 'error' => 'lock_timeout', 'message' => 'No se pudo obtener el bloqueo del relay'];
            }
            
            error_log("ShellyLockHelper - Lock '{$lockName}' acquired");
            
            // Ejecutar la función
            try {
                return $fn();
            } finally {
                // Siempre liberar el lock
                $db->query("DO RELEASE_LOCK('{$lockName}')");
                error_log("ShellyLockHelper - Lock '{$lockName}' released");
            }
            
        } catch (Exception $e) {
            error_log("ShellyLockHelper - Exception in lock: " . $e->getMessage());
            // Intentar liberar el lock en caso de error
            try {
                $db->query("DO RELEASE_LOCK('{$lockName}')");
            } catch (Exception $releaseEx) {
                // Ignorar errores al liberar
            }
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Registra un pulso en el log de idempotencia
     * 
     * @param Database $db Instancia de base de datos
     * @param string $action 'entry' o 'exit'
     * @param int $relayId ID del relay
     * @param string $correlation Identificador único (ej: "access:123:entry")
     * @param array $options Opciones adicionales (device_id, duration_ms, success, error_message)
     * @return bool True si se registró correctamente
     */
    public static function logPulse($db, string $action, int $relayId, string $correlation, array $options = []): bool {
        try {
            $sql = "INSERT INTO io_pulse_log 
                    (action, relay_id, correlation, device_id, duration_ms, success, error_message) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $action,
                $relayId,
                $correlation,
                $options['device_id'] ?? null,
                $options['duration_ms'] ?? null,
                isset($options['success']) ? (int)$options['success'] : 1,
                $options['error_message'] ?? null
            ];
            
            $db->execute($sql, $params);
            error_log("ShellyLockHelper - Pulse logged: {$correlation}");
            return true;
            
        } catch (Exception $e) {
            // Si falla por UNIQUE constraint, significa que ya existía
            if (strpos($e->getMessage(), 'Duplicate entry') !== false || 
                strpos($e->getMessage(), 'uniq_pulse') !== false) {
                error_log("ShellyLockHelper - Duplicate pulse prevented: {$correlation}");
                return false;
            }
            error_log("ShellyLockHelper - Error logging pulse: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica si ya existe un pulso registrado para evitar duplicados
     * 
     * @param Database $db Instancia de base de datos
     * @param int $relayId ID del relay
     * @param string $correlation Identificador único
     * @return bool True si el pulso ya existe
     */
    public static function pulseExists($db, int $relayId, string $correlation): bool {
        try {
            $sql = "SELECT COUNT(*) as count FROM io_pulse_log 
                    WHERE relay_id = ? AND correlation = ?";
            $result = $db->fetchOne($sql, [$relayId, $correlation]);
            return $result && $result['count'] > 0;
        } catch (Exception $e) {
            error_log("ShellyLockHelper - Error checking pulse existence: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Limpia registros antiguos del log de pulsos (más de N días)
     * Ejecutar periódicamente para mantener el log limpio.
     * 
     * @param Database $db Instancia de base de datos
     * @param int $daysToKeep Días a mantener (default: 30)
     * @return int Número de registros eliminados
     */
    public static function cleanOldLogs($db, int $daysToKeep = 30): int {
        try {
            $sql = "DELETE FROM io_pulse_log 
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            return $db->execute($sql, [$daysToKeep]);
        } catch (Exception $e) {
            error_log("ShellyLockHelper - Error cleaning old logs: " . $e->getMessage());
            return 0;
        }
    }
}
