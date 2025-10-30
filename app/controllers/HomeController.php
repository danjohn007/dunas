<?php
/**
 * Controlador Home
 */
require_once APP_PATH . '/controllers/BaseController.php';

class HomeController extends BaseController {
    
    public function index() {
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        $data = [
            'title' => 'Bienvenido',
            'showNav' => false
        ];
        
        $this->view('home/index', $data);
    }
    
    public function notFound() {
        http_response_code(404);
        $data = [
            'title' => 'Página no encontrada',
            'showNav' => Auth::isLoggedIn()
        ];
        $this->view('home/404', $data);
    }
    
    public function accessDenied() {
        http_response_code(403);
        $data = [
            'title' => 'Acceso denegado',
            'showNav' => Auth::isLoggedIn()
        ];
        $this->view('home/403', $data);
    }
}
