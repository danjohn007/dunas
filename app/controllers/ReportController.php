<?php
/**
 * Controlador Report
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/AccessLog.php';
require_once APP_PATH . '/models/Transaction.php';
require_once APP_PATH . '/models/Client.php';
require_once APP_PATH . '/models/Unit.php';
require_once APP_PATH . '/models/Driver.php';

class ReportController extends BaseController {
    
    private $accessModel;
    private $transactionModel;
    private $clientModel;
    private $unitModel;
    private $driverModel;
    
    public function __construct() {
        $this->accessModel = new AccessLog();
        $this->transactionModel = new Transaction();
        $this->clientModel = new Client();
        $this->unitModel = new Unit();
        $this->driverModel = new Driver();
    }
    
    public function index() {
        Auth::requireRole(['admin', 'supervisor']);
        
        $data = [
            'title' => 'Reportes',
            'showNav' => true
        ];
        
        $this->view('reports/index', $data);
    }
    
    public function access() {
        Auth::requireRole(['admin', 'supervisor']);
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        $accessLogs = $this->accessModel->getAll($filters);
        
        // Calcular estadísticas
        $stats = [
            'total_access' => count($accessLogs),
            'completed' => 0,
            'in_progress' => 0,
            'cancelled' => 0,
            'total_liters' => 0
        ];
        
        foreach ($accessLogs as $log) {
            $stats[$log['status']]++;
            if ($log['liters_supplied']) {
                $stats['total_liters'] += $log['liters_supplied'];
            }
        }
        
        $data = [
            'title' => 'Reporte de Accesos',
            'accessLogs' => $accessLogs,
            'stats' => $stats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'showNav' => true
        ];
        
        $this->view('reports/access', $data);
    }
    
    public function financial() {
        Auth::requireRole(['admin', 'supervisor']);
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'payment_status' => 'paid'
        ];
        
        $transactions = $this->transactionModel->getAll($filters);
        
        // Calcular estadísticas
        $stats = [
            'total_transactions' => count($transactions),
            'total_revenue' => 0,
            'total_liters' => 0,
            'by_method' => [
                'cash' => 0,
                'voucher' => 0,
                'bank_transfer' => 0
            ]
        ];
        
        foreach ($transactions as $trans) {
            $stats['total_revenue'] += $trans['total_amount'];
            $stats['total_liters'] += $trans['liters_supplied'];
            $stats['by_method'][$trans['payment_method']] += $trans['total_amount'];
        }
        
        // Obtener ingresos por día
        $revenueByDay = $this->transactionModel->getRevenueByPeriod($dateFrom, $dateTo);
        
        $data = [
            'title' => 'Reporte Financiero',
            'transactions' => $transactions,
            'stats' => $stats,
            'revenueByDay' => $revenueByDay,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'showNav' => true
        ];
        
        $this->view('reports/financial', $data);
    }
    
    public function operational() {
        Auth::requireRole(['admin', 'supervisor']);
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        // Obtener todos los accesos del período
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'status' => 'completed'
        ];
        
        $accessLogs = $this->accessModel->getAll($filters);
        
        // Estadísticas por unidad
        $unitStats = [];
        // Estadísticas por chofer
        $driverStats = [];
        // Estadísticas por tipo de cliente
        $clientTypeStats = [
            'residential' => ['count' => 0, 'liters' => 0],
            'commercial' => ['count' => 0, 'liters' => 0],
            'industrial' => ['count' => 0, 'liters' => 0]
        ];
        
        foreach ($accessLogs as $log) {
            // Por unidad
            $unitId = $log['unit_id'];
            if (!isset($unitStats[$unitId])) {
                $unitStats[$unitId] = [
                    'plate_number' => $log['plate_number'],
                    'trips' => 0,
                    'liters' => 0
                ];
            }
            $unitStats[$unitId]['trips']++;
            $unitStats[$unitId]['liters'] += $log['liters_supplied'];
            
            // Por chofer
            $driverId = $log['driver_id'];
            if (!isset($driverStats[$driverId])) {
                $driverStats[$driverId] = [
                    'driver_name' => $log['driver_name'],
                    'trips' => 0,
                    'liters' => 0
                ];
            }
            $driverStats[$driverId]['trips']++;
            $driverStats[$driverId]['liters'] += $log['liters_supplied'];
        }
        
        // Obtener clientes para estadísticas por tipo
        $clients = $this->clientModel->getAll();
        foreach ($clients as $client) {
            $clientType = $client['client_type'];
            $clientTransactions = $this->transactionModel->getAll([
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ]);
            
            foreach ($clientTransactions as $trans) {
                if ($trans['client_id'] == $client['id']) {
                    $clientTypeStats[$clientType]['count']++;
                    $clientTypeStats[$clientType]['liters'] += $trans['liters_supplied'];
                }
            }
        }
        
        $data = [
            'title' => 'Reporte Operativo',
            'unitStats' => $unitStats,
            'driverStats' => $driverStats,
            'clientTypeStats' => $clientTypeStats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'showNav' => true
        ];
        
        $this->view('reports/operational', $data);
    }
    
    public function exportExcel($type) {
        Auth::requireRole(['admin', 'supervisor']);
        
        // Aquí se implementaría la exportación a Excel
        // Por ahora, redirigir con mensaje
        $this->setFlash('info', 'Funcionalidad de exportación a Excel en desarrollo.');
        $this->redirect('/reports/' . $type);
    }
    
    public function exportPdf($type) {
        Auth::requireRole(['admin', 'supervisor']);
        
        // Aquí se implementaría la exportación a PDF
        // Por ahora, redirigir con mensaje
        $this->setFlash('info', 'Funcionalidad de exportación a PDF en desarrollo.');
        $this->redirect('/reports/' . $type);
    }
}
