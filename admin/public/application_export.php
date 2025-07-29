<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

$format = $_GET['format'] ?? 'csv';
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$applicationId = $_GET['id'] ?? '';

try {
    $db = (new Database())->connect();

    $whereClause = '';
    $params = [];
    $conditions = [];

    if ($applicationId) {
        // Single application export
        $conditions[] = "a.id = ?";
        $params[] = $applicationId;
    } else {
        // Multiple applications export
        if ($search) {
            $conditions[] = "(CONCAT(a.first_name, ' ', a.last_name) LIKE ? OR a.email LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if ($status) {
            $conditions[] = "a.status = ?";
            $params[] = $status;
        }
    }

    if (!empty($conditions)) {
        $whereClause = "WHERE " . implode(" AND ", $conditions);
    }

    $stmt = $db->prepare("SELECT a.*, p.title as program_title, CONCAT(a.first_name, ' ', a.last_name) as full_name FROM applications a LEFT JOIN programs p ON a.program_id = p.id $whereClause ORDER BY a.submitted_at DESC");
    $stmt->execute($params);
    $applications = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Applications export error: " . $e->getMessage());
    $_SESSION['error'] = "Error exporting applications.";
    Utilities::redirect('/admin/public/applications.php');
}

if ($format === 'csv') {
    $filename = $applicationId ? 'application_' . $applicationId . '_' . date('Y-m-d') . '.csv' : 'applications_' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // CSV Header
    fputcsv($output, [
        'ID',
        'Full Name',
        'Email',
        'Phone',
        'Date of Birth',
        'Address',
        'Program',
        'Status',
        'Message',
        'Education',
        'Experience',
        'Applied Date',
        'Last Updated'
    ]);

    // CSV Data
    foreach ($applications as $application) {
        fputcsv($output, [
            $application['id'],
            $application['full_name'],
            $application['email'],
            $application['phone'] ?? '',
            $application['date_of_birth'] ?? '',
            $application['address'] ?? '',
            $application['program_title'] ?? '',
            ucfirst($application['status']),
            $application['motivation'] ?? '',
            $application['education_details'] ?? '',
            $application['work_experience'] ?? '',
            $application['submitted_at'],
            $application['updated_at'] ?? ''
        ]);
    }

    fclose($output);
    exit;
}

elseif ($format === 'json') {
    $filename = $applicationId ? 'application_' . $applicationId . '_' . date('Y-m-d') . '.json' : 'applications_' . date('Y-m-d') . '.json';

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo json_encode($applications, JSON_PRETTY_PRINT);
    exit;
}

elseif ($format === 'pdf' && $applicationId) {
    // Simple PDF generation for single application
    $application = $applications[0] ?? null;

    if (!$application) {
        $_SESSION['error'] = "Application not found.";
        Utilities::redirect('/admin/public/applications.php');
    }

    $filename = 'application_' . $applicationId . '_' . date('Y-m-d') . '.pdf';

    // Simple HTML to PDF conversion (basic implementation)
    $html = generateApplicationPDF($application);

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // For a production environment, you would use a proper PDF library like TCPDF or DomPDF
    // This is a basic implementation
    echo $html;
    exit;
}

else {
    $_SESSION['error'] = "Invalid export format.";
    Utilities::redirect('/admin/applications.php');
}

function generateApplicationPDF($application) {
    // Basic HTML structure for PDF conversion
    // In production, use a proper PDF library
    return "
    <html>
    <head>
        <title>Application Details</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; }
            .section { margin: 20px 0; }
            .label { font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>Application Details</h1>
            <p>Application ID: {$application['id']}</p>
        </div>
        
        <div class='section'>
            <h2>Personal Information</h2>
            <p><span class='label'>Full Name:</span> {$application['full_name']}</p>
            <p><span class='label'>Email:</span> {$application['email']}</p>
            <p><span class='label'>Phone:</span> {$application['phone']}</p>
            <p><span class='label'>Date of Birth:</span> {$application['date_of_birth']}</p>
            <p><span class='label'>Address:</span> {$application['address']}</p>
        </div>
        
        <div class='section'>
            <h2>Application Details</h2>
            <p><span class='label'>Program:</span> {$application['program_title']}</p>
            <p><span class='label'>Status:</span> " . ucfirst($application['status']) . "</p>
            <p><span class='label'>Applied Date:</span> {$application['submitted_at']}</p>
        </div>
        
        <div class='section'>
            <h2>Motivation</h2>
            <p>" . nl2br(htmlspecialchars($application['motivation'])) . "</p>
        </div>
        
        <div class='section'>
            <h2>Education</h2>
            <p>" . nl2br(htmlspecialchars($application['education_details'])) . "</p>
        </div>
        
        <div class='section'>
            <h2>Experience</h2>
            <p>" . nl2br(htmlspecialchars($application['work_experience'])) . "</p>
        </div>
    </body>
    </html>";
}
?>