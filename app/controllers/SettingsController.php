<?php
/**
 * Controlador Settings
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/Settings.php';

class SettingsController extends BaseController {
    
    private $settingsModel;
    
    public function __construct() {
        $this->settingsModel = new Settings();
    }
    
    public function index() {
        Auth::requireRole(['admin']);
        
        $settings = $this->settingsModel->getAll();
        
        $data = [
            'title' => 'Configuraciones del Sistema',
            'settings' => $settings,
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
                    $upload = new FileUpload();
                    $logoPath = $upload->uploadImage($_FILES['site_logo'], 'logos');
                    if ($logoPath) {
                        $_POST['site_logo'] = $logoPath;
                    }
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
}
