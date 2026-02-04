<?php
/**
 * Controlador de Vales
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/Voucher.php';

class VoucherController extends BaseController {
    
    private $voucherModel;
    
    public function __construct() {
        parent::__construct();
        $this->voucherModel = new Voucher();
    }
    
    /**
     * Listar todos los vales
     */
    public function index() {
        // Verificar permisos
        if (!Auth::hasRole(['admin', 'supervisor'])) {
            header('Location: ' . BASE_URL . '/access-denied');
            exit;
        }
        
        // Obtener filtros
        $filters = [
            'status' => $_GET['status'] ?? '',
            'serie' => $_GET['serie'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        // Obtener vales
        $vouchers = $this->voucherModel->getAll($filters);
        $stats = $this->voucherModel->getStats();
        
        // Renderizar vista
        $this->render('vouchers/index', [
            'title' => 'Gestión de Vales',
            'vouchers' => $vouchers,
            'stats' => $stats,
            'filters' => $filters
        ]);
    }
    
    /**
     * Mostrar formulario de generación de vales
     */
    public function create() {
        // Verificar permisos
        if (!Auth::hasRole(['admin', 'supervisor'])) {
            header('Location: ' . BASE_URL . '/access-denied');
            exit;
        }
        
        $this->render('vouchers/create', [
            'title' => 'Generar Vales'
        ]);
    }
    
    /**
     * Generar vales
     */
    public function generate() {
        // Verificar permisos
        if (!Auth::hasRole(['admin', 'supervisor'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }
        
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        // Obtener datos del formulario
        $serie = strtoupper(trim($_POST['serie'] ?? ''));
        $folioInicio = intval($_POST['folio_inicio'] ?? 1);
        $cantidad = intval($_POST['cantidad'] ?? 1);
        $capacityLiters = intval($_POST['capacity_liters'] ?? 0);
        
        // Validar datos
        if (empty($serie)) {
            echo json_encode(['success' => false, 'message' => 'La serie es requerida']);
            exit;
        }
        
        if (!preg_match('/^[A-Za-z]+$/', $serie)) {
            echo json_encode(['success' => false, 'message' => 'La serie debe contener solo letras']);
            exit;
        }
        
        if ($folioInicio < 1) {
            echo json_encode(['success' => false, 'message' => 'El folio inicial debe ser mayor a 0']);
            exit;
        }
        
        if ($cantidad < 1 || $cantidad > 1000) {
            echo json_encode(['success' => false, 'message' => 'La cantidad debe estar entre 1 y 1000']);
            exit;
        }
        
        if ($capacityLiters < 1) {
            echo json_encode(['success' => false, 'message' => 'La capacidad debe ser mayor a 0']);
            exit;
        }
        
        // Generar vales
        $result = $this->voucherModel->generateVouchers(
            $serie,
            $folioInicio,
            $cantidad,
            $capacityLiters,
            Auth::getUserId()
        );
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => "Se generaron {$cantidad} vales exitosamente",
                'vouchers' => $result['vouchers']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $result['error']
            ]);
        }
    }
    
    /**
     * Imprimir vales
     */
    public function print() {
        // Verificar permisos
        if (!Auth::hasRole(['admin', 'supervisor', 'operator'])) {
            header('Location: ' . BASE_URL . '/access-denied');
            exit;
        }
        
        // Obtener IDs de vales a imprimir
        $voucherIds = $_GET['ids'] ?? '';
        if (empty($voucherIds)) {
            header('Location: ' . BASE_URL . '/vouchers');
            exit;
        }
        
        $ids = explode(',', $voucherIds);
        $vouchers = [];
        
        foreach ($ids as $id) {
            $voucher = $this->voucherModel->getById(intval($id));
            if ($voucher) {
                $vouchers[] = $voucher;
            }
        }
        
        if (empty($vouchers)) {
            header('Location: ' . BASE_URL . '/vouchers');
            exit;
        }
        
        // Renderizar vista de impresión
        $this->render('vouchers/print', [
            'title' => 'Imprimir Vales',
            'vouchers' => $vouchers
        ], false); // Sin layout para impresión
    }
    
    /**
     * Ver detalles de un vale
     */
    public function detail($id) {
        // Verificar permisos
        if (!Auth::hasRole(['admin', 'supervisor', 'operator'])) {
            header('Location: ' . BASE_URL . '/access-denied');
            exit;
        }
        
        $voucher = $this->voucherModel->getById($id);
        
        if (!$voucher) {
            header('Location: ' . BASE_URL . '/vouchers');
            exit;
        }
        
        $this->render('vouchers/view', [
            'title' => 'Detalle del Vale',
            'voucher' => $voucher
        ]);
    }
    
    /**
     * Cancelar un vale
     */
    public function cancel($id) {
        // Verificar permisos
        if (!Auth::hasRole(['admin', 'supervisor'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }
        
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        $voucher = $this->voucherModel->getById($id);
        
        if (!$voucher) {
            echo json_encode(['success' => false, 'message' => 'Vale no encontrado']);
            exit;
        }
        
        if ($voucher['status'] !== 'active') {
            echo json_encode(['success' => false, 'message' => 'Solo se pueden cancelar vales activos']);
            exit;
        }
        
        if ($this->voucherModel->cancel($id)) {
            echo json_encode(['success' => true, 'message' => 'Vale cancelado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al cancelar el vale']);
        }
    }
    
    /**
     * API: Validar un vale por su código QR
     */
    public function validate() {
        // Este endpoint es para el dispositivo HikVision o el sistema de acceso
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        $qrCode = $_POST['qr_code'] ?? '';
        
        if (empty($qrCode)) {
            echo json_encode(['success' => false, 'message' => 'Código QR requerido']);
            exit;
        }
        
        $result = $this->voucherModel->validateVoucher($qrCode);
        
        echo json_encode([
            'success' => $result['valid'],
            'message' => $result['error'] ?? 'Vale válido',
            'voucher' => $result['voucher']
        ]);
    }
}
