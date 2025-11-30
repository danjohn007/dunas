<?php
/**
 * Controlador Settings
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/Settings.php';
require_once APP_PATH . '/models/ShellyDevice.php';
require_once APP_PATH . '/models/ShellyAction.php';
require_once APP_PATH . '/models/HikvisionDevice.php';

class SettingsController extends BaseController {
    
    private $settingsModel;
    
    public function __construct() {
        $this->settingsModel = new Settings();
    }
    
    public function index() {
        Auth::requireRole(['admin']);
        
        $settings = $this->settingsModel->getAll();
        
        // Obtener dispositivos Shelly configurados
        $db = Database::getInstance();
        $shellyDevices = ShellyDevice::getAll($db);
        
        // Obtener acciones para cada dispositivo
        foreach ($shellyDevices as &$device) {
            $device['actions'] = ShellyAction::getByDevice($db, $device['id']);
        }
        
        // Obtener dispositivos HikVision configurados
        $hikvisionDevices = HikvisionDevice::getAll($db);
        
        $data = [
            'title' => 'Configuraciones del Sistema',
            'settings' => $settings,
            'shellyDevices' => $shellyDevices,
            'hikvisionDevices' => $hikvisionDevices,
            'showNav' => true
        ];
        
        $this->view('settings/index', $data);
    }
    
    public function update() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Procesar logo si se subió
                if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = UPLOAD_PATH . '/logos';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $result = FileUpload::upload($_FILES['site_logo'], $uploadDir);
                    if ($result['success']) {
                        $_POST['site_logo'] = '/uploads/logos/' . $result['filename'];
                    } else {
                        throw new Exception($result['error']);
                    }
                }
                
                // Procesar colores del tema - usar el valor del input color, no el hex text
                if (isset($_POST['theme_primary_color_hex'])) {
                    unset($_POST['theme_primary_color_hex']);
                }
                if (isset($_POST['theme_secondary_color_hex'])) {
                    unset($_POST['theme_secondary_color_hex']);
                }
                if (isset($_POST['theme_accent_color_hex'])) {
                    unset($_POST['theme_accent_color_hex']);
                }
                
                // Guardar todas las configuraciones
                $this->settingsModel->updateMultiple($_POST);
                
                $this->setFlash('success', 'Configuraciones actualizadas exitosamente.');
            } catch (Exception $e) {
                $this->setFlash('error', 'Error al actualizar configuraciones: ' . $e->getMessage());
            }
        }
        
        $this->redirect('/settings');
    }
    
    /**
     * Guarda/actualiza los dispositivos Shelly
     */
    public function saveShellyDevices() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings');
            return;
        }
        
        try {
            $db = Database::getInstance();
            $rows = [];
            $skippedDevices = 0;
            
            // Procesar dispositivos enviados desde el formulario
            if (isset($_POST['devices']) && is_array($_POST['devices'])) {
                foreach ($_POST['devices'] as $i => $d) {
                    // Sanitizar y validar datos
                    $authToken = trim($d['auth_token'] ?? '');
                    $deviceId = trim($d['device_id'] ?? '');
                    $serverHost = trim($d['server_host'] ?? '');
                    
                    // Validar campos requeridos
                    if (empty($authToken) || empty($deviceId) || empty($serverHost)) {
                        $skippedDevices++;
                        continue; // Saltar dispositivos con datos incompletos
                    }
                    
                    $rows[] = [
                        'id' => isset($d['id']) && $d['id'] !== '' ? (int)$d['id'] : null,
                        'name' => trim($d['name'] ?? 'Abrir/Cerrar'),
                        'auth_token' => $authToken,
                        'device_id' => $deviceId,
                        'server_host' => $serverHost,
                        'area' => trim($d['area'] ?? ''),
                        'active_channel' => max(0, min(3, (int)($d['active_channel'] ?? 0))),
                        'entry_channel' => max(0, min(3, (int)($d['entry_channel'] ?? 0))),
                        'exit_channel' => max(0, min(3, (int)($d['exit_channel'] ?? 1))),
                        'pulse_duration_ms' => max(100, min(10000, (int)($d['pulse_duration_ms'] ?? 5000))),
                        'channel_count' => max(1, min(4, (int)($d['channel_count'] ?? 4))),
                        'invert_sequence' => isset($d['invert_sequence']) ? 1 : 0,
                        'is_simultaneous' => isset($d['is_simultaneous']) ? 1 : 0,
                        'is_enabled' => isset($d['is_enabled']) ? 1 : 0,
                        'sort_order' => (int)($d['sort_order'] ?? $i),
                    ];
                }
            }
            
            // Guardar dispositivos en batch
            ShellyDevice::upsertBatch($db, $rows);
            
            // Procesar acciones para cada dispositivo
            if (isset($_POST['devices']) && is_array($_POST['devices'])) {
                foreach ($_POST['devices'] as $d) {
                    $deviceId = isset($d['id']) && $d['id'] !== '' ? (int)$d['id'] : null;
                    $authToken = trim($d['auth_token'] ?? '');
                    
                    if (!$deviceId && !empty($authToken)) {
                        // Dispositivo recién insertado: obtenerlo por auth_token y device_id
                        $device = $db->fetchOne(
                            "SELECT id FROM shelly_devices WHERE auth_token = ? AND device_id = ? ORDER BY id DESC LIMIT 1",
                            [$authToken, trim($d['device_id'] ?? '')]
                        );
                        if ($device) {
                            $deviceId = $device['id'];
                        }
                    }
                    
                    if ($deviceId) {
                        // Determinar la acción según el select "action_code"
                        $actionCode = $d['action_code'] ?? 'abrir_cerrar';
                        $actionLabel = ($actionCode === 'vacio') ? 'Vacío' : 'Abrir/Cerrar';
                        $kind = ($actionCode === 'vacio') ? 'off' : 'toggle';
                        $channel = (int)($d['active_channel'] ?? 0);
                        
                        // Crear/actualizar acción para el dispositivo
                        ShellyAction::upsertForDevice($db, $deviceId, [[
                            'code' => $actionCode,
                            'label' => $actionLabel,
                            'action_kind' => $kind,
                            'channel' => $channel,
                            'duration_ms' => null,
                            'is_default' => 1
                        ]]);
                    }
                }
            }
            
            // Mensaje de éxito con información de dispositivos omitidos
            if ($skippedDevices > 0) {
                $this->setFlash('warning', "Dispositivos Shelly guardados. Se omitieron $skippedDevices dispositivo(s) con datos incompletos.");
            } else {
                $this->setFlash('success', 'Dispositivos Shelly guardados exitosamente.');
            }
        } catch (Exception $e) {
            error_log("Error al guardar dispositivos Shelly: " . $e->getMessage());
            $this->setFlash('error', 'Error al guardar dispositivos Shelly: ' . $e->getMessage());
        }
        
        $this->redirect('/settings');
    }
    
    /**
     * Guarda/actualiza los dispositivos HikVision
     */
    public function saveHikvisionDevices() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings');
            return;
        }
        
        try {
            $db = Database::getInstance();
            $rows = [];
            $skippedDevices = 0;
            
            // Procesar dispositivos enviados desde el formulario
            if (isset($_POST['hikvision_devices']) && is_array($_POST['hikvision_devices'])) {
                foreach ($_POST['hikvision_devices'] as $i => $d) {
                    // Sanitizar y validar datos
                    // Para modo cloud, se requiere api_key. Para modo ISAPI, se requiere api_url
                    $apiKey = trim($d['api_key'] ?? '');
                    $apiUrl = trim($d['api_url'] ?? '');
                    
                    // Validar campos requeridos (al menos uno de los dos modos debe estar configurado)
                    if (empty($apiKey) && empty($apiUrl)) {
                        $skippedDevices++;
                        continue; // Saltar dispositivos con datos incompletos
                    }
                    
                    $rows[] = [
                        'id' => isset($d['id']) && $d['id'] !== '' ? (int)$d['id'] : null,
                        'name' => trim($d['name'] ?? 'Dispositivo HikVision'),
                        'device_type' => in_array($d['device_type'] ?? '', ['camera_lpr', 'barcode_reader']) 
                            ? $d['device_type'] 
                            : 'camera_lpr',
                        'api_url' => $apiUrl,
                        'username' => trim($d['username'] ?? ''),
                        'password' => trim($d['password'] ?? ''),
                        'api_key' => $apiKey,
                        'api_secret' => trim($d['api_secret'] ?? ''),
                        'token_endpoint' => trim($d['token_endpoint'] ?? ''),
                        'area_domain' => trim($d['area_domain'] ?? ''),
                        'device_index_code' => trim($d['device_index_code'] ?? ''),
                        'verify_ssl' => isset($d['verify_ssl']) ? 1 : 0,
                        'area' => trim($d['area'] ?? ''),
                        'area_label' => trim($d['area_label'] ?? ''),
                        'is_enabled' => isset($d['is_enabled']) ? 1 : 0,
                        'sort_order' => (int)($d['sort_order'] ?? $i),
                    ];
                }
            }
            
            // Guardar dispositivos en batch
            HikvisionDevice::upsertBatch($db, $rows);
            
            // Mensaje de éxito con información de dispositivos omitidos
            if ($skippedDevices > 0) {
                $this->setFlash('warning', "Dispositivos HikVision guardados. Se omitieron $skippedDevices dispositivo(s) con datos incompletos.");
            } else {
                $this->setFlash('success', 'Dispositivos HikVision guardados exitosamente.');
            }
        } catch (Exception $e) {
            error_log("Error al guardar dispositivos HikVision: " . $e->getMessage());
            $this->setFlash('error', 'Error al guardar dispositivos HikVision: ' . $e->getMessage());
        }
        
        $this->redirect('/settings');
    }
    
    /**
     * Guarda la configuración del Bridge HikVision (Control de Acceso)
     */
    public function saveHikvisionBridge() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings');
            return;
        }
        
        try {
            // Validar campos
            $bridgeUrl = trim($_POST['bridge_url'] ?? '');
            $bridgeTimeout = (int)($_POST['bridge_timeout'] ?? 10);
            $userValidityHours = (int)($_POST['user_validity_hours'] ?? 1);
            $bridgeEnabled = isset($_POST['bridge_enabled']) ? 1 : 0;
            
            // Validaciones
            if (empty($bridgeUrl)) {
                throw new Exception('La URL del Bridge es requerida.');
            }
            
            if (!filter_var($bridgeUrl, FILTER_VALIDATE_URL)) {
                throw new Exception('La URL del Bridge no es valida.');
            }
            
            if ($bridgeTimeout < 5 || $bridgeTimeout > 60) {
                throw new Exception('El timeout debe estar entre 5 y 60 segundos.');
            }
            
            if ($userValidityHours < 1 || $userValidityHours > 24) {
                throw new Exception('Las horas de validez deben estar entre 1 y 24.');
            }
            
            // Actualizar el archivo config.php
            $configPath = __DIR__ . '/../../config/config.php';
            if (!file_exists($configPath)) {
                throw new Exception('No se encontro el archivo config.php');
            }
            
            $configContent = file_get_contents($configPath);
            
            // Actualizar HIKVISION_BRIDGE_URL
            $configContent = preg_replace(
                "/define\('HIKVISION_BRIDGE_URL',\s*'[^']*'\);/",
                "define('HIKVISION_BRIDGE_URL', '" . addslashes($bridgeUrl) . "');",
                $configContent
            );
            
            // Actualizar HIKVISION_BRIDGE_TIMEOUT
            $configContent = preg_replace(
                "/define\('HIKVISION_BRIDGE_TIMEOUT',\s*\d+\);/",
                "define('HIKVISION_BRIDGE_TIMEOUT', " . $bridgeTimeout . ");",
                $configContent
            );
            
            // Actualizar HIKVISION_USER_VALIDITY_HOURS
            $configContent = preg_replace(
                "/define\('HIKVISION_USER_VALIDITY_HOURS',\s*\d+\);/",
                "define('HIKVISION_USER_VALIDITY_HOURS', " . $userValidityHours . ");",
                $configContent
            );
            
            // Actualizar HIKVISION_ENABLED
            $configContent = preg_replace(
                "/define\('HIKVISION_ENABLED',\s*(true|false)\);/",
                "define('HIKVISION_ENABLED', " . ($bridgeEnabled ? 'true' : 'false') . ");",
                $configContent
            );
            
            // Guardar el archivo
            if (file_put_contents($configPath, $configContent) === false) {
                throw new Exception('No se pudo escribir el archivo config.php');
            }
            
            $this->setFlash('success', 'Configuracion del Lector HikVision guardada exitosamente.');
        } catch (Exception $e) {
            error_log("Error al guardar configuracion del bridge HikVision: " . $e->getMessage());
            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        
        $this->redirect('/settings');
    }
    
    /**
     * Guarda/actualiza los costos por capacidad
     */
    public function saveCapacityCosts() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings');
            return;
        }
        
        try {
            require_once APP_PATH . '/models/CapacityCost.php';
            $capacityCostModel = new CapacityCost();
            $db = Database::getInstance();
            
            // Get existing IDs for cleanup
            $existingCosts = $capacityCostModel->getAll(false);
            $existingIds = array_column($existingCosts, 'id');
            $submittedIds = [];
            $savedCount = 0;
            $skippedCount = 0;
            $errors = [];
            
            if (isset($_POST['capacity_costs']) && is_array($_POST['capacity_costs'])) {
                foreach ($_POST['capacity_costs'] as $index => $cost) {
                    $capacityLiters = (int)($cost['capacity_liters'] ?? 0);
                    $costValue = (float)($cost['cost'] ?? 0);
                    $description = trim($cost['description'] ?? '');
                    $isActive = (int)($cost['is_active'] ?? 1);
                    
                    // Validate entries
                    if ($capacityLiters <= 0) {
                        $skippedCount++;
                        $errors[] = "Entrada #" . ($index + 1) . ": La capacidad debe ser mayor que 0";
                        continue;
                    }
                    
                    if ($costValue < 0) {
                        $skippedCount++;
                        $errors[] = "Entrada #" . ($index + 1) . ": El costo no puede ser negativo";
                        continue;
                    }
                    
                    $data = [
                        'capacity_liters' => $capacityLiters,
                        'cost' => $costValue,
                        'description' => $description,
                        'is_active' => $isActive
                    ];
                    
                    if (!empty($cost['id'])) {
                        // Update existing
                        $capacityCostModel->update($cost['id'], $data);
                        $submittedIds[] = (int)$cost['id'];
                    } else {
                        // Create new
                        $newId = $capacityCostModel->create($data);
                        $submittedIds[] = $newId;
                    }
                    $savedCount++;
                }
            }
            
            // Delete removed items
            $toDelete = array_diff($existingIds, $submittedIds);
            $deletedCount = count($toDelete);
            foreach ($toDelete as $id) {
                $capacityCostModel->delete($id);
            }
            
            // Build success message
            $message = "Costos por capacidad actualizados: $savedCount guardado(s)";
            if ($deletedCount > 0) {
                $message .= ", $deletedCount eliminado(s)";
            }
            if ($skippedCount > 0) {
                $message .= ". $skippedCount entrada(s) ignorada(s) por datos inválidos";
            }
            
            if (!empty($errors)) {
                $this->setFlash('warning', $message . '. Errores: ' . implode('; ', array_slice($errors, 0, 3)));
            } else {
                $this->setFlash('success', $message);
            }
        } catch (Exception $e) {
            error_log("Error al guardar costos por capacidad: " . $e->getMessage());
            $this->setFlash('error', 'Error al guardar: ' . $e->getMessage());
        }
        
        $this->redirect('/settings');
    }
}
