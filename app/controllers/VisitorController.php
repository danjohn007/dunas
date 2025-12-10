<?php
/**
 * Controlador Visitor - Módulo de Registro de Visitantes
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/Visitor.php';
require_once APP_PATH . '/helpers/FileUpload.php';

class VisitorController extends BaseController {
    
    private $visitorModel;
    
    public function __construct() {
        $this->visitorModel = new Visitor();
    }
    
    /**
     * Página pública de registro de visitantes
     */
    public function register() {
        // Esta es una página pública, no requiere autenticación
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'visitor_name' => $_POST['visitor_name'] ?? null,
                    'plate_number' => strtoupper(trim($_POST['plate_number'] ?? '')),
                    'phone' => $_POST['phone'] ?? null,
                    'notes' => $_POST['notes'] ?? null
                ];
                
                // Procesar foto de identificación (requerida)
                if (isset($_FILES['id_photo']) && $_FILES['id_photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = UPLOAD_PATH . '/visitors';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $result = FileUpload::upload($_FILES['id_photo'], $uploadDir);
                    if ($result['success']) {
                        $data['id_photo'] = '/uploads/visitors/' . $result['filename'];
                    } else {
                        throw new Exception('Error al subir foto de identificación: ' . $result['error']);
                    }
                } else {
                    throw new Exception('La foto de identificación es requerida');
                }
                
                // Procesar foto de placas (requerida)
                if (isset($_FILES['plate_photo']) && $_FILES['plate_photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = UPLOAD_PATH . '/visitors';
                    $result = FileUpload::upload($_FILES['plate_photo'], $uploadDir);
                    if ($result['success']) {
                        $data['plate_photo'] = '/uploads/visitors/' . $result['filename'];
                    } else {
                        throw new Exception('Error al subir foto de placas: ' . $result['error']);
                    }
                } else {
                    throw new Exception('La foto de placas es requerida');
                }
                
                // Procesar foto de gafete (opcional)
                if (isset($_FILES['badge_photo']) && $_FILES['badge_photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = UPLOAD_PATH . '/visitors';
                    $result = FileUpload::upload($_FILES['badge_photo'], $uploadDir);
                    if ($result['success']) {
                        $data['badge_photo'] = '/uploads/visitors/' . $result['filename'];
                    }
                }
                
                $visitorId = $this->visitorModel->create($data);
                
                // Redirigir a página de éxito
                $this->setFlash('success', 'Registro de visitante completado exitosamente. ID: ' . $visitorId);
                $this->redirect('/visitors/success/' . $visitorId);
                return;
                
            } catch (Exception $e) {
                $this->setFlash('error', $e->getMessage());
            }
        }
        
        $data = [
            'title' => 'Registro de Visitante',
            'showNav' => false
        ];
        
        $this->view('visitors/register', $data);
    }
    
    /**
     * Página de éxito después del registro
     */
    public function success($id) {
        $visitor = $this->visitorModel->getById($id);
        
        if (!$visitor) {
            $this->redirect('/visitors/register');
            return;
        }
        
        $data = [
            'title' => 'Registro Exitoso',
            'visitor' => $visitor,
            'showNav' => false
        ];
        
        $this->view('visitors/success', $data);
    }
    
    /**
     * Lista de visitantes (requiere autenticación)
     */
    public function index() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $perPage = 20;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $perPage;
        
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'limit' => $perPage,
            'offset' => $offset
        ];
        
        $totalRecords = $this->visitorModel->countAll($filters);
        $totalPages = ceil($totalRecords / $perPage);
        $visitors = $this->visitorModel->getAll($filters);
        
        $data = [
            'title' => 'Visitantes',
            'visitors' => $visitors,
            'filters' => $filters,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalRecords' => $totalRecords,
                'perPage' => $perPage
            ],
            'showNav' => true
        ];
        
        $this->view('visitors/index', $data);
    }
    
    /**
     * Ver detalle de visitante
     */
    public function show($id) {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $visitor = $this->visitorModel->getById($id);
        
        if (!$visitor) {
            $this->setFlash('error', 'Visitante no encontrado');
            $this->redirect('/visitors');
            return;
        }
        
        $data = [
            'title' => 'Detalle de Visitante',
            'visitor' => $visitor,
            'showNav' => true
        ];
        
        $this->view('visitors/view', $data);
    }
    
    /**
     * Registrar salida de visitante
     */
    public function exit($id) {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        try {
            $this->visitorModel->registerExit($id);
            $this->setFlash('success', 'Salida de visitante registrada exitosamente');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al registrar salida: ' . $e->getMessage());
        }
        
        $this->redirect('/visitors');
    }
    
    /**
     * Cancelar registro de visitante
     */
    public function cancel($id) {
        Auth::requireRole(['admin', 'supervisor']);
        
        try {
            $this->visitorModel->cancel($id);
            $this->setFlash('success', 'Registro de visitante cancelado');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al cancelar registro: ' . $e->getMessage());
        }
        
        $this->redirect('/visitors');
    }
    
    /**
     * Ver pase de visita
     */
    public function pass($id) {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $visitor = $this->visitorModel->getById($id);
        
        if (!$visitor) {
            $this->setFlash('error', 'Visitante no encontrado');
            $this->redirect('/visitors');
            return;
        }
        
        // Si el visitante no tiene código de pase, generar uno usando el modelo
        if (empty($visitor['pass_code'])) {
            $passCode = $this->visitorModel->generatePassCode($visitor['entry_datetime']);
            
            // Actualizar en la base de datos usando el método del modelo
            $this->visitorModel->updatePassCode($id, $passCode);
            
            $visitor['pass_code'] = $passCode;
        }
        
        $data = [
            'title' => 'Pase de Visita',
            'visitor' => $visitor,
            'showNav' => false
        ];
        
        $this->view('visitors/pass', $data);
    }
    
    /**
     * Formulario para generar un pase de visita con vigencia
     */
    public function generatePass() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        $data = [
            'title' => 'Generar Pase de Visita',
            'showNav' => true
        ];
        
        $this->view('visitors/generate_pass', $data);
    }
    
    /**
     * Crear pase de visita con vigencia
     */
    public function createPass() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/visitors');
            return;
        }
        
        try {
            $data = [
                'visitor_name' => $_POST['visitor_name'] ?? null,
                'plate_number' => strtoupper(trim($_POST['plate_number'] ?? '')),
                'phone' => $_POST['phone'] ?? null,
                'identification' => $_POST['identification'] ?? null,
                'visit_type' => $_POST['visit_type'] ?? 'personal',
                'valid_from' => $_POST['valid_from'] ?? null,
                'valid_until' => $_POST['valid_until'] ?? null,
                'notes' => $_POST['notes'] ?? null
            ];
            
            // Validate required fields
            if (empty($data['visitor_name'])) {
                throw new Exception('El nombre del visitante es requerido');
            }
            
            if (empty($data['phone'])) {
                throw new Exception('El teléfono es requerido');
            }
            
            if (empty($data['valid_from']) || empty($data['valid_until'])) {
                throw new Exception('Las fechas de vigencia son requeridas');
            }
            
            // Validate date range
            $validFrom = new DateTime($data['valid_from']);
            $validUntil = new DateTime($data['valid_until']);
            
            if ($validUntil <= $validFrom) {
                throw new Exception('La fecha de fin debe ser posterior a la fecha de inicio');
            }
            
            $visitorId = $this->visitorModel->createPass($data);
            
            $this->setFlash('success', 'Pase de visita generado exitosamente');
            $this->redirect('/visitors/pass/' . $visitorId);
            
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/visitors/generatePass');
        }
    }
    
    /**
     * Página pública para validar código QR
     */
    public function validateQr() {
        // Esta es una página pública, no requiere autenticación
        
        $validationResult = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $passCode = trim($_POST['pass_code'] ?? '');
            
            if (!empty($passCode)) {
                $validationResult = $this->visitorModel->validatePass($passCode);
            } else {
                $validationResult = [
                    'valid' => false,
                    'status' => 'empty',
                    'message' => 'Por favor ingrese un código QR'
                ];
            }
        }
        
        $data = [
            'title' => 'Validar Código QR',
            'validationResult' => $validationResult,
            'showNav' => false
        ];
        
        $this->view('visitors/validate_qr', $data);
    }
}
