<?php
/**
 * Controlador de Vales (Vouchers)
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/Voucher.php';

class VoucherController extends BaseController {
    
    private $voucherModel;
    
    public function __construct() {
        $this->voucherModel = new Voucher();
    }
    
    /**
     * Lista todos los vales
     */
    public function index() {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $filters = [
            'serie' => $_GET['serie'] ?? '',
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        $vouchers = $this->voucherModel->getAll($filters);
        $stats = $this->voucherModel->getStats();
        $series = $this->voucherModel->getUniqueSeries();
        
        $data = [
            'title' => 'Gestión de Vales',
            'vouchers' => $vouchers,
            'stats' => $stats,
            'series' => $series,
            'filters' => $filters,
            'showNav' => true
        ];
        
        $this->view('vouchers/index', $data);
    }
    
    /**
     * Muestra el formulario de creación de vales en lote
     */
    public function create() {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);
        
        // Load clients for dropdown
        require_once APP_PATH . '/models/Client.php';
        $clientModel = new Client();
        $clients = $clientModel->getAll(['status' => 'active']);
        
        $data = [
            'title' => 'Generar Vales',
            'clients' => $clients,
            'showNav' => true
        ];
        
        $this->view('vouchers/create', $data);
    }
    
    /**
     * Procesa la generación de vales en lote
     */
    public function store() {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vouchers/create');
            return;
        }
        
        // Validar campos requeridos
        $required = ['serie', 'start_folio', 'quantity', 'capacity', 'client_id'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $this->setFlash('error', 'Todos los campos son requeridos, incluyendo la selección de cliente.');
                $this->redirect('/vouchers/create');
                return;
            }
        }
        
        $serie = strtoupper(trim($_POST['serie']));
        $startFolio = (int)$_POST['start_folio'];
        $quantity = (int)$_POST['quantity'];
        $capacity = (int)$_POST['capacity'];
        $clientId = (int)$_POST['client_id'];
        
        // Validaciones
        if (!preg_match('/^[A-Z]{1,10}$/', $serie)) {
            $this->setFlash('error', 'La serie debe contener solo letras (A-Z, máximo 10 caracteres).');
            $this->redirect('/vouchers/create');
            return;
        }
        
        if ($startFolio < 1) {
            $this->setFlash('error', 'El folio inicial debe ser mayor a 0.');
            $this->redirect('/vouchers/create');
            return;
        }
        
        if ($quantity < 1 || $quantity > 1000) {
            $this->setFlash('error', 'La cantidad debe estar entre 1 y 1000 vales.');
            $this->redirect('/vouchers/create');
            return;
        }
        
        if ($capacity < 1) {
            $this->setFlash('error', 'La capacidad debe ser mayor a 0 litros.');
            $this->redirect('/vouchers/create');
            return;
        }
        
        // Generar vales
        try {
            $result = $this->voucherModel->generateBatch(
                $serie,
                $startFolio,
                $quantity,
                $capacity,
                Auth::user()['id'],
                $clientId
            );
            
            if ($result['total'] > 0) {
                $message = "Se generaron exitosamente {$result['total']} vales.";
                
                if (count($result['errors']) > 0) {
                    $message .= " Se encontraron " . count($result['errors']) . " errores (posibles duplicados).";
                }
                
                $this->setFlash('success', $message);
                
                // Guardar IDs de vales creados en sesión para impresión
                Session::set('last_voucher_batch', array_column($result['created'], 'id'));
                
                $this->redirect('/vouchers/printBatch');
            } else {
                // Mostrar el primer error si existe para mejor diagnóstico
                $errorMessage = 'No se pudo generar ningún vale.';
                if (count($result['errors']) > 0) {
                    $firstError = $result['errors'][0];
                    $errorMessage .= ' Error en serie ' . $firstError['serie'] . ' folio ' . $firstError['folio'] . ': ' . $firstError['error'];
                }
                $this->setFlash('error', $errorMessage);
                $this->redirect('/vouchers/create');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al generar vales: ' . $e->getMessage());
            $this->redirect('/vouchers/create');
        }
    }
    
    /**
     * Muestra la página de impresión para un lote de vales
     */
    public function printBatch() {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);
        
        $voucherIds = Session::get('last_voucher_batch');
        
        if (empty($voucherIds)) {
            $this->setFlash('error', 'No hay vales para imprimir.');
            $this->redirect('/vouchers');
            return;
        }
        
        // Obtener los vales
        $vouchers = [];
        foreach ($voucherIds as $id) {
            $voucher = $this->voucherModel->getById($id);
            if ($voucher) {
                $vouchers[] = $voucher;
            }
        }
        
        $data = [
            'title' => 'Imprimir Vales',
            'vouchers' => $vouchers,
            'showNav' => false  // No mostrar navegación en vista de impresión
        ];
        
        $this->view('vouchers/print_batch', $data);
    }
    
    /**
     * Muestra la página de impresión para un vale individual
     */
    public function printSingle($id) {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);
        
        $voucher = $this->voucherModel->getById($id);
        
        if (!$voucher) {
            $this->setFlash('error', 'Vale no encontrado.');
            $this->redirect('/vouchers');
            return;
        }
        
        $data = [
            'title' => 'Imprimir Vale',
            'vouchers' => [$voucher],  // Array con un solo voucher para reutilizar la vista
            'showNav' => false  // No mostrar navegación en vista de impresión
        ];
        
        $this->view('vouchers/print_batch', $data);
    }
    
    /**
     * Ver detalles de un vale
     */
    public function detail($id) {
        Auth::requireLogin();
        
        $voucher = $this->voucherModel->getById($id);
        
        if (!$voucher) {
            $this->setFlash('error', 'Vale no encontrado.');
            $this->redirect('/vouchers');
            return;
        }
        
        $data = [
            'title' => 'Detalle de Vale',
            'voucher' => $voucher,
            'showNav' => true
        ];
        
        $this->view('vouchers/view', $data);
    }
    
    /**
     * Cancela un vale
     */
    public function cancel($id) {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vouchers');
            return;
        }
        
        try {
            $this->voucherModel->cancel($id);
            $this->setFlash('success', 'Vale cancelado exitosamente.');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al cancelar el vale: ' . $e->getMessage());
        }
        
        $this->redirect('/vouchers');
    }
    
    /**
     * API: Valida un vale por código QR
     */
    public function validateQR() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $qrCode = $_POST['qr_code'] ?? '';
        
        if (empty($qrCode)) {
            echo json_encode(['success' => false, 'message' => 'Código QR requerido']);
            return;
        }
        
        try {
            $voucher = $this->voucherModel->getByQRCode($qrCode);
            
            if (!$voucher) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Vale no encontrado'
                ]);
                return;
            }
            
            if ($voucher['status'] !== 'active') {
                $statusMessages = [
                    'used' => 'Este vale ya fue utilizado',
                    'cancelled' => 'Este vale ha sido cancelado'
                ];
                
                echo json_encode([
                    'success' => false,
                    'message' => $statusMessages[$voucher['status']] ?? 'Vale no válido',
                    'voucher' => [
                        'serie' => $voucher['serie'],
                        'folio' => $voucher['folio'],
                        'status' => $voucher['status'],
                        'used_at' => $voucher['used_at']
                    ]
                ]);
                return;
            }
            
            // Vale válido
            echo json_encode([
                'success' => true,
                'message' => 'Vale válido',
                'voucher' => [
                    'id' => $voucher['id'],
                    'serie' => $voucher['serie'],
                    'folio' => $voucher['folio'],
                    'capacity' => $voucher['capacity'],
                    'qr_code' => $voucher['qr_code'],
                    'status' => $voucher['status']
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al validar vale: ' . $e->getMessage()
            ]);
        }
    }
}
