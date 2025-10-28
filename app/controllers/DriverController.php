<?php
/**
 * Controlador Driver
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/Driver.php';

class DriverController extends BaseController {
    
    private $driverModel;
    
    public function __construct() {
        $this->driverModel = new Driver();
    }
    
    public function index() {
        Auth::requireLogin();
        
        $filters = [
            'status' => $_GET['status'] ?? ''
        ];
        
        $drivers = $this->driverModel->getAll($filters);
        
        // Obtener choferes con licencias próximas a vencer
        $expiringLicenses = $this->driverModel->getExpiringLicenses(30);
        
        $data = [
            'title' => 'Gestión de Choferes',
            'drivers' => $drivers,
            'expiringLicenses' => $expiringLicenses,
            'filters' => $filters,
            'showNav' => true
        ];
        
        $this->view('drivers/index', $data);
    }
    
    public function detail($id) {
        Auth::requireLogin();
        
        $driver = $this->driverModel->getById($id);
        
        if (!$driver) {
            $this->setFlash('error', 'Chofer no encontrado.');
            $this->redirect('/drivers');
        }
        
        $assignedUnit = $this->driverModel->getAssignedUnit($id);
        
        $data = [
            'title' => 'Detalle de Chofer',
            'driver' => $driver,
            'assignedUnit' => $assignedUnit,
            'showNav' => true
        ];
        
        $this->view('drivers/view', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin', 'supervisor']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new Validator();
            $rules = [
                'full_name' => 'required',
                'license_number' => 'required|unique:drivers,license_number',
                'license_expiry' => 'required|date',
                'phone' => 'required'
            ];
            
            if ($validator->validate($_POST, $rules)) {
                try {
                    $data = $_POST;
                    
                    // Manejar foto
                    if (!empty($_FILES['photo']['name'])) {
                        $uploadResult = FileUpload::upload($_FILES['photo'], UPLOAD_PATH . '/drivers');
                        if ($uploadResult['success']) {
                            $data['photo'] = $uploadResult['filename'];
                        }
                    }
                    
                    $this->driverModel->create($data);
                    $this->setFlash('success', 'Chofer registrado exitosamente.');
                    $this->redirect('/drivers');
                } catch (Exception $e) {
                    $this->setFlash('error', 'Error al registrar el chofer: ' . $e->getMessage());
                }
            } else {
                $this->setFlash('error', 'Error de validación. Verifique los datos ingresados.');
            }
        }
        
        $data = [
            'title' => 'Nuevo Chofer',
            'showNav' => true
        ];
        
        $this->view('drivers/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin', 'supervisor']);
        
        $driver = $this->driverModel->getById($id);
        
        if (!$driver) {
            $this->setFlash('error', 'Chofer no encontrado.');
            $this->redirect('/drivers');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new Validator();
            $rules = [
                'full_name' => 'required',
                'license_number' => 'required',
                'license_expiry' => 'required|date',
                'phone' => 'required'
            ];
            
            if ($validator->validate($_POST, $rules)) {
                try {
                    $data = $_POST;
                    
                    // Manejar foto
                    if (!empty($_FILES['photo']['name'])) {
                        $uploadResult = FileUpload::upload($_FILES['photo'], UPLOAD_PATH . '/drivers');
                        if ($uploadResult['success']) {
                            // Eliminar foto anterior
                            if (!empty($driver['photo'])) {
                                FileUpload::delete(UPLOAD_PATH . '/drivers/' . $driver['photo']);
                            }
                            $data['photo'] = $uploadResult['filename'];
                        }
                    }
                    
                    $this->driverModel->update($id, $data);
                    $this->setFlash('success', 'Chofer actualizado exitosamente.');
                    $this->redirect('/drivers');
                } catch (Exception $e) {
                    $this->setFlash('error', 'Error al actualizar el chofer: ' . $e->getMessage());
                }
            } else {
                $this->setFlash('error', 'Error de validación. Verifique los datos ingresados.');
            }
        }
        
        $data = [
            'title' => 'Editar Chofer',
            'driver' => $driver,
            'showNav' => true
        ];
        
        $this->view('drivers/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin']);
        
        try {
            $driver = $this->driverModel->getById($id);
            
            // Eliminar foto si existe
            if (!empty($driver['photo'])) {
                FileUpload::delete(UPLOAD_PATH . '/drivers/' . $driver['photo']);
            }
            
            $this->driverModel->delete($id);
            $this->setFlash('success', 'Chofer eliminado exitosamente.');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al eliminar el chofer: ' . $e->getMessage());
        }
        
        $this->redirect('/drivers');
    }
    
    public function assignUnit($id) {
        Auth::requireRole(['admin', 'supervisor']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->driverModel->assignUnit($id, $_POST['unit_id']);
                $this->setFlash('success', 'Unidad asignada exitosamente.');
            } catch (Exception $e) {
                $this->setFlash('error', 'Error al asignar la unidad: ' . $e->getMessage());
            }
        }
        
        $this->redirect('/drivers/detail/' . $id);
    }
}
