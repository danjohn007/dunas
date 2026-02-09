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
                'password' => 'required|min:8',
                'full_name' => 'required',
                'email' => 'required|email|unique:users,email',
                'role' => 'required'
            ];
            
            if ($validator->validate($_POST, $rules)) {
                // Additional password strength validation
                $password = $_POST['password'];
                $passwordErrors = [];
                
                if (strlen($password) < 8) {
                    $passwordErrors[] = 'debe tener al menos 8 caracteres';
                }
                if (!preg_match('/[A-Z]/', $password)) {
                    $passwordErrors[] = 'debe incluir al menos una letra mayúscula';
                }
                if (!preg_match('/[a-z]/', $password)) {
                    $passwordErrors[] = 'debe incluir al menos una letra minúscula';
                }
                if (!preg_match('/[0-9]/', $password)) {
                    $passwordErrors[] = 'debe incluir al menos un número';
                }
                if (!preg_match('/[^A-Za-z0-9]/', $password)) {
                    $passwordErrors[] = 'debe incluir al menos un carácter especial (!@#$%^&*()_+-=[]{}|;:,.<>?)';
                }
                
                if (!empty($passwordErrors)) {
                    $errorMsg = 'La contraseña no es segura: ' . implode(', ', $passwordErrors) . '.';
                    $this->setFlash('error', $errorMsg);
                    
                    $data = [
                        'title' => 'Nuevo Usuario',
                        'showNav' => true,
                        'formData' => $_POST
                    ];
                    
                    $this->view('users/create', $data);
                    return;
                }
                
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
    
    public function changePassword($id) {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users/edit/' . $id);
            return;
        }
        
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $this->setFlash('error', 'Usuario no encontrado.');
            $this->redirect('/users');
            return;
        }
        
        try {
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validate passwords
            if (empty($newPassword)) {
                throw new Exception('La contraseña es requerida.');
            }
            
            if (strlen($newPassword) < 6) {
                throw new Exception('La contraseña debe tener al menos 6 caracteres.');
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('Las contraseñas no coinciden.');
            }
            
            // Update password
            $this->userModel->updatePassword($id, $newPassword);
            
            $this->setFlash('success', 'Contraseña actualizada exitosamente.');
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        
        $this->redirect('/users/edit/' . $id);
    }
}
