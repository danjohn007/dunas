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
}
