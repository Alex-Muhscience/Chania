<?php

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../../shared/Core/Report.php';

class ReportsController extends BaseController
{
    private $reportModel;

    public function __construct()
    {
        parent::__construct();
        $this->reportModel = new Report($this->db);
    }

    /**
     * Display reports dashboard page
     */
    public function index()
    {
        // Check permissions
        if (!$this->hasPermission('reports') && !$this->hasPermission('*')) {
            $this->redirect(BASE_URL . '/admin/', "You don't have permission to access this resource.");
        }

        // Get predefined reports
        $predefinedReports = $this->reportModel->getPredefinedReports();

        // Get custom reports (safely handle non-existent table)
        try {
            $customReports = $this->reportModel->getCustomReports($_SESSION['user_id']);
        } catch (PDOException $e) {
            // If reports table doesn't exist, create empty array
            $customReports = [];
        }

        // Get quick stats
        $stats = $this->reportModel->getQuickStats();

        $data = [
            'predefinedReports' => $predefinedReports,
            'customReports' => $customReports,
            'stats' => $stats,
            'pageTitle' => 'Reports & Analytics',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Reports & Analytics']
            ]
        ];

        $this->render('reports/index', $data);
    }

    /**
     * Check if user has permission for reports access
     */
    private function checkPermission($permission)
    {
        return $this->hasPermission($permission) || $this->hasPermission('*');
    }
}
