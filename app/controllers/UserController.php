<?php
/**
 * Controlador User
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/User.php';

class UserController extends BaseController {
    
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function index() {
        Auth::requireRole(['admin']);
        
        $filters = [
            'role' => $_GET['role'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $users = $this->userModel->getAll($filters);
        
        $data = [
            'title' => 'Gestión de Usuarios',
            'users' => $users,
            'filters' => $filters,
            'showNav' => true
        ];
        
        $this->view('users/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new Validator();
            $rules = [
                'username' => 'required|min:4|unique:users,username',
                'password' => 'required|min:6',
                'full_name' => 'required',
                'email' => 'required|email|unique:users,email',
                'role' => 'required'
            ];
            
            if ($validator->validate($_POST, $rules)) {
                try {
                    $this->userModel->create($_POST);
                    $this->setFlash('success', 'Usuario creado exitosamente.');
                    $this->redirect('/users');
                } catch (Exception $e) {
                    $this->setFlash('error', 'Error al crear el usuario: ' . $e->getMessage());
                }
            } else {
                $this->setFlash('error', 'Error de validación. Verifique los datos ingresados.');
            }
        }
        
        $data = [
            'title' => 'Nuevo Usuario',
            'showNav' => true
        ];
        
        $this->view('users/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin']);
        
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $this->setFlash('error', 'Usuario no encontrado.');
            $this->redirect('/users');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new Validator();
            $rules = [
                'full_name' => 'required',
                'email' => 'required|email',
                'role' => 'required'
            ];
            
            if ($validator->validate($_POST, $rules)) {
                try {
                    $this->userModel->update($id, $_POST);
                    $this->setFlash('success', 'Usuario actualizado exitosamente.');
                    $this->redirect('/users');
                } catch (Exception $e) {
                    $this->setFlash('error', 'Error al actualizar el usuario: ' . $e->getMessage());
                }
            } else {
                $this->setFlash('error', 'Error de validación. Verifique los datos ingresados.');
            }
        }
        
        $data = [
            'title' => 'Editar Usuario',
            'user' => $user,
            'showNav' => true
        ];
        
        $this->view('users/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin']);
        
        try {
            $this->userModel->delete($id);
            $this->setFlash('success', 'Usuario eliminado exitosamente.');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
        
        $this->redirect('/users');
    }
}
