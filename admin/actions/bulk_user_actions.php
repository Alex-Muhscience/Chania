<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/User.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit();
}

$database = new Database();
$db = $database->connect();
$userModel = new User($db);

if (isset($_POST['bulk_action']) && isset($_POST['user_ids'])) {
    $bulkAction = $_POST['bulk_action'];
    $userIds = $_POST['user_ids'];

    switch ($bulkAction) {
        case 'export':
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename=users.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Username', 'Email', 'Role', 'Status', 'Created At']);

            foreach ($userIds as $userId) {
                $user = $userModel->getById($userId);
                if ($user) {
                    fputcsv($output, [
                        $user['id'],
                        $user['username'],
                        $user['email'],
                        $user['role'],
                        $user['is_active'] ? 'Active' : 'Inactive',
                        $user['created_at'],
                    ]);
                }
            }
            fclose($output);
            exit();

        case 'activate':
            foreach ($userIds as $userId) {
                $userModel->activate($userId);
            }
            $_SESSION['message'] = 'Selected users have been activated.';
            break;

        case 'deactivate':
            foreach ($userIds as $userId) {
                $userModel->deactivate($userId);
            }
            $_SESSION['message'] = 'Selected users have been deactivated.';
            break;

        case 'delete':
            foreach ($userIds as $userId) {
                $userModel->delete($userId);
            }
            $_SESSION['message'] = 'Selected users have been deleted.';
            break;
    }
}

header('Location: ../public/users.php');
exit();
