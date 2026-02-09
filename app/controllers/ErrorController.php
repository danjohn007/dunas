<?php
/**
 * Controlador ErrorLog
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/ErrorLog.php';

class ErrorController extends BaseController {
    
    private $errorLogModel;
    
    public function __construct() {
        $this->errorLogModel = new ErrorLog();
    }
    
    /**
     * Lista todos los errores del sistema
     */
    public function index() {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);
        
        // Pagination
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $filters = [
            'level' => $_GET['level'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? '',
            'limit' => $perPage,
            'offset' => $offset
        ];
        
        $errors = $this->errorLogModel->getAll($filters);
        $totalErrors = $this->errorLogModel->getTotalCount($filters);
        $totalPages = ceil($totalErrors / $perPage);
        
        // Get stats
        $stats = $this->errorLogModel->getStats(
            $filters['date_from'] ?: null,
            $filters['date_to'] ?: null
        );
        
        $data = [
            'title' => 'Registro de Errores del Sistema',
            'errors' => $errors,
            'stats' => $stats,
            'filters' => $filters,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalErrors' => $totalErrors,
            'showNav' => true
        ];
        
        $this->view('errors/index', $data);
    }
    
    /**
     * Ver detalle de un error
     */
    public function detail($id) {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);
        
        $error = $this->errorLogModel->getById($id);
        
        if (!$error) {
            $this->setFlash('error', 'Error no encontrado.');
            $this->redirect('/errors');
            return;
        }
        
        // Decode context JSON
        if (!empty($error['context'])) {
            $error['context_decoded'] = json_decode($error['context'], true);
        }
        
        $data = [
            'title' => 'Detalle de Error',
            'error' => $error,
            'showNav' => true
        ];
        
        $this->view('errors/detail', $data);
    }
    
    /**
     * Limpia el registro de errores
     */
    public function clear() {
        Auth::requireLogin();
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/errors');
            return;
        }
        
        try {
            $this->errorLogModel->deleteAll();
            $this->setFlash('success', 'Registro de errores limpiado exitosamente.');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al limpiar el registro: ' . $e->getMessage());
        }
        
        $this->redirect('/errors');
    }
    
    /**
     * Exporta errores a archivo de texto
     */
    public function export() {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'supervisor']);
        
        $filters = [
            'level' => $_GET['level'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        $errors = $this->errorLogModel->getAll($filters);
        
        // Generate text file
        $filename = 'error_log_' . date('Y-m-d_H-i-s') . '.txt';
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo "REGISTRO DE ERRORES DEL SISTEMA\n";
        echo "================================\n";
        echo "Generado: " . date('Y-m-d H:i:s') . "\n";
        echo "Total de errores: " . count($errors) . "\n\n";
        
        foreach ($errors as $error) {
            echo str_repeat('-', 80) . "\n";
            echo "ID: " . $error['id'] . "\n";
            echo "Fecha/Hora: " . $error['created_at'] . "\n";
            echo "Nivel: " . strtoupper($error['level']) . "\n";
            echo "Mensaje: " . $error['message'] . "\n";
            
            if (!empty($error['user_name'])) {
                echo "Usuario: " . $error['user_name'] . " (" . $error['user_username'] . ")\n";
            }
            
            if (!empty($error['ip_address'])) {
                echo "IP: " . $error['ip_address'] . "\n";
            }
            
            if (!empty($error['context']) && $error['context'] !== '[]') {
                echo "Contexto: " . $error['context'] . "\n";
            }
            
            echo "\n";
        }
        
        exit;
    }
}
