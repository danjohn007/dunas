<?php
/**
 * Controlador Access
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/AccessLog.php';
require_once APP_PATH . '/models/Driver.php';
require_once APP_PATH . '/models/Unit.php';
require_once APP_PATH . '/models/Client.php';

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
                    $accessId = $this->accessModel->create($_POST);
                    
                    // Abrir barrera con Shelly Relay
                    $shellyResult = ShellyAPI::openBarrier();
                    
                    if (!$shellyResult['success']) {
                        $this->setFlash('warning', 'Acceso registrado pero no se pudo abrir la barrera automáticamente.');
                    } else {
                        $this->setFlash('success', 'Acceso registrado y barrera abierta exitosamente.');
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
                    
                    // Cerrar barrera con Shelly Relay
                    $shellyResult = ShellyAPI::closeBarrier();
                    
                    if (!$shellyResult['success']) {
                        $this->setFlash('warning', 'Salida registrada pero no se pudo cerrar la barrera automáticamente.');
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
        
        $result = ShellyAPI::openBarrier();
        
        if ($result['success']) {
            $this->json(['success' => true, 'message' => 'Barrera abierta exitosamente.']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al abrir la barrera.'], 500);
        }
    }
    
    public function closeBarrier() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $result = ShellyAPI::closeBarrier();
        
        if ($result['success']) {
            $this->json(['success' => true, 'message' => 'Barrera cerrada exitosamente.']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al cerrar la barrera.'], 500);
        }
    }
}
