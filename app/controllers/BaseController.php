<?php
/**
 * Controlador Base
 */
class BaseController {
    
    protected function view($viewPath, $data = []) {
        // Cargar configuraciones del sistema
        require_once APP_PATH . '/models/Settings.php';
        $settingsModel = new Settings();
        $settings = $settingsModel->getAll();
        
        // Agregar settings a los datos
        $data['systemSettings'] = $settings;
        
        extract($data);
        
        // Incluir layout
        ob_start();
        require_once APP_PATH . '/views/' . $viewPath . '.php';
        $content = ob_get_clean();
        
        require_once APP_PATH . '/views/layouts/main.php';
    }
    
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }
    
    protected function back() {
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL);
        exit;
    }
    
    protected function setFlash($type, $message) {
        Session::setFlash($type, $message);
    }
    
    protected function getFlash($type) {
        return Session::getFlash($type);
    }
}
