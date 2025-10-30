<?php
/**
 * Controlador Unit
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/Unit.php';
require_once APP_PATH . '/models/AccessLog.php';

class UnitController extends BaseController {
    
    private $unitModel;
    
    public function __construct() {
        $this->unitModel = new Unit();
    }
    
    public function index() {
        Auth::requireLogin();
        
        $filters = [
            'status' => $_GET['status'] ?? ''
        ];
        
        $units = $this->unitModel->getAll($filters);
        
        $data = [
            'title' => 'Gestión de Unidades',
            'units' => $units,
            'filters' => $filters,
            'showNav' => true
        ];
        
        $this->view('units/index', $data);
    }
    
    public function detail($id) {
        Auth::requireLogin();
        
        $unit = $this->unitModel->getById($id);
        
        if (!$unit) {
            $this->setFlash('error', 'Unidad no encontrada.');
            $this->redirect('/units');
        }
        
        $maintenance = $this->unitModel->getMaintenanceHistory($id, 20);
        
        $data = [
            'title' => 'Detalle de Unidad',
            'unit' => $unit,
            'maintenance' => $maintenance,
            'showNav' => true
        ];
        
        $this->view('units/view', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin', 'supervisor']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new Validator();
            $rules = [
                'plate_number' => 'required|unique:units,plate_number',
                'capacity_liters' => 'required|integer',
                'brand' => 'required',
                'model' => 'required',
                'year' => 'required|integer',
                'serial_number' => 'required|unique:units,serial_number'
            ];
            
            if ($validator->validate($_POST, $rules)) {
                try {
                    $data = $_POST;
                    
                    // Manejar foto
                    if (!empty($_FILES['photo']['name'])) {
                        $uploadResult = FileUpload::upload($_FILES['photo'], UPLOAD_PATH . '/units');
                        if ($uploadResult['success']) {
                            $data['photo'] = $uploadResult['filename'];
                        }
                    }
                    
                    $this->unitModel->create($data);
                    $this->setFlash('success', 'Unidad registrada exitosamente.');
                    $this->redirect('/units');
                } catch (Exception $e) {
                    $this->setFlash('error', 'Error al registrar la unidad: ' . $e->getMessage());
                }
            } else {
                $this->setFlash('error', 'Error de validación. Verifique los datos ingresados.');
            }
        }
        
        $data = [
            'title' => 'Nueva Unidad',
            'showNav' => true
        ];
        
        $this->view('units/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin', 'supervisor']);
        
        $unit = $this->unitModel->getById($id);
        
        if (!$unit) {
            $this->setFlash('error', 'Unidad no encontrada.');
            $this->redirect('/units');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new Validator();
            $rules = [
                'plate_number' => 'required',
                'capacity_liters' => 'required|integer',
                'brand' => 'required',
                'model' => 'required',
                'year' => 'required|integer',
                'serial_number' => 'required'
            ];
            
            if ($validator->validate($_POST, $rules)) {
                try {
                    $data = $_POST;
                    
                    // Manejar foto
                    if (!empty($_FILES['photo']['name'])) {
                        $uploadResult = FileUpload::upload($_FILES['photo'], UPLOAD_PATH . '/units');
                        if ($uploadResult['success']) {
                            // Eliminar foto anterior
                            if (!empty($unit['photo'])) {
                                FileUpload::delete(UPLOAD_PATH . '/units/' . $unit['photo']);
                            }
                            $data['photo'] = $uploadResult['filename'];
                        }
                    }
                    
                    $this->unitModel->update($id, $data);
                    $this->setFlash('success', 'Unidad actualizada exitosamente.');
                    $this->redirect('/units');
                } catch (Exception $e) {
                    $this->setFlash('error', 'Error al actualizar la unidad: ' . $e->getMessage());
                }
            } else {
                $this->setFlash('error', 'Error de validación. Verifique los datos ingresados.');
            }
        }
        
        $data = [
            'title' => 'Editar Unidad',
            'unit' => $unit,
            'showNav' => true
        ];
        
        $this->view('units/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin']);
        
        try {
            $unit = $this->unitModel->getById($id);
            
            if (!$unit) {
                $this->setFlash('error', 'Unidad no encontrada.');
                $this->redirect('/units');
                return;
            }
            
            // Verificar si la unidad tiene registros de acceso
            $accessModel = new AccessLog();
            $accessLogs = $accessModel->getAll(['unit_id' => $id]);
            
            if (!empty($accessLogs)) {
                $this->setFlash('error', 'No se puede eliminar la unidad porque tiene registros de acceso asociados. En su lugar, puede cambiar el estado a "inactivo".');
                $this->redirect('/units');
                return;
            }
            
            // Eliminar foto si existe
            if (!empty($unit['photo'])) {
                FileUpload::delete(UPLOAD_PATH . '/units/' . $unit['photo']);
            }
            
            $this->unitModel->delete($id);
            $this->setFlash('success', 'Unidad eliminada exitosamente.');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al eliminar la unidad: ' . $e->getMessage());
        }
        
        $this->redirect('/units');
    }
    
    public function addMaintenance($id) {
        Auth::requireRole(['admin', 'supervisor']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $_POST['unit_id'] = $id;
                $this->unitModel->addMaintenance($_POST);
                $this->setFlash('success', 'Mantenimiento registrado exitosamente.');
                $this->redirect('/units/detail/' . $id);
            } catch (Exception $e) {
                $this->setFlash('error', 'Error al registrar el mantenimiento: ' . $e->getMessage());
                $this->redirect('/units/detail/' . $id);
            }
        }
    }
}
