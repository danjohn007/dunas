<?php
/**
 * Controlador Dashboard
 */
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/models/Dashboard.php';

class DashboardController extends BaseController {
    
    private $dashboardModel;
    
    public function __construct() {
        $this->dashboardModel = new Dashboard();
    }
    
    public function index() {
        Auth::requireLogin();
        
        $stats = $this->dashboardModel->getStats();
        $recentAccess = $this->dashboardModel->getRecentAccess(5);
        $todayRevenue = $this->dashboardModel->getTodayRevenue();
        $monthlyData = $this->dashboardModel->getMonthlyData();
        
        $data = [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentAccess' => $recentAccess,
            'todayRevenue' => $todayRevenue,
            'monthlyData' => $monthlyData,
            'showNav' => true
        ];
        
        $this->view('dashboard/index', $data);
    }
}
