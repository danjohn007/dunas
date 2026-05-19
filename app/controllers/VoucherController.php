<?php
/**
 * Controlador de Vales (Vouchers)
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/Voucher.php';

class VoucherController extends BaseController {
    private const FOLIO_CODE_PATTERN_4_DIGITS = '/^([A-Z]{1,5})-(\d{4})$/';
    
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
        
        // Pagination
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $filters = [
            'serie' => $_GET['serie'] ?? '',
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? '',
            'limit' => $perPage,
            'offset' => $offset
        ];
        
        $vouchers = $this->voucherModel->getAll($filters);
        $stats = $this->voucherModel->getStats();
        $series = $this->voucherModel->getUniqueSeries();
        
        // Get total count for pagination
        $totalVouchers = $this->voucherModel->getTotalCount($filters);
        $totalPages = ceil($totalVouchers / $perPage);
        
        $data = [
            'title' => 'Gestión de Vales',
            'vouchers' => $vouchers,
            'stats' => $stats,
            'series' => $series,
            'filters' => $filters,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'totalVouchers' => $totalVouchers,
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
     * Muestra formulario de generación de vales imprenta
     */
    public function imprenta() {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);
        
        $data = [
            'title' => 'Imprenta de Vales',
            'showNav' => true
        ];
        
        $this->view('vouchers/imprenta', $data);
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
        $required = ['serie', 'start_folio', 'quantity', 'capacity', 'client_id', 'cost', 'payment_status'];
        foreach ($required as $field) {
            if (!isset($_POST[$field]) || (trim($_POST[$field]) === '' && $field !== 'cost')) {
                $this->setFlash('error', 'Todos los campos son requeridos, incluyendo la selección de cliente, costo y estado de pago.');
                $this->redirect('/vouchers/create');
                return;
            }
        }
        
        $serie = strtoupper(trim($_POST['serie']));
        $startFolio = (int)$_POST['start_folio'];
        $quantity = (int)$_POST['quantity'];
        $capacity = (int)$_POST['capacity'];
        $clientId = (int)$_POST['client_id'];
        $cost = (float)$_POST['cost'];
        $paymentStatus = $_POST['payment_status'];
        
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
        
        if ($cost < 0) {
            $this->setFlash('error', 'El costo debe ser mayor o igual a 0.');
            $this->redirect('/vouchers/create');
            return;
        }
        
        if (!in_array($paymentStatus, ['paid', 'pending'])) {
            $this->setFlash('error', 'El estado de pago debe ser "paid" o "pending".');
            $this->redirect('/vouchers/create');
            return;
        }
        
        // Verificar si el folio inicial ya existe y ajustar
        $originalStartFolio = $startFolio;
        $nextAvailable = $this->voucherModel->getNextAvailableFolio($serie, $startFolio, $capacity);
        
        if ($nextAvailable != $startFolio) {
            $this->setFlash('warning', "El folio {$startFolio} ya existe para la serie {$serie} con capacidad {$capacity} L. Se iniciará desde el folio {$nextAvailable}.");
            $startFolio = $nextAvailable;
        }
        
        // Generar vales
        try {
            $result = $this->voucherModel->generateBatch(
                $serie,
                $startFolio,
                $quantity,
                $capacity,
                Auth::user()['id'],
                $clientId,
                $cost,
                $paymentStatus
            );
            
            if ($result['total'] > 0) {
                $message = "Se generaron exitosamente {$result['total']} vales.";
                
                if (count($result['errors']) > 0) {
                    $message .= " Se encontraron " . count($result['errors']) . " errores (posibles duplicados).";
                }
                
                $this->setFlash('success', $message);
                
                // Guardar IDs de vales creados en sesión para impresión
                Session::set('last_voucher_batch', array_column($result['created'], 'id'));
                Session::set('last_voucher_print_mode', 'standard');
                
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
     * Procesa la generación de vales de imprenta
     */
    public function storeImprenta() {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vouchers/imprenta');
            return;
        }
        
        $required = ['serie', 'start_pin', 'quantity', 'capacity'];
        foreach ($required as $field) {
            if (!isset($_POST[$field]) || trim((string)$_POST[$field]) === '') {
                $this->setFlash('error', 'Todos los campos son requeridos.');
                $this->redirect('/vouchers/imprenta');
                return;
            }
        }
        
        $serie = strtoupper(trim($_POST['serie']));
        $startPin = (int)$_POST['start_pin'];
        $quantity = (int)$_POST['quantity'];
        $capacity = (int)$_POST['capacity'];
        
        if (!preg_match('/^[A-Z]{1,2}$/', $serie)) {
            $this->setFlash('error', 'La serie de imprenta debe ser de 1 a 2 letras (A-Z).');
            $this->redirect('/vouchers/imprenta');
            return;
        }
        
        if ($startPin < 1 || $startPin > 9999) {
            $this->setFlash('error', 'El PIN inicial debe estar entre 0001 y 9999.');
            $this->redirect('/vouchers/imprenta');
            return;
        }
        
        if ($quantity < 1 || $quantity > 1000) {
            $this->setFlash('error', 'La cantidad debe estar entre 1 y 1000 vales.');
            $this->redirect('/vouchers/imprenta');
            return;
        }
        
        if (($startPin + $quantity - 1) > 9999) {
            $this->setFlash('error', 'El rango de PIN excede 9999. Ajuste PIN inicial o cantidad.');
            $this->redirect('/vouchers/imprenta');
            return;
        }
        
        if ($capacity < 1) {
            $this->setFlash('error', 'La capacidad debe ser mayor a 0 litros.');
            $this->redirect('/vouchers/imprenta');
            return;
        }
        
        // Verificar si el folio inicial ya existe y ajustar al siguiente disponible
        $nextAvailable = $this->voucherModel->getNextAvailableFolio($serie, $startPin, $capacity);
        if ($nextAvailable != $startPin) {
            if (($nextAvailable + $quantity - 1) > 9999) {
                $this->setFlash('error', 'El folio ' . str_pad((string)$startPin, 4, '0', STR_PAD_LEFT) . ' ya existe para la serie ' . $serie . ' con capacidad ' . number_format($capacity) . ' L. El siguiente disponible (' . str_pad((string)$nextAvailable, 4, '0', STR_PAD_LEFT) . ') más la cantidad solicitada excede el límite de 9999.');
                $this->redirect('/vouchers/imprenta');
                return;
            }
            $this->setFlash('warning', 'El folio ' . str_pad((string)$startPin, 4, '0', STR_PAD_LEFT) . ' ya existe para la serie ' . $serie . ' con capacidad ' . number_format($capacity) . ' L. Se iniciará desde el folio ' . str_pad((string)$nextAvailable, 4, '0', STR_PAD_LEFT) . '.');
            $startPin = $nextAvailable;
        }
        
        // Look up the cost for this capacity from system settings
        require_once APP_PATH . '/models/CapacityCost.php';
        $capacityCostModel = new CapacityCost();
        $capacityCost = $capacityCostModel->getCostForCapacity($capacity);

        try {
            $result = $this->voucherModel->generateImprentaBatch(
                $serie,
                $startPin,
                $quantity,
                $capacity,
                Auth::user()['id'],
                $capacityCost
            );
            
            if ($result['total'] > 0) {
                $message = "Se generaron exitosamente {$result['total']} vales de imprenta.";
                
                if (count($result['errors']) > 0) {
                    $message .= " Se encontraron " . count($result['errors']) . " errores (posibles duplicados).";
                }
                
                $this->setFlash('success', $message);
                Session::set('last_voucher_batch', array_column($result['created'], 'id'));
                Session::set('last_voucher_print_mode', 'imprenta');
                $this->redirect('/vouchers/printBatch');
            } else {
                $errorMessage = 'No se pudo generar ningún vale de imprenta.';
                if (count($result['errors']) > 0) {
                    $firstError = $result['errors'][0];
                    $errorMessage .= ' Error en serie ' . $firstError['serie'] . ' folio ' . str_pad((string)$firstError['folio'], 4, '0', STR_PAD_LEFT) . ': ' . $firstError['error'];
                }
                $this->setFlash('error', $errorMessage);
                $this->redirect('/vouchers/imprenta');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al generar vales de imprenta: ' . $e->getMessage());
            $this->redirect('/vouchers/imprenta');
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
            'printMode' => Session::get('last_voucher_print_mode', 'standard'),
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
            'printMode' => 'standard',
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
     * Quita la relación de empresa de un vale de imprenta
     */
    public function unlink($id) {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vouchers');
            return;
        }

        try {
            $this->voucherModel->unlinkImprentaVoucher($id);
            $this->setFlash('success', 'Relación eliminada. El vale quedó pendiente de asignación.');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al desvincular el vale: ' . $e->getMessage());
        }

        $this->redirect('/vouchers');
    }

    public function unlinkBulk() {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);

        require_once APP_PATH . '/models/Client.php';
        $clientModel = new Client();
        $clients = $clientModel->getAll(['status' => 'active']);

        $data = [
            'title' => 'Quitar Relaciones de Vales',
            'clients' => $clients,
            'defaultSerie' => strtoupper(trim($_GET['serie'] ?? '')),
            'showNav' => true
        ];

        $this->view('vouchers/unlink_bulk', $data);
    }

    public function unlinkBulkStore() {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vouchers?status=active');
            return;
        }

        $clientId = (int)($_POST['client_id'] ?? 0);
        $folioStartCode = strtoupper(trim($_POST['folio_start_code'] ?? ''));
        $folioEndCode = strtoupper(trim($_POST['folio_end_code'] ?? ''));
        $returnSerie = strtoupper(trim($_POST['return_serie'] ?? ''));
        $redirectUrl = '/vouchers?status=active' . ($returnSerie !== '' ? '&serie=' . urlencode($returnSerie) : '');

        if ($clientId < 1) {
            $this->setFlash('error', 'Debe seleccionar una empresa válida.');
            $this->redirect($redirectUrl);
            return;
        }

        $useRange = ($folioStartCode !== '' || $folioEndCode !== '');
        $serie = null;
        $folioStart = null;
        $folioEnd = null;

        if ($useRange) {
            if ($folioStartCode === '' || $folioEndCode === '') {
                $this->setFlash('error', 'Debe capturar folio inicial y folio final, o dejar ambos vacíos para quitar todas las relaciones de la empresa.');
                $this->redirect('/vouchers/unlinkBulk' . ($returnSerie !== '' ? '?serie=' . urlencode($returnSerie) : ''));
                return;
            }

            if (!preg_match(self::FOLIO_CODE_PATTERN_4_DIGITS, $folioStartCode, $startMatches) ||
                !preg_match(self::FOLIO_CODE_PATTERN_4_DIGITS, $folioEndCode, $endMatches)) {
                $this->setFlash('error', 'Los folios deben tener formato SERIE-0000, por ejemplo AC-0026.');
                $this->redirect('/vouchers/unlinkBulk' . ($returnSerie !== '' ? '?serie=' . urlencode($returnSerie) : ''));
                return;
            }

            if ($startMatches[1] !== $endMatches[1]) {
                $this->setFlash('error', 'El folio inicial y final deben pertenecer a la misma serie.');
                $this->redirect('/vouchers/unlinkBulk' . ($returnSerie !== '' ? '?serie=' . urlencode($returnSerie) : ''));
                return;
            }

            $serie = $startMatches[1];
            $folioStart = (int)$startMatches[2];
            $folioEnd = (int)$endMatches[2];

            if ($folioEnd < $folioStart) {
                $this->setFlash('error', 'El folio final no puede ser menor que el folio inicial.');
                $this->redirect('/vouchers/unlinkBulk' . ($returnSerie !== '' ? '?serie=' . urlencode($returnSerie) : ''));
                return;
            }
        }

        try {
            $updated = $this->voucherModel->unlinkActiveVouchersByClientAndRange($clientId, $serie, $folioStart, $folioEnd);

            if ($updated > 0) {
                $message = $useRange
                    ? "Se quitaron {$updated} relaciones de vales en el rango {$folioStartCode} a {$folioEndCode}."
                    : "Se quitaron {$updated} relaciones activas de la empresa seleccionada.";
                $this->setFlash('success', $message);
            } else {
                $message = $useRange
                    ? 'No se encontraron vales activos relacionados con la empresa en el rango indicado.'
                    : 'No se encontraron vales activos relacionados con la empresa seleccionada.';
                $this->setFlash('warning', $message);
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al quitar relaciones de vales: ' . $e->getMessage());
        }

        $this->redirect($redirectUrl);
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
            $voucher = $this->voucherModel->getByCode($qrCode);
            
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
            
            // Vale válido - incluir datos del cliente si existen
            if (empty($voucher['client_id']) || $voucher['status'] === 'pending_assignment') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Este vale de imprenta aún no está relacionado a una empresa.'
                ]);
                return;
            }
            
            $response = [
                'success' => true,
                'message' => 'Vale válido',
                'voucher' => [
                    'id' => $voucher['id'],
                    'serie' => $voucher['serie'],
                    'folio' => $voucher['folio'],
                    'capacity' => $voucher['capacity'],
                    'qr_code' => $voucher['qr_code'],
                    'status' => $voucher['status'],
                    'voucher_type' => $voucher['voucher_type'] ?? 'standard',
                    'access_pin' => $this->voucherModel->formatAccessPin($voucher['folio'])
                ]
            ];
            
            // Si el vale tiene un cliente asociado, incluir sus datos
            if (!empty($voucher['client_id'])) {
                $client = $this->voucherModel->getClientById($voucher['client_id']);
                if ($client) {
                    $response['client'] = [
                        'id' => $client['id'],
                        'business_name' => $client['business_name'],
                        'rfc_curp' => $client['rfc_curp'],
                        'address' => $client['address'],
                        'phone' => $client['phone'],
                        'client_type' => $client['client_type']
                    ];
                }
            }
            
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al validar vale: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Muestra formulario para relacionar vales de imprenta
     */
    public function relate() {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);
        
        require_once APP_PATH . '/models/Client.php';
        $clientModel = new Client();
        $clients = $clientModel->getAll(['status' => 'active']);
        
        $data = [
            'title' => 'Relacionar Vales',
            'clients' => $clients,
            'series' => $this->voucherModel->getUniqueSeriesByType('imprenta'),
            'seriesCapacities' => $this->voucherModel->getSeriesCapacitiesByType('imprenta'),
            'showNav' => true
        ];
        
        $this->view('vouchers/relate', $data);
    }

    /**
     * Procesa la relación de vales de imprenta con una empresa
     */
    public function relateStore() {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vouchers/relate');
            return;
        }
        
        $required = ['client_id', 'serie', 'folio_start', 'folio_end', 'capacity'];
        foreach ($required as $field) {
            if (!isset($_POST[$field]) || trim((string)$_POST[$field]) === '') {
                $this->setFlash('error', 'Todos los campos son requeridos para relacionar vales.');
                $this->redirect('/vouchers/relate');
                return;
            }
        }
        
        $clientId = (int)$_POST['client_id'];
        $serie = strtoupper(trim($_POST['serie']));
        $folioStart = (int)$_POST['folio_start'];
        $folioEnd = (int)$_POST['folio_end'];
        $capacity = (int)$_POST['capacity'];
        
        if (!preg_match('/^[A-Z]{1,5}$/', $serie)) {
            $this->setFlash('error', 'La serie debe ser de 1 a 5 letras (A-Z).');
            $this->redirect('/vouchers/relate');
            return;
        }
        
        if ($capacity < 1) {
            $this->setFlash('error', 'La capacidad debe ser mayor a 0 litros.');
            $this->redirect('/vouchers/relate');
            return;
        }
        
        if ($folioStart < 0 || $folioStart > 9999 || $folioEnd < 0 || $folioEnd > 9999 || $folioEnd < $folioStart) {
            $this->setFlash('error', 'El rango de PIN debe estar entre 0000 y 9999.');
            $this->redirect('/vouchers/relate');
            return;
        }
        
        try {
            $updated = $this->voucherModel->relateImprentaVouchers($serie, $folioStart, $folioEnd, $clientId, $capacity);
            
            if ((int)$updated > 0) {
                $this->setFlash('success', "Se relacionaron {$updated} vales y quedaron activos.");
            } else {
                $this->setFlash('warning', 'No se encontraron vales pendientes de relación en el rango indicado.');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al relacionar vales: ' . $e->getMessage());
        }
        
        $this->redirect('/vouchers');
    }
}
