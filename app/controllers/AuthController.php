<?php
/**
 * Controlador de Autenticación
 */
require_once APP_PATH . '/controllers/BaseController.php';

class AuthController extends BaseController {
    
    public function index() {
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        $data = [
            'title' => 'Iniciar Sesión',
            'showNav' => false
        ];
        
        $this->view('auth/login', $data);
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
        }
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (Auth::login($username, $password)) {
            $this->setFlash('success', '¡Bienvenido al sistema!');
            $this->redirect('/dashboard');
        } else {
            $this->setFlash('error', 'Credenciales incorrectas. Intente nuevamente.');
            $this->redirect('/login');
        }
    }
    
    public function logout() {
        Auth::logout();
        $this->setFlash('success', 'Sesión cerrada exitosamente.');
        $this->redirect('/login');
    }
}
