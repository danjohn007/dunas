<?php
/**
 * Controlador Settings
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/Settings.php';
require_once APP_PATH . '/models/ShellyDevice.php';
require_once APP_PATH . '/models/ShellyAction.php';

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
        
        $data = [
            'title' => 'Configuraciones del Sistema',
            'settings' => $settings,
            'shellyDevices' => $shellyDevices,
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
            
            // Procesar dispositivos enviados desde el formulario
            if (isset($_POST['devices']) && is_array($_POST['devices'])) {
                foreach ($_POST['devices'] as $i => $d) {
                    // Sanitizar y validar datos
                    $authToken = trim($d['auth_token'] ?? '');
                    $deviceId = trim($d['device_id'] ?? '');
                    $serverHost = trim($d['server_host'] ?? '');
                    
                    // Validar campos requeridos
                    if (empty($authToken) || empty($deviceId) || empty($serverHost)) {
                        continue; // Saltar dispositivos con datos incompletos
                    }
                    
                    $rows[] = [
                        'id' => isset($d['id']) && $d['id'] !== '' ? (int)$d['id'] : null,
                        'name' => trim($d['name'] ?? 'Abrir/Cerrar'),
                        'auth_token' => $authToken,
                        'device_id' => $deviceId,
                        'server_host' => $serverHost,
                        'active_channel' => max(0, min(3, (int)($d['active_channel'] ?? 0))),
                        'channel_count' => max(1, min(4, (int)($d['channel_count'] ?? 4))),
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
            
            $this->setFlash('success', 'Dispositivos Shelly guardados exitosamente.');
        } catch (Exception $e) {
            error_log("Error al guardar dispositivos Shelly: " . $e->getMessage());
            $this->setFlash('error', 'Error al guardar dispositivos Shelly: ' . $e->getMessage());
        }
        
        $this->redirect('/settings');
    }
}
