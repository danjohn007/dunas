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
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        // Obtener los datos según el tipo de reporte
        $data = [];
        $filename = '';
        
        switch($type) {
            case 'financial':
                $filters = [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'payment_status' => 'paid'
                ];
                $data = $this->transactionModel->getAll($filters);
                $filename = "reporte_financiero_{$dateFrom}_{$dateTo}.csv";
                $headers = ['Fecha', 'Cliente', 'Litros', 'Método de Pago', 'Monto'];
                break;
            case 'access':
                $filters = [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo
                ];
                $data = $this->accessModel->getAll($filters);
                $filename = "reporte_acceso_{$dateFrom}_{$dateTo}.csv";
                $headers = ['Fecha Entrada', 'Fecha Salida', 'Unidad', 'Chofer', 'Cliente', 'Litros', 'Estado'];
                break;
            default:
                $this->setFlash('error', 'Tipo de reporte no válido.');
                $this->redirect('/reports');
                return;
        }
        
        // Generar archivo CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Escribir BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Escribir encabezados
        fputcsv($output, $headers);
        
        // Escribir datos
        if ($type === 'financial') {
            foreach ($data as $row) {
                $methodLabels = [
                    'cash' => 'Efectivo',
                    'voucher' => 'Vales',
                    'bank_transfer' => 'Transferencia'
                ];
                fputcsv($output, [
                    date('d/m/Y H:i', strtotime($row['transaction_date'])),
                    $row['client_name'],
                    number_format($row['liters_supplied']),
                    $methodLabels[$row['payment_method']],
                    '$' . number_format($row['total_amount'], 2)
                ]);
            }
        } elseif ($type === 'access') {
            foreach ($data as $row) {
                $statusLabels = [
                    'in_progress' => 'En Progreso',
                    'completed' => 'Completado',
                    'cancelled' => 'Cancelado'
                ];
                fputcsv($output, [
                    date('d/m/Y H:i', strtotime($row['entry_datetime'])),
                    $row['exit_datetime'] ? date('d/m/Y H:i', strtotime($row['exit_datetime'])) : '-',
                    $row['plate_number'],
                    $row['driver_name'],
                    $row['client_name'],
                    $row['liters_supplied'] ? number_format($row['liters_supplied']) : '-',
                    $statusLabels[$row['status']]
                ]);
            }
        }
        
        fclose($output);
        exit;
    }
    
    public function exportPdf($type) {
        Auth::requireRole(['admin', 'supervisor']);
        
        // Para PDF, usaremos una simple impresión HTML que el navegador puede convertir a PDF
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        // Reutilizar la misma vista con un parámetro para indicar modo de impresión
        if ($type === 'financial') {
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
            
            $data = [
                'title' => 'Reporte Financiero - Impresión',
                'transactions' => $transactions,
                'stats' => $stats,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'showNav' => false,
                'printMode' => true
            ];
            
            $this->view('reports/financial_print', $data);
        } else {
            $this->setFlash('error', 'Exportación PDF disponible solo para reporte financiero.');
            $this->redirect('/reports/' . $type);
        }
    }
}
