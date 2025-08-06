<?php

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../../shared/Core/Settings.php';

class SettingsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display the settings management page
     */
    public function index()
    {
        // Require admin role
        if (!$this->hasPermission('admin') && !$this->hasPermission('*')) {
            $this->redirect(BASE_URL . '/admin/public/', "Access denied.");
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
        }

        $groupedSettings = Settings::getAllGrouped();

        $data = [
            'groupedSettings' => $groupedSettings,
            'pageTitle' => 'Site Settings',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'index.php'],
                ['title' => 'Site Settings']
            ]
        ];

        $this->render('settings/index', $data);
    }

    /**
     * Handle form submission
     */
    private function handlePost()
    {
        try {
            // CSRF Protection
            if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $this->setFlashMessage('error', 'Invalid security token. Please try again.');
                $this->redirect(BASE_URL . '/admin/public/settings.php');
                return;
            }
            
            $settingsToUpdate = [];
            $errors = [];

            // Handle file uploads
            foreach ($_FILES as $key => $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/../../uploads/settings/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    // Validate file type for security
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico'];
                    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    
                    if (!in_array($file_extension, $allowed_extensions)) {
                        $errors[] = "Invalid file type for $key. Only " . implode(', ', $allowed_extensions) . " are allowed.";
                        continue;
                    }

                    $filename = $key . '_' . time() . '.' . $file_extension;
                    $filepath = 'uploads/settings/' . $filename;

                    if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                        $settingsToUpdate[$key] = $filepath;
                    } else {
                        $errors[] = "Failed to upload $key";
                    }
                } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                    $errors[] = "Upload error for $key: " . $this->getUploadErrorMessage($file['error']);
                }
            }

            // Handle regular post data
            foreach ($_POST as $key => $value) {
                if (!isset($_FILES[$key]) && $key !== 'csrf_token') {
                    // Sanitize the value based on setting type
                    $settingsToUpdate[$key] = $this->sanitizeSettingValue($key, $value);
                }
            }

            // Handle boolean checkboxes (unchecked checkboxes don't send POST data)
            $groupedSettings = Settings::getAllGrouped();
            foreach ($groupedSettings as $group => $settings) {
                foreach ($settings as $setting) {
                    if ($setting['setting_type'] === 'boolean' && !isset($settingsToUpdate[$setting['setting_key']])) {
                        $settingsToUpdate[$setting['setting_key']] = '0';
                    }
                }
            }

            if (!empty($errors)) {
                $this->setFlashMessage('error', implode('<br>', $errors));
                $this->redirect(BASE_URL . '/admin/public/settings.php');
                return;
            }

            if (empty($settingsToUpdate)) {
                $this->setFlashMessage('error', 'No settings to update');
                $this->redirect(BASE_URL . '/admin/public/settings.php');
                return;
            }

            if (Settings::updateMultiple($settingsToUpdate)) {
                $this->setFlashMessage('success', 'Settings updated successfully!');
            } else {
                $this->setFlashMessage('error', 'Failed to update settings. Please check the error log for details.');
            }

        } catch (Exception $e) {
            error_log("Settings update error: " . $e->getMessage());
            $this->setFlashMessage('error', 'An error occurred while updating settings.');
        }

        $this->redirect(BASE_URL . '/admin/public/settings.php');
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($error)
    {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File is too large';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }

    /**
     * Sanitize setting value based on type
     */
    private function sanitizeSettingValue($key, $value)
    {
        // Get setting type from database
        $groupedSettings = Settings::getAllGrouped();
        $settingType = 'text'; // default
        
        foreach ($groupedSettings as $group => $settings) {
            foreach ($settings as $setting) {
                if ($setting['setting_key'] === $key) {
                    $settingType = $setting['setting_type'];
                    break 2;
                }
            }
        }

        switch ($settingType) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'number':
                return is_numeric($value) ? $value : '0';
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : '';
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) ? $value : '';
            default:
                return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
    }

}
