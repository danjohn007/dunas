<?php
/**
 * Controlador Visitor - Módulo de Registro de Visitantes
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/Visitor.php';
require_once APP_PATH . '/helpers/FileUpload.php';
require_once APP_PATH . '/helpers/Database.php';

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
            
            // Actualizar en la base de datos
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE visitors SET pass_code = ? WHERE id = ?");
            $stmt->execute([$passCode, $id]);
            
            $visitor['pass_code'] = $passCode;
        }
        
        $data = [
            'title' => 'Pase de Visita',
            'visitor' => $visitor,
            'showNav' => false
        ];
        
        $this->view('visitors/pass', $data);
    }
}
