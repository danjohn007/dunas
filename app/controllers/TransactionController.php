<?php
/**
 * Controlador Transaction
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/Transaction.php';
require_once APP_PATH . '/models/AccessLog.php';
require_once APP_PATH . '/models/Client.php';

class TransactionController extends BaseController {
    
    private $transactionModel;
    private $accessModel;
    private $clientModel;
    
    public function __construct() {
        $this->transactionModel = new Transaction();
        $this->accessModel = new AccessLog();
        $this->clientModel = new Client();
    }
    
    public function index() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $filters = [
            'payment_status' => $_GET['payment_status'] ?? '',
            'payment_method' => $_GET['payment_method'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        $transactions = $this->transactionModel->getAll($filters);
        
        $data = [
            'title' => 'Gestión de Transacciones',
            'transactions' => $transactions,
            'filters' => $filters,
            'showNav' => true
        ];
        
        $this->view('transactions/index', $data);
    }
    
    public function view($id) {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $transaction = $this->transactionModel->getById($id);
        
        if (!$transaction) {
            $this->setFlash('error', 'Transacción no encontrada.');
            $this->redirect('/transactions');
        }
        
        $data = [
            'title' => 'Detalle de Transacción',
            'transaction' => $transaction,
            'showNav' => true
        ];
        
        $this->view('transactions/view', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        // Obtener ID de acceso si se proporciona
        $accessId = $_GET['access_id'] ?? null;
        $access = null;
        
        if ($accessId) {
            $access = $this->accessModel->getById($accessId);
            
            if (!$access || $access['status'] !== 'completed') {
                $this->setFlash('error', 'El acceso debe estar completado para crear una transacción.');
                $this->redirect('/access');
            }
            
            // Verificar si ya existe una transacción para este acceso
            $existing = $this->transactionModel->getByAccessLog($accessId);
            if ($existing) {
                $this->setFlash('error', 'Ya existe una transacción para este acceso.');
                $this->redirect('/transactions/view/' . $existing['id']);
            }
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new Validator();
            $rules = [
                'access_log_id' => 'required|integer',
                'client_id' => 'required|integer',
                'liters_supplied' => 'required|integer',
                'price_per_liter' => 'required|numeric',
                'payment_method' => 'required'
            ];
            
            if ($validator->validate($_POST, $rules)) {
                try {
                    $data = $_POST;
                    $data['total_amount'] = $data['liters_supplied'] * $data['price_per_liter'];
                    
                    $transactionId = $this->transactionModel->create($data);
                    $this->setFlash('success', 'Transacción registrada exitosamente.');
                    $this->redirect('/transactions/view/' . $transactionId);
                } catch (Exception $e) {
                    $this->setFlash('error', 'Error al registrar la transacción: ' . $e->getMessage());
                }
            } else {
                $this->setFlash('error', 'Error de validación. Verifique los datos ingresados.');
            }
        }
        
        $clients = $this->clientModel->getAll(['status' => 'active']);
        
        $data = [
            'title' => 'Nueva Transacción',
            'access' => $access,
            'clients' => $clients,
            'showNav' => true
        ];
        
        $this->view('transactions/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin', 'supervisor']);
        
        $transaction = $this->transactionModel->getById($id);
        
        if (!$transaction) {
            $this->setFlash('error', 'Transacción no encontrada.');
            $this->redirect('/transactions');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new Validator();
            $rules = [
                'total_amount' => 'required|numeric',
                'payment_method' => 'required',
                'payment_status' => 'required'
            ];
            
            if ($validator->validate($_POST, $rules)) {
                try {
                    $this->transactionModel->update($id, $_POST);
                    $this->setFlash('success', 'Transacción actualizada exitosamente.');
                    $this->redirect('/transactions/view/' . $id);
                } catch (Exception $e) {
                    $this->setFlash('error', 'Error al actualizar la transacción: ' . $e->getMessage());
                }
            } else {
                $this->setFlash('error', 'Error de validación. Verifique los datos ingresados.');
            }
        }
        
        $data = [
            'title' => 'Editar Transacción',
            'transaction' => $transaction,
            'showNav' => true
        ];
        
        $this->view('transactions/edit', $data);
    }
    
    public function updateStatus($id) {
        Auth::requireRole(['admin', 'supervisor']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->transactionModel->updateStatus($id, $_POST['payment_status']);
                $this->setFlash('success', 'Estado de pago actualizado exitosamente.');
            } catch (Exception $e) {
                $this->setFlash('error', 'Error al actualizar el estado: ' . $e->getMessage());
            }
        }
        
        $this->redirect('/transactions/view/' . $id);
    }
}
