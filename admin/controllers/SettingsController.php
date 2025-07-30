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
            $this->redirect(BASE_URL . '/admin/', "Access denied.");
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
        $settingsToUpdate = [];

        // Handle file uploads
        foreach ($_FILES as $key => $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../uploads/settings/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = $key . '.' . $file_extension;
                $filepath = 'uploads/settings/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    $settingsToUpdate[$key] = $filepath;
                } else {
                    $this->redirect(BASE_URL . '/admin/settings.php', 'Failed to upload ' . $key);
                }
            }
        }

        // Handle regular post data
        foreach ($_POST as $key => $value) {
            if (!isset($_FILES[$key])) {
                $settingsToUpdate[$key] = $value;
            }
        }

        // Handle boolean checkboxes
        $groupedSettings = Settings::getAllGrouped();
        foreach ($groupedSettings as $group => $settings) {
            foreach ($settings as $setting) {
                if ($setting['setting_type'] === 'boolean' && !isset($settingsToUpdate[$setting['setting_key']])) {
                    $settingsToUpdate[$setting['setting_key']] = '0';
                }
            }
        }

        if (Settings::updateMultiple($settingsToUpdate)) {
            $this->setFlashMessage('success', 'Settings updated successfully!');
        } else {
            $this->setFlashMessage('error', 'Failed to update settings');
        }

        $this->redirect(BASE_URL . '/admin/settings.php');
    }

}
