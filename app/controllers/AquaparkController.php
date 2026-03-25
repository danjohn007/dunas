<?php
/**
 * Controlador AquaparkController - Módulo PARQUE ACUÁTICO
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/AquaparkCode.php';
require_once APP_PATH . '/models/AquaparkTicket.php';
require_once APP_PATH . '/models/Settings.php';

class AquaparkController extends BaseController {

    private $codeModel;
    private $ticketModel;
    private $settingsModel;

    public function __construct() {
        $this->codeModel   = new AquaparkCode();
        $this->ticketModel = new AquaparkTicket();
        $this->settingsModel = new Settings();
    }

    // =========================================================
    // CÓDIGOS DE ACCESO (pulseras por serie)
    // =========================================================

    /**
     * Lista de códigos generados por serie.
     */
    public function codes() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);

        $perPage = 50;
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $offset  = ($page - 1) * $perPage;

        $filters = [
            'date_from'  => $_GET['date_from']  ?? date('Y-m-d'),
            'date_to'    => $_GET['date_to']    ?? date('Y-m-d'),
            'validated'  => $_GET['validated']  ?? '',
            'limit'      => $perPage,
            'offset'     => $offset,
        ];

        $totalRecords = $this->codeModel->countAll($filters);
        $totalPages   = max(1, ceil($totalRecords / $perPage));
        $codes        = $this->codeModel->getAll($filters);

        $data = [
            'title'      => 'Códigos de Acceso',
            'codes'      => $codes,
            'filters'    => $filters,
            'pagination' => [
                'currentPage'  => $page,
                'totalPages'   => $totalPages,
                'totalRecords' => $totalRecords,
                'perPage'      => $perPage,
            ],
            'showNav'    => true,
        ];

        $this->view('aquapark/codes', $data);
    }

    /**
     * Formulario + acción para generar un lote de códigos de serie.
     */
    public function generateCodes() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $start = (int)($_POST['start_number'] ?? 0);
                $end   = (int)($_POST['end_number']   ?? 0);
                $date  = $_POST['valid_date'] ?? date('Y-m-d');

                if ($start <= 0 || $end <= 0) {
                    throw new Exception('Los números de serie deben ser mayores a cero.');
                }
                if ($start > $end) {
                    throw new Exception('El número inicial debe ser menor o igual al final.');
                }
                if (($end - $start + 1) > 500) {
                    throw new Exception('No se pueden generar más de 500 códigos a la vez.');
                }

                $userId = Auth::user()['id'];
                $count  = $this->codeModel->generateBatch($start, $end, $date, $userId);

                // Redirect to print wristbands
                $this->redirect('/aquapark/printWristbands?start=' . $start . '&end=' . $end . '&date=' . urlencode($date));
                return;

            } catch (Exception $e) {
                $this->setFlash('error', $e->getMessage());
            }
        }

        $data = [
            'title'   => 'Generar Códigos de Acceso',
            'showNav' => true,
        ];

        $this->view('aquapark/generate_codes', $data);
    }

    /**
     * Vista de impresión de pulseras (11 por hoja carta).
     */
    public function printWristbands() {
        Auth::requireRole(['admin', 'supervisor', 'operator']);

        $start = (int)($_GET['start'] ?? 0);
        $end   = (int)($_GET['end']   ?? 0);
        $date  = $_GET['date'] ?? date('Y-m-d');

        $codes = $this->codeModel->getBySeriesAndDate($start, $end, $date);

        $data = [
            'title'   => 'Imprimir Pulseras',
            'codes'   => $codes,
            'date'    => $date,
            'start'   => $start,
            'end'     => $end,
            'showNav' => false,
        ];

        $this->view('aquapark/print_wristbands', $data);
    }

    // =========================================================
    // VALIDACIÓN PÚBLICA DE CÓDIGOS QR
    // =========================================================

    /**
     * Página pública para validar un código QR de parque acuático.
     * Acepta códigos de pulsera (AQP-...) y boletos individuales (TKT-...).
     */
    public function validateCode() {
        // Página pública, sin autenticación requerida
        $validationResult = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = trim($_POST['code'] ?? '');

            if (empty($code)) {
                $validationResult = [
                    'valid'   => false,
                    'status'  => 'empty',
                    'message' => 'Por favor ingrese o escanee un código.'
                ];
            } else {
                // Intentar primero como código de pulsera de serie
                if (strpos($code, 'AQP-') === 0) {
                    $validationResult = $this->codeModel->validate($code, 'Escaneo público');
                } elseif (strpos($code, 'TKT-') === 0) {
                    // Intentar primero como boleto individual (aquapark_ticket_items)
                    $item = $this->ticketModel->getItemByCode($code);
                    if ($item) {
                        $today = date('Y-m-d');
                        if ($item['visit_date'] !== $today) {
                            $validationResult = [
                                'valid'   => false,
                                'status'  => 'expired',
                                'message' => 'Este boleto es para el ' . date('d/m/Y', strtotime($item['visit_date'])),
                                'ticket'  => $item
                            ];
                        } elseif (!empty($item['validated_at'])) {
                            $validationResult = [
                                'valid'   => false,
                                'status'  => 'already_used',
                                'message' => 'Este boleto ya fue utilizado a las ' . date('H:i', strtotime($item['validated_at'])),
                                'ticket'  => $item
                            ];
                        } else {
                            // Marcar item como validado
                            $this->ticketModel->markItemValidated($item['id']);
                            $validationResult = [
                                'valid'   => true,
                                'status'  => 'ok',
                                'message' => '¡Boleto válido! Boleto #' . $item['item_number'] . ((!empty($item['visitor_name'])) ? ' - ' . $item['visitor_name'] : ''),
                                'ticket'  => $item
                            ];
                        }
                    } else {
                        // Fallback: boleto padre (registros sin items individuales)
                        $ticket = $this->ticketModel->getByCode($code);
                        if ($ticket) {
                            $today = date('Y-m-d');
                            if ($ticket['visit_date'] === $today) {
                                $validationResult = [
                                    'valid'   => true,
                                    'status'  => 'ok',
                                    'message' => '¡Boleto válido! ' . ($ticket['ticket_count'] > 1 ? $ticket['ticket_count'] . ' boletos' : '1 boleto'),
                                    'ticket'  => $ticket
                                ];
                            } else {
                                $validationResult = [
                                    'valid'   => false,
                                    'status'  => 'expired',
                                    'message' => 'Este boleto es para el ' . date('d/m/Y', strtotime($ticket['visit_date'])),
                                    'ticket'  => $ticket
                                ];
                            }
                        } else {
                            $validationResult = [
                                'valid'   => false,
                                'status'  => 'not_found',
                                'message' => 'Código no encontrado en el sistema.'
                            ];
                        }
                    }
                } else {
                    // Intento genérico en ambas tablas
                    $validationResult = $this->codeModel->validate($code, 'Escaneo público');
                    if ($validationResult['status'] === 'not_found') {
                        $item = $this->ticketModel->getItemByCode($code);
                        if ($item) {
                            $validationResult = [
                                'valid'   => true,
                                'status'  => 'ok',
                                'message' => '¡Boleto válido!',
                                'ticket'  => $item
                            ];
                        } else {
                            $ticket = $this->ticketModel->getByCode($code);
                            if ($ticket) {
                                $validationResult = [
                                    'valid'   => true,
                                    'status'  => 'ok',
                                    'message' => '¡Boleto válido!',
                                    'ticket'  => $ticket
                                ];
                            }
                        }
                    }
                }
            }
        }

        $settings = $this->settingsModel->getAll();

        $data = [
            'title'            => 'Validar Acceso - Parque Acuático',
            'validationResult' => $validationResult,
            'systemSettings'   => $settings,
            'showNav'          => false,
        ];

        $this->view('aquapark/validate_code', $data);
    }

    // =========================================================
    // VISITANTES (boletos manuales)
    // =========================================================

    /**
     * Lista de boletos registrados manualmente.
     */
    public function visitors() {
        Auth::requireRole(['admin', 'supervisor', 'operator', 'cajero_parque']);

        $perPage = 20;
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $offset  = ($page - 1) * $perPage;

        $filters = [
            'date_from' => $_GET['date_from'] ?? date('Y-m-d'),
            'date_to'   => $_GET['date_to']   ?? date('Y-m-d'),
            'search'    => $_GET['search']    ?? '',
            'limit'     => $perPage,
            'offset'    => $offset,
        ];

        $totalRecords = $this->ticketModel->countAll($filters);
        $totalPages   = max(1, ceil($totalRecords / $perPage));
        $tickets      = $this->ticketModel->getAll($filters);

        $settings  = $this->settingsModel->getAll();
        $unitPrice = (float)($settings['aquapark_ticket_price_manual'] ?? 0);

        $data = [
            'title'      => 'Visitantes - Parque Acuático',
            'tickets'    => $tickets,
            'filters'    => $filters,
            'pagination' => [
                'currentPage'  => $page,
                'totalPages'   => $totalPages,
                'totalRecords' => $totalRecords,
                'perPage'      => $perPage,
            ],
            'unitPrice'  => $unitPrice,
            'showNav'    => true,
        ];

        $this->view('aquapark/visitors', $data);
    }

    /**
     * Registrar un nuevo visitante / boleto manual.
     */
    public function registerVisitor() {
        Auth::requireRole(['admin', 'supervisor', 'operator', 'cajero_parque']);

        $settings  = $this->settingsModel->getAll();
        $unitPrice = (float)($settings['aquapark_ticket_price_manual'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $ticketCount = (int)($_POST['ticket_count'] ?? 0);

                if ($ticketCount <= 0) {
                    throw new Exception('El número de boletos debe ser mayor a cero.');
                }

                $visitDate   = $_POST['visit_date'] ?? date('Y-m-d');
                $totalAmount = $unitPrice > 0 ? $unitPrice * $ticketCount : null;

                $ticketData = [
                    'visitor_name'  => $_POST['visitor_name']  ?? '',
                    'phone'         => $_POST['phone']          ?? '',
                    'visit_date'    => $visitDate,
                    'ticket_count'  => $ticketCount,
                    'total_amount'  => $totalAmount,
                    'notes'         => $_POST['notes']          ?? '',
                    'created_by'    => Auth::user()['id'],
                ];

                $ticket = $this->ticketModel->create($ticketData);

                $this->setFlash('success', 'Boleto registrado exitosamente.');
                $this->redirect('/aquapark/printTicket/' . $ticket['id']);
                return;

            } catch (Exception $e) {
                $this->setFlash('error', $e->getMessage());
            }
        }

        $data = [
            'title'     => 'Registrar Visitante',
            'unitPrice' => $unitPrice,
            'showNav'   => true,
        ];

        $this->view('aquapark/register_visitor', $data);
    }

    /**
     * Imprime el boleto de un visitante con código QR.
     */
    public function printTicket($id) {
        Auth::requireRole(['admin', 'supervisor', 'operator', 'cajero_parque']);

        $ticket = $this->ticketModel->getById($id);

        if (!$ticket) {
            $this->setFlash('error', 'Boleto no encontrado.');
            $this->redirect('/aquapark/visitors');
            return;
        }

        $items    = $this->ticketModel->getItemsByTicketId($id);
        $settings = $this->settingsModel->getAll();

        $data = [
            'title'          => 'Boleto de Visita',
            'ticket'         => $ticket,
            'items'          => $items,
            'systemSettings' => $settings,
            'showNav'        => false,
        ];

        $this->view('aquapark/print_ticket', $data);
    }

    // =========================================================
    // REPORTES
    // =========================================================

    /**
     * Reportes por rango de fechas.
     */
    public function reports() {
        Auth::requireRole(['admin', 'supervisor']);

        $dateFrom = $_GET['date_from'] ?? date('Y-m-d');
        $dateTo   = $_GET['date_to']   ?? date('Y-m-d');

        $settings      = $this->settingsModel->getAll();
        $priceSerials  = (float)($settings['aquapark_ticket_price_series'] ?? 0);
        $priceManual   = (float)($settings['aquapark_ticket_price_manual'] ?? 0);

        // Stats for wristband codes
        $codeStats   = $this->codeModel->getStatsByDate($dateFrom, $dateTo);

        // Stats for manual tickets
        $ticketStats = $this->ticketModel->getStatsByDate($dateFrom, $dateTo);

        // Detailed records
        $filters = ['date_from' => $dateFrom, 'date_to' => $dateTo];

        $codes   = $this->codeModel->getAll(array_merge($filters, ['validated' => '1']));
        $tickets = $this->ticketModel->getAll($filters);

        // Aggregate totals
        $totalCodesValidated   = array_sum(array_column($codeStats,   'validated_count'));
        $totalTickets          = array_sum(array_column($ticketStats,  'total_tickets'));
        $totalAmountCodes      = $totalCodesValidated * $priceSerials;
        $totalAmountTickets    = array_sum(array_column($ticketStats,  'total_amount'));
        $grandTotal            = $totalAmountCodes + $totalAmountTickets;

        $data = [
            'title'              => 'Reportes - Parque Acuático',
            'dateFrom'           => $dateFrom,
            'dateTo'             => $dateTo,
            'codeStats'          => $codeStats,
            'ticketStats'        => $ticketStats,
            'codes'              => $codes,
            'tickets'            => $tickets,
            'priceSerials'       => $priceSerials,
            'priceManual'        => $priceManual,
            'totalCodesValidated'=> $totalCodesValidated,
            'totalTickets'       => $totalTickets,
            'totalAmountCodes'   => $totalAmountCodes,
            'totalAmountTickets' => $totalAmountTickets,
            'grandTotal'         => $grandTotal,
            'showNav'            => true,
        ];

        $this->view('aquapark/reports', $data);
    }

    /**
     * Página principal del módulo (redirige a códigos de acceso o visitantes según rol).
     */
    public function index() {
        Auth::requireRole(['admin', 'supervisor', 'operator', 'cajero_parque']);

        if (Auth::hasRole(['cajero_parque'])) {
            $this->redirect('/aquapark/visitors');
        } else {
            $this->redirect('/aquapark/codes');
        }
    }
}
