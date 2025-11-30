<?php
/**
 * Controlador Devices - Módulo de Dispositivos IoT
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/ShellyDevice.php';
require_once APP_PATH . '/models/ShellyAction.php';
require_once APP_PATH . '/models/HikvisionDevice.php';

class DevicesController extends BaseController {
    
    public function __construct() {
        // Constructor
    }
    
    /**
     * Lista de dispositivos
     */
    public function index() {
        Auth::requireRole(['admin']);
        
        $db = Database::getInstance();
        
        // Obtener dispositivos Shelly configurados
        $shellyDevices = ShellyDevice::getAll($db);
        
        // Obtener acciones para cada dispositivo
        foreach ($shellyDevices as &$device) {
            $device['actions'] = ShellyAction::getByDevice($db, $device['id']);
        }
        
        // Obtener dispositivos HikVision configurados
        $hikvisionDevices = HikvisionDevice::getAll($db);
        
        $data = [
            'title' => 'Dispositivos',
            'shellyDevices' => $shellyDevices,
            'hikvisionDevices' => $hikvisionDevices,
            'showNav' => true
        ];
        
        $this->view('devices/index', $data);
    }
    
    /**
     * Guarda/actualiza los dispositivos Shelly
     */
    public function saveShellyDevices() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/devices');
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
        
        $this->redirect('/devices');
    }
    
    /**
     * Guarda/actualiza los dispositivos HikVision
     */
    public function saveHikvisionDevices() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/devices');
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
        
        $this->redirect('/devices');
    }
}
