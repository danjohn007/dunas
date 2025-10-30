<?php
/**
 * Controlador Client
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/Client.php';

class ClientController extends BaseController {
    
    private $clientModel;
    
    public function __construct() {
        $this->clientModel = new Client();
    }
    
    public function index() {
        Auth::requireLogin();
        
        $filters = [
            'client_type' => $_GET['client_type'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $clients = $this->clientModel->getAll($filters);
        
        $data = [
            'title' => 'Gestión de Clientes',
            'clients' => $clients,
            'filters' => $filters,
            'showNav' => true
        ];
        
        $this->view('clients/index', $data);
    }
    
    public function detail($id) {
        Auth::requireLogin();
        
        $client = $this->clientModel->getById($id);
        
        if (!$client) {
            $this->setFlash('error', 'Cliente no encontrado.');
            $this->redirect('/clients');
        }
        
        $transactions = $this->clientModel->getTransactionHistory($id, 20);
        
        $data = [
            'title' => 'Detalle de Cliente',
            'client' => $client,
            'transactions' => $transactions,
            'showNav' => true
        ];
        
        $this->view('clients/view', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new Validator();
            $rules = [
                'business_name' => 'required',
                'rfc_curp' => 'required',
                'address' => 'required',
                'phone' => 'required|phone',
                'email' => 'required|email',
                'client_type' => 'required'
            ];
            
            if ($validator->validate($_POST, $rules)) {
                try {
                    $this->clientModel->create($_POST);
                    $this->setFlash('success', 'Cliente registrado exitosamente.');
                    $this->redirect('/clients');
                } catch (Exception $e) {
                    $this->setFlash('error', 'Error al registrar el cliente: ' . $e->getMessage());
                }
            } else {
                $this->setFlash('error', 'Error de validación. Verifique los datos ingresados.');
            }
        }
        
        $data = [
            'title' => 'Nuevo Cliente',
            'showNav' => true
        ];
        
        $this->view('clients/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $client = $this->clientModel->getById($id);
        
        if (!$client) {
            $this->setFlash('error', 'Cliente no encontrado.');
            $this->redirect('/clients');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new Validator();
            $rules = [
                'business_name' => 'required',
                'rfc_curp' => 'required',
                'address' => 'required',
                'phone' => 'required|phone',
                'email' => 'required|email',
                'client_type' => 'required'
            ];
            
            if ($validator->validate($_POST, $rules)) {
                try {
                    $this->clientModel->update($id, $_POST);
                    $this->setFlash('success', 'Cliente actualizado exitosamente.');
                    $this->redirect('/clients');
                } catch (Exception $e) {
                    $this->setFlash('error', 'Error al actualizar el cliente: ' . $e->getMessage());
                }
            } else {
                $this->setFlash('error', 'Error de validación. Verifique los datos ingresados.');
            }
        }
        
        $data = [
            'title' => 'Editar Cliente',
            'client' => $client,
            'showNav' => true
        ];
        
        $this->view('clients/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin']);
        
        try {
            $this->clientModel->delete($id);
            $this->setFlash('success', 'Cliente eliminado exitosamente.');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al eliminar el cliente: ' . $e->getMessage());
        }
        
        $this->redirect('/clients');
    }
}
