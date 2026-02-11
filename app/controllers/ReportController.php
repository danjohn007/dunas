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
require_once APP_PATH . '/models/Voucher.php';
require_once APP_PATH . '/models/VoucherPayment.php';

class ReportController extends BaseController {
    
    private $accessModel;
    private $transactionModel;
    private $clientModel;
    private $unitModel;
    private $driverModel;
    private $voucherModel;
    private $voucherPaymentModel;
    
    public function __construct() {
        $this->accessModel = new AccessLog();
        $this->transactionModel = new Transaction();
        $this->clientModel = new Client();
        $this->unitModel = new Unit();
        $this->driverModel = new Driver();
        $this->voucherModel = new Voucher();
        $this->voucherPaymentModel = new VoucherPayment();
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
        
        // Obtener estadísticas de vales
        $voucherStats = $this->voucherModel->getFinancialStats($dateFrom, $dateTo);
        
        // Obtener resumen de vales por empresa
        $vouchersByCompany = $this->voucherModel->getVouchersByCompany($dateFrom, $dateTo);
        
        // Obtener pagos realizados por cada empresa para reflejar montos correctos
        foreach ($vouchersByCompany as $index => $company) {
            if ($company['client_id']) {
                $company['total_paid_registered'] = $this->voucherPaymentModel->getTotalPaidByClient(
                    $company['client_id'], 
                    $dateFrom, 
                    $dateTo
                );
                // Calcular el monto pendiente real (pendiente - pagos registrados)
                // Si hay sobrepago, se registra como 0 pero se podría alertar al administrador
                $company['actual_pending'] = max(0, $company['total_pending'] - $company['total_paid_registered']);
                
                // Log de advertencia si hay sobrepago (para debugging)
                if ($company['total_paid_registered'] > $company['total_pending']) {
                    error_log(sprintf(
                        "ADVERTENCIA: Sobrepago detectado para cliente ID %d (%s). Pagado: $%.2f, Pendiente: $%.2f",
                        $company['client_id'],
                        $company['client_name'] ?? 'Sin nombre',
                        $company['total_paid_registered'],
                        $company['total_pending']
                    ));
                }
            } else {
                $company['total_paid_registered'] = 0;
                $company['actual_pending'] = $company['total_pending'];
            }
            $vouchersByCompany[$index] = $company;
        }
        
        
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
        
        // Agregar datos de vales al reporte
        $stats['vouchers'] = [
            'total_paid' => (float)$voucherStats['total_paid'],
            'total_pending' => (float)$voucherStats['total_pending'],
            'total_amount' => (float)$voucherStats['total_amount'],
            'paid_count' => (int)$voucherStats['paid_count'],
            'pending_count' => (int)$voucherStats['pending_count']
        ];
        
        // Sumar vales pagados al total de ingresos
        $stats['total_revenue'] += $stats['vouchers']['total_paid'];
        
        // Obtener ingresos por día
        $revenueByDay = $this->transactionModel->getRevenueByPeriod($dateFrom, $dateTo);
        
        $data = [
            'title' => 'Reporte Financiero',
            'transactions' => $transactions,
            'stats' => $stats,
            'revenueByDay' => $revenueByDay,
            'vouchersByCompany' => $vouchersByCompany,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'showNav' => true
        ];
        
        $this->view('reports/financial', $data);
    }
    
    /**
     * Reporte detallado de vales por empresa
     */
    public function vouchersByCompany() {
        Auth::requireRole(['admin', 'supervisor']);
        
        // Pagination
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $clientId = $_GET['client_id'] ?? null;
        $search = $_GET['search'] ?? '';
        $serie = $_GET['serie'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $filters = [
            'client_id' => $clientId,
            'search' => $search,
            'serie' => $serie,
            'status' => $status,
            'limit' => $perPage,
            'offset' => $offset
        ];
        
        // Obtener detalle de vales
        $vouchers = $this->voucherModel->getVoucherDetailsByCompany($clientId, $dateFrom, $dateTo, $filters);
        
        // Get total count for pagination
        $totalVouchers = $this->voucherModel->getTotalCountByCompany($clientId, $dateFrom, $dateTo, $filters);
        $totalPages = ceil($totalVouchers / $perPage);
        
        // Obtener nombre del cliente si se filtró
        $clientName = null;
        if ($clientId) {
            $client = $this->voucherModel->getClientById($clientId);
            $clientName = $client ? $client['business_name'] : 'Cliente no encontrado';
        }
        
        // Get available series and clients for filters
        $series = $this->voucherModel->getUniqueSeries();
        require_once APP_PATH . '/models/Client.php';
        $clientModel = new Client();
        $clients = $clientModel->getAll(['status' => 'active']);
        
        $data = [
            'title' => 'Detalle de Vales por Empresa',
            'vouchers' => $vouchers,
            'clientName' => $clientName,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'search' => $search,
            'serie' => $serie,
            'status' => $status,
            'series' => $series,
            'clients' => $clients,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalVouchers' => $totalVouchers,
            'showNav' => true
        ];
        
        $this->view('reports/vouchers_by_company', $data);
    }
    
    /**
     * Reporte de resumen de vales generados
     */
    public function vouchersSummary() {
        Auth::requireRole(['admin', 'supervisor']);
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        // Obtener resumen por empresa
        $vouchersByCompany = $this->voucherModel->getVouchersByCompany($dateFrom, $dateTo);
        
        // Obtener pagos realizados por cada empresa
        foreach ($vouchersByCompany as &$company) {
            if ($company['client_id']) {
                $company['total_paid_registered'] = $this->voucherPaymentModel->getTotalPaidByClient(
                    $company['client_id'], 
                    $dateFrom, 
                    $dateTo
                );
                // Calculate actual pending (total pending - registered payments)
                $company['actual_pending'] = max(0, $company['total_pending'] - $company['total_paid_registered']);
            } else {
                $company['total_paid_registered'] = 0;
                $company['actual_pending'] = $company['total_pending'];
            }
        }
        
        $data = [
            'title' => 'Resumen de Vales Generados',
            'vouchersByCompany' => $vouchersByCompany,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'showNav' => true
        ];
        
        $this->view('reports/vouchers_summary', $data);
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
            case 'discrepancies':
                $filters = [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'plate_discrepancy' => true
                ];
                $data = $this->accessModel->getPlateDiscrepancies($filters);
                $filename = "reporte_discrepancias_{$dateFrom}_{$dateTo}.csv";
                $headers = ['Ticket', 'Fecha Entrada', 'Cliente', 'Placa Registrada', 'Placa Detectada', 'Chofer', 'Estado'];
                break;
            case 'plateVerification':
                $filters = [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo
                ];
                $data = $this->accessModel->getAll($filters);
                $filename = "reporte_verificacion_placas_{$dateFrom}_{$dateTo}.csv";
                $headers = ['Ticket', 'Fecha Entrada', 'Cliente', 'Placa Registrada', 'Placa Detectada', 'Verificación', 'Chofer', 'Estado'];
                break;
            case 'visitors':
                require_once APP_PATH . '/models/Visitor.php';
                $visitorModel = new Visitor();
                $filters = [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo
                ];
                $data = $visitorModel->getAll($filters);
                $filename = "reporte_visitantes_{$dateFrom}_{$dateTo}.csv";
                $headers = ['ID', 'Nombre', 'Placa', 'Teléfono', 'Entrada', 'Salida', 'Estado'];
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
        } elseif ($type === 'discrepancies') {
            foreach ($data as $row) {
                $statusLabels = [
                    'in_progress' => 'En Progreso',
                    'completed' => 'Completado',
                    'cancelled' => 'Cancelado'
                ];
                fputcsv($output, [
                    $row['ticket_code'],
                    date('d/m/Y H:i', strtotime($row['entry_datetime'])),
                    $row['client_name'],
                    $row['plate_number'],
                    $row['license_plate_reading'] ?? 'N/A',
                    $row['driver_name'],
                    $statusLabels[$row['status']]
                ]);
            }
        } elseif ($type === 'plateVerification') {
            foreach ($data as $row) {
                $statusLabels = [
                    'in_progress' => 'En Progreso',
                    'completed' => 'Completado',
                    'cancelled' => 'Cancelado'
                ];
                
                // Determinar estado de verificación
                if (empty($row['license_plate_reading'])) {
                    $verification = 'No Detectada';
                } elseif ($row['plate_discrepancy'] == 1) {
                    $verification = 'No Coincide';
                } else {
                    $verification = 'Coincide';
                }
                
                fputcsv($output, [
                    $row['ticket_code'],
                    date('d/m/Y H:i', strtotime($row['entry_datetime'])),
                    $row['client_name'],
                    $row['plate_number'],
                    $row['license_plate_reading'] ?? 'N/A',
                    $verification,
                    $row['driver_name'],
                    $statusLabels[$row['status']]
                ]);
            }
        } elseif ($type === 'visitors') {
            $statusLabels = [
                'in' => 'Dentro',
                'out' => 'Salió',
                'cancelled' => 'Cancelado'
            ];
            foreach ($data as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['visitor_name'] ?? '-',
                    $row['plate_number'] ?? '-',
                    $row['phone'] ?? '-',
                    date('d/m/Y H:i', strtotime($row['entry_datetime'])),
                    $row['exit_datetime'] ? date('d/m/Y H:i', strtotime($row['exit_datetime'])) : '-',
                    $statusLabels[$row['status']] ?? $row['status']
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
        } elseif ($type === 'discrepancies') {
            $filters = [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'plate_discrepancy' => true
            ];
            
            $discrepancies = $this->accessModel->getPlateDiscrepancies($filters);
            
            // Calcular estadísticas
            $stats = [
                'total_discrepancies' => count($discrepancies),
                'by_status' => [
                    'in_progress' => 0,
                    'completed' => 0,
                    'cancelled' => 0
                ]
            ];
            
            foreach ($discrepancies as $log) {
                $stats['by_status'][$log['status']]++;
            }
            
            $data = [
                'title' => 'Reporte de Discrepancias de Placas - Impresión',
                'discrepancies' => $discrepancies,
                'stats' => $stats,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'showNav' => false,
                'printMode' => true
            ];
            
            $this->view('reports/discrepancies_print', $data);
        } else {
            $this->setFlash('error', 'Tipo de reporte no válido para exportación PDF.');
            $this->redirect('/reports/' . $type);
        }
    }
    
    public function plateDiscrepancies() {
        Auth::requireRole(['admin', 'supervisor']);
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'plate_discrepancy' => true
        ];
        
        $discrepancies = $this->accessModel->getPlateDiscrepancies($filters);
        
        // Obtener todos los accesos del período para calcular tasa de verificación
        $allFilters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        $allAccesses = $this->accessModel->getAll($allFilters);
        
        // Calcular placas verificadas (plate_discrepancy = 0)
        $platesMatched = 0;
        foreach ($allAccesses as $access) {
            if ($access['plate_discrepancy'] == 0) {
                $platesMatched++;
            }
        }
        
        // Calcular estadísticas
        $stats = [
            'total_discrepancies' => count($discrepancies),
            'total_accesses' => count($allAccesses),
            'plates_matched' => $platesMatched,
            'by_status' => [
                'in_progress' => 0,
                'completed' => 0,
                'cancelled' => 0
            ]
        ];
        
        foreach ($discrepancies as $log) {
            $stats['by_status'][$log['status']]++;
        }
        
        $data = [
            'title' => 'Reporte de Discrepancias de Placas',
            'discrepancies' => $discrepancies,
            'stats' => $stats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'showNav' => true
        ];
        
        $this->view('reports/plate_discrepancies', $data);
    }
    
    public function plateVerification() {
        Auth::requireRole(['admin', 'supervisor']);
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        // Obtener accesos con placas verificadas (plate_discrepancy = 0)
        $accesses = $this->accessModel->getPlateVerifications($filters);
        
        // Debug temporal
        error_log("=== DEBUG PLATE VERIFICATION ===");
        error_log("Date From: " . $dateFrom);
        error_log("Date To: " . $dateTo);
        error_log("Total accesses with plate_discrepancy=0: " . count($accesses));
        
        // Obtener todos los accesos para estadísticas generales
        $allAccesses = $this->accessModel->getAll($filters);
        
        // Calcular estadísticas generales (de todos los accesos)
        $stats = [
            'total_accesses' => count($allAccesses),
            'plates_matched' => count($accesses), // Solo los que coincidieron
            'plates_not_matched' => 0,
            'plates_not_detected' => 0,
            'by_status' => [
                'in_progress' => 0,
                'completed' => 0,
                'cancelled' => 0
            ]
        ];
        
        // Calcular estadísticas de todos los accesos
        foreach ($allAccesses as $access) {
            $hasValidReading = !empty($access['license_plate_reading']) 
                && $access['license_plate_reading'] !== 'N/A' 
                && $access['license_plate_reading'] !== 'Placa no encontrada';
                
            if (!$hasValidReading) {
                $stats['plates_not_detected']++;
            } elseif ($access['plate_discrepancy'] == 1 || $access['plate_discrepancy'] === true || $access['plate_discrepancy'] === '1') {
                $stats['plates_not_matched']++;
            }
        }
        
        // Calcular estadísticas de estado solo de los que coincidieron
        foreach ($accesses as $access) {
            $stats['by_status'][$access['status']]++;
        }
        
        $data = [
            'title' => 'Reporte de Verificación de Placas',
            'accesses' => $accesses,
            'stats' => $stats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'showNav' => true
        ];
        
        $this->view('reports/plate_verification', $data);
    }
    
    /**
     * Reporte de visitantes
     */
    public function visitors() {
        Auth::requireRole(['admin', 'supervisor']);
        
        require_once APP_PATH . '/models/Visitor.php';
        $visitorModel = new Visitor();
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        $visitors = $visitorModel->getAll($filters);
        $stats = $visitorModel->getStats($dateFrom, $dateTo);
        
        $data = [
            'title' => 'Reporte de Visitantes',
            'visitors' => $visitors,
            'stats' => $stats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'showNav' => true
        ];
        
        $this->view('reports/visitors', $data);
    }
    
    /**
     * Registra un pago de vales de una empresa
     */
    public function registerPayment() {
        Auth::requireRole(['admin', 'supervisor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Método no permitido.');
            $this->redirect('/reports/vouchersSummary');
            return;
        }
        
        // Validar datos
        $required = ['client_id', 'amount', 'payment_date', 'payment_method'];
        foreach ($required as $field) {
            if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->setFlash('error', 'Todos los campos obligatorios son requeridos.');
                $this->redirect('/reports/vouchersSummary');
                return;
            }
        }
        
        $amount = (float)$_POST['amount'];
        if ($amount <= 0) {
            $this->setFlash('error', 'El monto debe ser mayor a 0.');
            $this->redirect('/reports/vouchersSummary');
            return;
        }
        
        try {
            $paymentData = [
                'client_id' => (int)$_POST['client_id'],
                'amount' => $amount,
                'payment_date' => $_POST['payment_date'],
                'payment_method' => $_POST['payment_method'],
                'reference' => $_POST['reference'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'created_by' => Auth::user()['id']
            ];
            
            $this->voucherPaymentModel->create($paymentData);
            
            // El trigger automáticamente actualiza el payment_status de los vouchers
            // No se necesita código adicional aquí
            
            $this->setFlash('success', 'Pago registrado exitosamente.');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
        
        $this->redirect('/reports/vouchersSummary');
    }
    
    /**
     * Obtiene los pagos de una empresa (AJAX)
     */
    public function getClientPayments($clientId) {
        Auth::requireRole(['admin', 'supervisor']);
        
        header('Content-Type: application/json');
        
        try {
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;
            
            $payments = $this->voucherPaymentModel->getByClient($clientId, $dateFrom, $dateTo);
            $totalPaid = $this->voucherPaymentModel->getTotalPaidByClient($clientId, $dateFrom, $dateTo);
            
            echo json_encode([
                'success' => true,
                'payments' => $payments,
                'totalPaid' => $totalPaid
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener pagos: ' . $e->getMessage()
            ]);
        }
        
        exit;
    }
    
    /**
     * Exporta el resumen de vales por empresa a CSV (compatible con Excel) o PDF
     */
    public function exportVouchersSummary() {
        Auth::requireRole(['admin', 'supervisor']);
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $format = $_GET['format'] ?? 'csv'; // csv o pdf
        
        // Obtener resumen por empresa
        $vouchersByCompany = $this->voucherModel->getVouchersByCompany($dateFrom, $dateTo);
        
        // Obtener pagos realizados por cada empresa
        foreach ($vouchersByCompany as &$company) {
            if ($company['client_id']) {
                $company['total_paid_registered'] = $this->voucherPaymentModel->getTotalPaidByClient(
                    $company['client_id'], 
                    $dateFrom, 
                    $dateTo
                );
                $company['actual_pending'] = max(0, $company['total_pending'] - $company['total_paid_registered']);
            } else {
                $company['total_paid_registered'] = 0;
                $company['actual_pending'] = $company['total_pending'];
            }
        }
        
        if ($format === 'csv') {
            $this->exportToCSV($vouchersByCompany, $dateFrom, $dateTo);
        } else {
            $this->exportToPDF($vouchersByCompany, $dateFrom, $dateTo);
        }
    }
    
    /**
     * Exporta datos a CSV (compatible con Excel)
     */
    private function exportToCSV($data, $dateFrom, $dateTo) {
        // Configurar headers para descarga
        $filename = "detalle_vales_empresa_{$dateFrom}_a_{$dateTo}.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Crear output stream
        $output = fopen('php://output', 'w');
        
        // Agregar BOM (Byte Order Mark) para Excel UTF-8
        // Excel requiere BOM para detectar correctamente la codificación UTF-8
        // y mostrar caracteres especiales (ñ, á, é, etc.) correctamente
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados del CSV
        $headers = [
            'Empresa/Cliente',
            'Serie',
            'Rango Folios',
            'Cantidad',
            'Capacidad (L)',
            'Activos',
            'Utilizados',
            'Pagado ($)',
            'Pago Registrado ($)',
            'Pendiente ($)'
        ];
        fputcsv($output, $headers);
        
        // Datos
        foreach ($data as $row) {
            $csvRow = [
                $row['client_name'] ?? 'Sin asignar',
                $row['serie'],
                str_pad($row['folio_inicial'], 4, '0', STR_PAD_LEFT) . ' - ' . str_pad($row['folio_final'], 4, '0', STR_PAD_LEFT),
                $row['total_vouchers'],
                number_format($row['total_capacity'], 0, '.', ''),
                $row['active_count'],
                $row['used_count'],
                number_format($row['total_paid'], 2, '.', ''),
                number_format($row['total_paid_registered'], 2, '.', ''),
                number_format($row['actual_pending'], 2, '.', '')
            ];
            fputcsv($output, $csvRow);
        }
        
        // Calcular totales
        $totals = [
            'Total General',
            '',
            '',
            array_sum(array_column($data, 'total_vouchers')),
            number_format(array_sum(array_column($data, 'total_capacity')), 0, '.', ''),
            array_sum(array_column($data, 'active_count')),
            array_sum(array_column($data, 'used_count')),
            number_format(array_sum(array_column($data, 'total_paid')), 2, '.', ''),
            number_format(array_sum(array_column($data, 'total_paid_registered')), 2, '.', ''),
            number_format(array_sum(array_column($data, 'actual_pending')), 2, '.', '')
        ];
        fputcsv($output, $totals);
        
        fclose($output);
        exit();
    }
    
    /**
     * Exporta datos a PDF (HTML simple para imprimir)
     */
    private function exportToPDF($data, $dateFrom, $dateTo) {
        // Para PDF, generamos HTML y usamos el navegador para imprimir a PDF
        $filename = "detalle_vales_empresa_{$dateFrom}_a_{$dateTo}.pdf";
        
        // Calcular totales
        $totales = [
            'cantidad' => array_sum(array_column($data, 'total_vouchers')),
            'capacidad' => array_sum(array_column($data, 'total_capacity')),
            'activos' => array_sum(array_column($data, 'active_count')),
            'usados' => array_sum(array_column($data, 'used_count')),
            'pagado' => array_sum(array_column($data, 'total_paid')),
            'registrado' => array_sum(array_column($data, 'total_paid_registered')),
            'pendiente' => array_sum(array_column($data, 'actual_pending'))
        ];
        
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title><?php echo $filename; ?></title>
            <style>
                @page { size: A4 landscape; margin: 1cm; }
                body { font-family: Arial, sans-serif; font-size: 10px; }
                h1 { text-align: center; color: #4F46E5; margin-bottom: 5px; }
                .subtitle { text-align: center; color: #666; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #4F46E5; color: white; font-weight: bold; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .total-row { background-color: #E0E7FF !important; font-weight: bold; }
                .badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 9px; }
                .badge-purple { background-color: #DDD6FE; color: #6B21A8; }
                .badge-green { background-color: #D1FAE5; color: #065F46; }
                .badge-gray { background-color: #F3F4F6; color: #374151; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="no-print" style="margin-bottom: 20px; text-align: center;">
                <button onclick="window.print()" style="background-color: #4F46E5; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                    Imprimir / Guardar como PDF
                </button>
                <button onclick="window.close()" style="background-color: #6B7280; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
                    Cerrar
                </button>
            </div>
            
            <h1>Detalle de Vales por Empresa</h1>
            <div class="subtitle">Período: <?php echo date('d/m/Y', strtotime($dateFrom)); ?> - <?php echo date('d/m/Y', strtotime($dateTo)); ?></div>
            
            <table>
                <thead>
                    <tr>
                        <th>Empresa/Cliente</th>
                        <th class="text-center">Serie</th>
                        <th class="text-center">Rango Folios</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-center">Capacidad</th>
                        <th class="text-center">Activos</th>
                        <th class="text-center">Usados</th>
                        <th class="text-right">Pagado</th>
                        <th class="text-right">Pago Reg.</th>
                        <th class="text-right">Pendiente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['client_name'] ?? 'Sin asignar'); ?></td>
                        <td class="text-center">
                            <span class="badge badge-purple"><?php echo htmlspecialchars($row['serie']); ?></span>
                        </td>
                        <td class="text-center" style="font-family: monospace;">
                            <?php echo str_pad($row['folio_inicial'], 4, '0', STR_PAD_LEFT); ?> → 
                            <?php echo str_pad($row['folio_final'], 4, '0', STR_PAD_LEFT); ?>
                        </td>
                        <td class="text-center"><?php echo $row['total_vouchers']; ?></td>
                        <td class="text-center"><?php echo number_format($row['total_capacity']); ?> L</td>
                        <td class="text-center">
                            <span class="badge badge-green"><?php echo $row['active_count']; ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-gray"><?php echo $row['used_count']; ?></span>
                        </td>
                        <td class="text-right">$<?php echo number_format($row['total_paid'], 2); ?></td>
                        <td class="text-right">$<?php echo number_format($row['total_paid_registered'], 2); ?></td>
                        <td class="text-right">$<?php echo number_format($row['actual_pending'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="3"><strong>TOTAL GENERAL</strong></td>
                        <td class="text-center"><strong><?php echo $totales['cantidad']; ?></strong></td>
                        <td class="text-center"><strong><?php echo number_format($totales['capacidad']); ?> L</strong></td>
                        <td class="text-center"><strong><?php echo $totales['activos']; ?></strong></td>
                        <td class="text-center"><strong><?php echo $totales['usados']; ?></strong></td>
                        <td class="text-right"><strong>$<?php echo number_format($totales['pagado'], 2); ?></strong></td>
                        <td class="text-right"><strong>$<?php echo number_format($totales['registrado'], 2); ?></strong></td>
                        <td class="text-right"><strong>$<?php echo number_format($totales['pendiente'], 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>
            
            <div style="margin-top: 30px; font-size: 9px; color: #666;">
                <p><strong>Generado:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
                <p><strong>Usuario:</strong> <?php echo htmlspecialchars(Auth::user()['full_name']); ?></p>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
}
