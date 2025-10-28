<?php
/**
 * Controlador Profile
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/User.php';

class ProfileController extends BaseController {
    
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function index() {
        Auth::requireLogin();
        
        $userId = Auth::user()['id'];
        $user = $this->userModel->getById($userId);
        
        $data = [
            'title' => 'Mi Perfil',
            'user' => $user,
            'showNav' => true
        ];
        
        $this->view('profile/index', $data);
    }
    
    public function update() {
        Auth::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Auth::user()['id'];
            
            try {
                $updateData = [
                    'full_name' => $_POST['full_name'],
                    'email' => $_POST['email']
                ];
                
                // Solo actualizar contraseña si se proporcionó
                if (!empty($_POST['new_password'])) {
                    // Verificar contraseña actual
                    $user = $this->userModel->getById($userId);
                    if (!password_verify($_POST['current_password'], $user['password'])) {
                        $this->setFlash('error', 'La contraseña actual es incorrecta.');
                        $this->redirect('/profile');
                        return;
                    }
                    
                    // Validar nueva contraseña
                    if ($_POST['new_password'] !== $_POST['confirm_password']) {
                        $this->setFlash('error', 'Las contraseñas no coinciden.');
                        $this->redirect('/profile');
                        return;
                    }
                    
                    $updateData['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                }
                
                $this->userModel->update($userId, $updateData);
                
                // Actualizar sesión
                $_SESSION['user']['full_name'] = $_POST['full_name'];
                $_SESSION['user']['email'] = $_POST['email'];
                
                $this->setFlash('success', 'Perfil actualizado exitosamente.');
            } catch (Exception $e) {
                $this->setFlash('error', 'Error al actualizar el perfil: ' . $e->getMessage());
            }
        }
        
        $this->redirect('/profile');
    }
}
