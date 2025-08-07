<?php

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/ProgramSchedule.php';
require_once __DIR__ . '/../classes/Program.php';

class ScheduleController extends BaseController {
    private $scheduleModel;
    private $programModel;

    public function __construct() {
        parent::__construct();
        $this->scheduleModel = new ProgramSchedule($this->db);
        $this->programModel = new Program($this->db);
        $this->setPageTitle('Manage Schedules');
    }

    public function index() {
        // Permission check
        if (!$this->hasPermission('schedules') && !$this->hasPermission('*')) {
            $this->setFlashMessage('error', 'Access denied. You do not have permission to manage schedules.');
            header('Location: index.php');
            exit;
        }

        // Run automated maintenance (closes expired registrations and archives expired schedules)
        try {
            $this->scheduleModel->runAutomatedMaintenance();
        } catch (Exception $e) {
            // Log error but don't interrupt the normal flow
            error_log("Schedule maintenance error: " . $e->getMessage());
        }

        $filters = [
            'search' => $_GET['search'] ?? '',
            'program' => isset($_GET['program']) && $_GET['program'] !== '' ? (int)$_GET['program'] : null,
            'status' => isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        try {
            $schedules = $this->scheduleModel->getAll($perPage, $offset, $filters['search'], $filters['program'], $filters['status']);
            $totalSchedules = $this->scheduleModel->getTotalCount($filters['search'], $filters['program'], $filters['status']);
            $totalPages = ceil($totalSchedules / $perPage);

            $programs = $this->programModel->getAll(1000, 0); // Get all programs for dropdown

            $stats = $this->scheduleModel->getStatistics();

            $this->render('schedules/index', [
                'schedules' => $schedules,
                'programs' => $programs,
                'stats' => $stats,
                'filters' => $filters,
                'pagination' => [
                    'page' => $page,
                    'totalPages' => $totalPages,
                    'totalSchedules' => $totalSchedules,
                    'perPage' => $perPage
                ]
            ]);
        } catch (Exception $e) {
            error_log("Schedules fetch error: " . $e->getMessage());
            $this->addError('Error loading schedules.');
            $this->render('schedules/index', [
                'schedules' => [],
                'programs' => [],
                'stats' => ['total' => 0, 'active' => 0, 'upcoming' => 0, 'open_registration' => 0],
                'filters' => $filters,
                'pagination' => [
                    'page' => 1,
                    'totalPages' => 0,
                    'totalSchedules' => 0,
                    'perPage' => $perPage
                ]
            ]);
        }
    }

    public function add() {
        $this->setPageTitle('Add New Schedule');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleScheduleCreation();
        }

        $programs = $this->programModel->getAll(1000, 0); // Get all programs for dropdown
        $this->renderView(__DIR__ . '/../views/schedules/add.php', ['programs' => $programs]);
    }

    public function edit() {
        $scheduleId = intval($_GET['id'] ?? 0);
        if (!$scheduleId) {
            $this->redirect(BASE_URL . '/admin/schedules.php', 'Invalid schedule ID.');
        }

        $this->setPageTitle('Edit Schedule');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleScheduleUpdate($scheduleId);
        }

        try {
            $schedule = $this->scheduleModel->getById($scheduleId);
            $programs = $this->programModel->getAll(1000, 0); // Get all programs for dropdown

            if (!$schedule) {
                $this->redirect(BASE_URL . '/admin/schedules.php', 'Schedule not found.');
            }

            $this->renderView(__DIR__ . '/../views/schedules/edit.php', [
                'schedule' => $schedule,
                'programs' => $programs
            ]);
        } catch (Exception $e) {
            $this->redirect(BASE_URL . '/admin/schedules.php', 'Error loading schedule: ' . $e->getMessage());
        }
    }

    private function handleScheduleCreation() {
        $requiredFields = ['program_id', 'title', 'start_date'];
        $errors = $this->validateRequired($_POST, $requiredFields);

        if (empty($errors)) {
            try {
                $scheduleData = $this->sanitizeInput($_POST);
                $result = $this->scheduleModel->create($scheduleData);

                if ($result) {
                    $this->redirect(BASE_URL . '/admin/schedules.php', 'Schedule created successfully.');
                } else {
                    $this->addError('Failed to create schedule.');
                }
            } catch (Exception $e) {
                $this->addError('Error creating schedule: ' . $e->getMessage());
            }
        } else {
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    private function handleScheduleUpdate($scheduleId) {
        $requiredFields = ['program_id', 'title', 'start_date'];
        $errors = $this->validateRequired($_POST, $requiredFields);

        if (empty($errors)) {
            try {
                $scheduleData = $this->sanitizeInput($_POST);
                $result = $this->scheduleModel->update($scheduleId, $scheduleData);

                if ($result) {
                    $this->redirect(BASE_URL . '/admin/schedules.php', 'Schedule updated successfully.');
                } else {
                    $this->addError('Failed to update schedule.');
                }
            } catch (Exception $e) {
                $this->addError('Error updating schedule: ' . $e->getMessage());
            }
        } else {
            $this->errors = array_merge($this->errors, $errors);
        }
    }
}

