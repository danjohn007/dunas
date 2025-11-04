<?php
/**
 * Controlador Access
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/AccessLog.php';
require_once APP_PATH . '/models/Driver.php';
require_once APP_PATH . '/models/Unit.php';
require_once APP_PATH . '/models/Client.php';
require_once APP_PATH . '/helpers/HikvisionAPI.php';
require_once APP_PATH . '/services/ShellyActionService.php';

class AccessController extends BaseController {
    
    private $accessModel;
    private $driverModel;
    private $unitModel;
    private $clientModel;
    
    public function __construct() {
        $this->accessModel = new AccessLog();
        $this->driverModel = new Driver();
        $this->unitModel = new Unit();
        $this->clientModel = new Client();
    }
    
    /**
     * Ejecuta una acción Shelly con fallback al método legacy
     * @param string $action Código de la acción ('abrir_cerrar', etc.)
     * @param string $mode Modo de operación ('open' o 'close')
     * @return array Resultado de la operación
     */
    private function executeShellyAction($action, $mode) {
        try {
            $db = Database::getInstance();
            return ShellyActionService::execute($db, $action, $mode);
        } catch (Exception $e) {
            // Si no hay dispositivos configurados, usar método legacy
            error_log("ShellyActionService error, usando método legacy: " . $e->getMessage());
            if ($mode === 'open') {
                return ShellyAPI::openBarrier();
            } else {
                return ShellyAPI::closeBarrier();
            }
        }
    }
    
    public function index() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $filters = [
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        $accessLogs = $this->accessModel->getAll($filters);
        $inProgress = $this->accessModel->getInProgress();
        
        $data = [
            'title' => 'Control de Acceso',
            'accessLogs' => $accessLogs,
            'inProgress' => $inProgress,
            'filters' => $filters,
            'showNav' => true
        ];
        
        $this->view('access/index', $data);
    }
    
    public function detail($id) {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $access = $this->accessModel->getById($id);
        
        if (!$access) {
            $this->setFlash('error', 'Registro de acceso no encontrado.');
            $this->redirect('/access');
        }
        
        $data = [
            'title' => 'Detalle de Acceso',
            'access' => $access,
            'showNav' => true
        ];
        
        $this->view('access/view', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new Validator();
            $rules = [
                'driver_id' => 'required|integer',
                'unit_id' => 'required|integer',
                'client_id' => 'required|integer'
            ];
            
            if ($validator->validate($_POST, $rules)) {
                try {
                    $data = $_POST;
                    
                    // Leer placa desde cámara Hikvision
                    $cameraReading = HikvisionAPI::readLicensePlate();
                    if ($cameraReading['success'] && !empty($cameraReading['plate'])) {
                        $data['license_plate_reading'] = $cameraReading['plate'];
                        
                        // Obtener placa de la unidad seleccionada
                        $unit = $this->unitModel->getById($data['unit_id']);
                        if ($unit) {
                            $data['plate_discrepancy'] = HikvisionAPI::hasDiscrepancy(
                                $unit['plate_number'], 
                                $cameraReading['plate']
                            );
                        }
                    }
                    
                    $accessId = $this->accessModel->create($data);
                    
                    // Abrir barrera con Shelly Relay usando el nuevo servicio
                    $shellyResult = $this->executeShellyAction('abrir_cerrar', 'open');
                    
                    $message = 'Acceso registrado exitosamente';
                    if (!empty($data['license_plate_reading'])) {
                        $message .= '. Placa leída por cámara: ' . $data['license_plate_reading'];
                        if (!empty($data['plate_discrepancy'])) {
                            $message .= ' (⚠️ DISCREPANCIA DETECTADA)';
                        }
                    }
                    
                    if (!$shellyResult['success']) {
                        $errorDetails = isset($shellyResult['error']) ? $shellyResult['error'] : 'Error desconocido';
                        $errorUrl = isset($shellyResult['url']) ? ' (URL: ' . $shellyResult['url'] . ')' : '';
                        $message .= ' pero no se pudo abrir la barrera automáticamente. ';
                        $message .= 'Error: ' . $errorDetails . $errorUrl;
                        $message .= '. Por favor, verifique que el dispositivo Shelly esté encendido y conectado a la red, ';
                        $message .= 'y que la URL configurada sea correcta en Configuraciones del Sistema.';
                        $this->setFlash('warning', $message);
                    } else {
                        $message .= ' y barrera abierta exitosamente.';
                        $this->setFlash('success', $message);
                    }
                    
                    $this->redirect('/access/detail/' . $accessId);
                } catch (Exception $e) {
                    $this->setFlash('error', 'Error al registrar el acceso: ' . $e->getMessage());
                }
            } else {
                $this->setFlash('error', 'Error de validación. Verifique los datos ingresados.');
            }
        }
        
        // Obtener datos para formulario
        $drivers = $this->driverModel->getAll(['status' => 'active']);
        $units = $this->unitModel->getAll(['status' => 'active']);
        $clients = $this->clientModel->getAll(['status' => 'active']);
        
        $data = [
            'title' => 'Registrar Entrada',
            'drivers' => $drivers,
            'units' => $units,
            'clients' => $clients,
            'showNav' => true
        ];
        
        $this->view('access/create', $data);
    }
    
    public function registerExit($id) {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $access = $this->accessModel->getById($id);
        
        if (!$access) {
            $this->setFlash('error', 'Registro de acceso no encontrado.');
            $this->redirect('/access');
        }
        
        if ($access['status'] !== 'in_progress') {
            $this->setFlash('error', 'Este acceso ya ha sido completado o cancelado.');
            $this->redirect('/access');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new Validator();
            $rules = [
                'liters_supplied' => 'required|integer'
            ];
            
            if ($validator->validate($_POST, $rules)) {
                try {
                    $this->accessModel->registerExit($id, $_POST['liters_supplied']);
                    
                    // Cerrar barrera con Shelly Relay usando el nuevo servicio
                    $shellyResult = $this->executeShellyAction('abrir_cerrar', 'close');
                    
                    if (!$shellyResult['success']) {
                        $errorDetails = isset($shellyResult['error']) ? $shellyResult['error'] : 'Error desconocido';
                        $errorUrl = isset($shellyResult['url']) ? ' (URL: ' . $shellyResult['url'] . ')' : '';
                        $message = 'Salida registrada pero no se pudo cerrar la barrera automáticamente. ';
                        $message .= 'Error: ' . $errorDetails . $errorUrl;
                        $message .= '. Por favor, verifique que el dispositivo Shelly esté encendido y conectado a la red, ';
                        $message .= 'y que la URL configurada sea correcta en Configuraciones del Sistema.';
                        $this->setFlash('warning', $message);
                    } else {
                        $this->setFlash('success', 'Salida registrada y barrera cerrada exitosamente.');
                    }
                    
                    $this->redirect('/access/detail/' . $id);
                } catch (Exception $e) {
                    $this->setFlash('error', 'Error al registrar la salida: ' . $e->getMessage());
                }
            } else {
                $this->setFlash('error', 'Error de validación. Verifique los datos ingresados.');
            }
        }
        
        $data = [
            'title' => 'Registrar Salida',
            'access' => $access,
            'showNav' => true
        ];
        
        $this->view('access/exit', $data);
    }
    
    public function cancel($id) {
        Auth::requireRole(['admin', 'supervisor']);
        
        try {
            $this->accessModel->cancel($id);
            $this->setFlash('success', 'Acceso cancelado exitosamente.');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al cancelar el acceso: ' . $e->getMessage());
        }
        
        $this->redirect('/access');
    }
    
    public function openBarrier() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $result = $this->executeShellyAction('abrir_cerrar', 'open');
        
        if ($result['success']) {
            $this->json([
                'success' => true, 
                'message' => 'Barrera abierta exitosamente.'
            ]);
        } else {
            $errorMessage = 'Error al abrir la barrera';
            if (isset($result['error'])) {
                $errorMessage .= ': ' . $result['error'];
            }
            
            $this->json([
                'success' => false, 
                'message' => $errorMessage,
                'error' => $result['error'] ?? 'Error desconocido'
            ], 500);
        }
    }
    
    public function closeBarrier() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $result = $this->executeShellyAction('abrir_cerrar', 'close');
        
        if ($result['success']) {
            $this->json([
                'success' => true, 
                'message' => 'Barrera cerrada exitosamente.'
            ]);
        } else {
            $errorMessage = 'Error al cerrar la barrera';
            if (isset($result['error'])) {
                $errorMessage .= ': ' . $result['error'];
            }
            
            $this->json([
                'success' => false, 
                'message' => $errorMessage,
                'error' => $result['error'] ?? 'Error desconocido'
            ], 500);
        }
    }
    
    public function quickRegistration() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $data = [
            'title' => 'Registro Rápido',
            'showNav' => true
        ];
        
        $this->view('access/quick_registration', $data);
    }
    
    public function searchUnit() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $plateNumber = $_GET['plate'] ?? '';
        
        if (empty($plateNumber)) {
            $this->json(['success' => false, 'message' => 'Número de placa requerido']);
            return;
        }
        
        $unit = $this->unitModel->findByPlateNumber($plateNumber);
        
        if ($unit) {
            // Obtener último registro de entrada para esta placa
            $lastEntry = $this->accessModel->getLastEntryByPlate($plateNumber);
            
            // Obtener choferes del cliente de la unidad
            $drivers = [];
            if (!empty($unit['client_id'])) {
                $drivers = $this->unitModel->getDriversByClient($unit['client_id']);
            }
            
            $this->json([
                'success' => true, 
                'exists' => true, 
                'unit' => $unit,
                'lastEntry' => $lastEntry,
                'drivers' => $drivers
            ]);
        } else {
            $this->json(['success' => true, 'exists' => false]);
        }
    }
    
    public function quickEntry() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/access/quickRegistration');
            return;
        }
        
        try {
            $plateNumber = $_POST['plate_number'] ?? '';
            $clientId = null;
            $driverId = null;
            $unitId = null;
            
            // Buscar o crear cliente
            if (!empty($_POST['client_id'])) {
                $clientId = $_POST['client_id'];
            } else {
                // Crear nuevo cliente
                $clientData = [
                    'business_name' => $_POST['client_name'],
                    'rfc_curp' => $_POST['client_rfc'] ?? 'XAXX010101000',
                    'address' => $_POST['client_address'] ?? 'Sin dirección',
                    'phone' => $_POST['client_phone'],
                    'email' => $_POST['client_email'] ?? 'sin-email@dunas.com',
                    'client_type' => $_POST['client_type'] ?? 'commercial',
                    'status' => 'active'
                ];
                $clientId = $this->clientModel->create($clientData);
            }
            
            // Buscar o crear chofer
            if (!empty($_POST['driver_id'])) {
                $driverId = $_POST['driver_id'];
            } else {
                // Crear nuevo chofer
                $driverData = [
                    'client_id' => $clientId,
                    'full_name' => $_POST['driver_name'],
                    'license_number' => !empty($_POST['driver_license']) ? $_POST['driver_license'] : null,
                    'license_expiry' => !empty($_POST['driver_license_expiry']) ? $_POST['driver_license_expiry'] : null,
                    'phone' => $_POST['driver_phone'],
                    'status' => 'active'
                ];
                $driverId = $this->driverModel->create($driverData);
            }
            
            // Buscar o crear unidad
            $unit = $this->unitModel->findByPlateNumber($plateNumber);
            
            if ($unit) {
                $unitId = $unit['id'];
            } else {
                // Crear nueva unidad
                $unitData = [
                    'client_id' => $clientId,
                    'driver_id' => $driverId,
                    'plate_number' => $plateNumber,
                    'capacity_liters' => $_POST['capacity_liters'],
                    'brand' => $_POST['brand'] ?? 'Genérico',
                    'model' => $_POST['model'] ?? 'Estándar',
                    'year' => !empty($_POST['year']) ? $_POST['year'] : null,
                    'serial_number' => !empty($_POST['serial_number']) ? $_POST['serial_number'] : null,
                    'status' => 'active'
                ];
                $unitId = $this->unitModel->create($unitData);
            }
            
            // Registrar entrada
            $accessData = [
                'driver_id' => $driverId,
                'unit_id' => $unitId,
                'client_id' => $clientId
            ];
            
            // Leer placa desde cámara Hikvision
            $cameraReading = HikvisionAPI::readLicensePlate();
            if ($cameraReading['success'] && !empty($cameraReading['plate'])) {
                $accessData['license_plate_reading'] = $cameraReading['plate'];
                $accessData['plate_discrepancy'] = HikvisionAPI::hasDiscrepancy(
                    $plateNumber, 
                    $cameraReading['plate']
                );
            }
            
            $accessId = $this->accessModel->create($accessData);
            
            // Abrir barrera usando el nuevo servicio
            $shellyResult = $this->executeShellyAction('abrir_cerrar', 'open');
            
            $message = 'Entrada registrada exitosamente';
            if (!empty($accessData['license_plate_reading'])) {
                $message .= '. Placa leída por cámara: ' . $accessData['license_plate_reading'];
                if (!empty($accessData['plate_discrepancy'])) {
                    $message .= ' (⚠️ DISCREPANCIA DETECTADA)';
                }
            }
            
            if (!$shellyResult['success']) {
                $message .= ' pero no se pudo abrir la barrera automáticamente.';
                $this->setFlash('warning', $message);
            } else {
                $message .= ' y barrera abierta exitosamente.';
                $this->setFlash('success', $message);
            }
            
            $this->redirect('/access/printTicket/' . $accessId);
            
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al registrar la entrada: ' . $e->getMessage());
            $this->redirect('/access/quickRegistration');
        }
    }
    
    public function scanExit() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $data = [
            'title' => 'Escanear Salida',
            'showNav' => true
        ];
        
        $this->view('access/scan_exit', $data);
    }
    
    public function processExit() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $barcode = $_POST['barcode'] ?? '';
        
        if (empty($barcode)) {
            $this->json(['success' => false, 'message' => 'Código de barras requerido']);
            return;
        }
        
        try {
            // Buscar acceso por código de ticket
            $access = $this->accessModel->getByTicket($barcode);
            
            if (!$access) {
                $this->json(['success' => false, 'message' => 'Código de barras no válido o no encontrado']);
                return;
            }
            
            if ($access['status'] !== 'in_progress') {
                $this->json(['success' => false, 'message' => 'Este acceso ya fue completado o cancelado']);
                return;
            }
            
            // Registrar salida con capacidad máxima de la unidad
            $this->accessModel->registerExit($access['id'], $access['capacity_liters']);
            
            // Cerrar barrera usando el nuevo servicio
            $shellyResult = $this->executeShellyAction('abrir_cerrar', 'close');
            
            $message = 'Salida registrada exitosamente con ' . number_format($access['capacity_liters']) . ' litros.';
            
            if (!$shellyResult['success']) {
                $message .= ' La barrera debe cerrarse manualmente.';
            }
            
            $this->json([
                'success' => true, 
                'message' => $message,
                'access' => $access
            ]);
            
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    public function printTicket($id) {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $access = $this->accessModel->getById($id);
        
        if (!$access) {
            $this->setFlash('error', 'Registro de acceso no encontrado.');
            $this->redirect('/access');
        }
        
        $data = [
            'title' => 'Ticket de Entrada',
            'access' => $access,
            'showNav' => false
        ];
        
        $this->view('access/print_ticket', $data);
    }
}
