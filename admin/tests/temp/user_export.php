<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/BaseController.php';
require_once __DIR__ . '/../../../shared/Core/User.php';
require_once __DIR__ . '/../../../shared/Core/Role.php';

// Initialize session and authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

// Initialize controller for permission checks
$controller = new BaseController();

// Check permissions - users or admin access required
if (!$controller->hasPermission('users') && !$controller->hasPermission('*')) {
    $_SESSION['flash_message'] = 'Access denied. You do not have permission to export users.';
    $_SESSION['flash_type'] = 'error';
    header('Location: index.php');
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $userModel = new User($db);
    $roleModel = new Role($db);

    // Get available roles for filtering
    $roles = $roleModel->getAll();

    // Handle export request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $exportType = $_POST['export_type'] ?? 'csv';
        $filters = [
            'search' => $_POST['search'] ?? '',
            'role' => isset($_POST['role']) && $_POST['role'] !== '' ? (int)$_POST['role'] : null,
            'status' => isset($_POST['status']) && $_POST['status'] !== '' ? (int)$_POST['status'] : null,
            'date_from' => $_POST['date_from'] ?? '',
            'date_to' => $_POST['date_to'] ?? ''
        ];
        
        $selectedFields = $_POST['fields'] ?? [];
        if (empty($selectedFields)) {
            $selectedFields = ['id', 'username', 'email', 'full_name', 'role', 'status', 'created_at'];
        }

        // Get users based on filters
        $users = getUsersForExport($userModel, $filters);

        if (empty($users)) {
            $error = 'No users found matching the specified criteria.';
        } else {
            // Export users
            exportUsers($users, $selectedFields, $exportType);
            exit; // Stop execution after export
        }
    }

    // Get user statistics for display
    $stats = $userModel->getStatistics();

} catch (Exception $e) {
    error_log('User export error: ' . $e->getMessage());
    $error = 'An error occurred while loading the export page.';
}

/**
 * Get users for export based on filters
 */
function getUsersForExport($userModel, $filters) {
    $sql = "SELECT u.*, ur.display_name as role_name, ur.name as role_slug,
                   CONCAT(u.first_name, ' ', u.last_name) as full_name
            FROM users u 
            LEFT JOIN user_roles ur ON u.role_id = ur.id 
            WHERE 1=1";
    $params = [];

    // Apply filters
    if (!empty($filters['search'])) {
        $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
        $searchTerm = "%{$filters['search']}%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }

    if ($filters['role'] !== null) {
        $sql .= " AND u.role_id = ?";
        $params[] = $filters['role'];
    }

    if ($filters['status'] !== null) {
        $sql .= " AND u.is_active = ?";
        $params[] = $filters['status'];
    }

    if (!empty($filters['date_from'])) {
        $sql .= " AND u.created_at >= ?";
        $params[] = $filters['date_from'] . ' 00:00:00';
    }

    if (!empty($filters['date_to'])) {
        $sql .= " AND u.created_at <= ?";
        $params[] = $filters['date_to'] . ' 23:59:59';
    }

    $sql .= " ORDER BY u.created_at DESC";

    global $db;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Export users to specified format
 */
function exportUsers($users, $selectedFields, $exportType) {
    $filename = 'users_export_' . date('Y-m-d_H-i-s');
    
    // Define field mappings
    $fieldMappings = [
        'id' => 'ID',
        'username' => 'Username',
        'email' => 'Email',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'full_name' => 'Full Name',
        'role_name' => 'Role',
        'is_active' => 'Status',
        'created_at' => 'Created Date',
        'updated_at' => 'Updated Date',
        'last_login' => 'Last Login'
    ];

    switch ($exportType) {
        case 'csv':
            exportToCSV($users, $selectedFields, $fieldMappings, $filename);
            break;
        case 'excel':
            exportToExcel($users, $selectedFields, $fieldMappings, $filename);
            break;
        case 'json':
            exportToJSON($users, $selectedFields, $fieldMappings, $filename);
            break;
        case 'xml':
            exportToXML($users, $selectedFields, $fieldMappings, $filename);
            break;
        default:
            exportToCSV($users, $selectedFields, $fieldMappings, $filename);
    }
}

/**
 * Export to CSV format
 */
function exportToCSV($users, $selectedFields, $fieldMappings, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Cache-Control: no-cache, must-revalidate');
    
    $output = fopen('php://output', 'w');
    
    // Write headers
    $headers = [];
    foreach ($selectedFields as $field) {
        $headers[] = $fieldMappings[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
    fputcsv($output, $headers);
    
    // Write data
    foreach ($users as $user) {
        $row = [];
        foreach ($selectedFields as $field) {
            $value = '';
            switch ($field) {
                case 'full_name':
                    $value = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                    break;
                case 'role':
                case 'role_name':
                    $value = $user['role_name'] ?? 'N/A';
                    break;
                case 'is_active':
                case 'status':
                    $value = $user['is_active'] ? 'Active' : 'Inactive';
                    break;
                case 'created_at':
                case 'updated_at':
                case 'last_login':
                    $value = $user[$field] ? date('Y-m-d H:i:s', strtotime($user[$field])) : 'N/A';
                    break;
                default:
                    $value = $user[$field] ?? 'N/A';
            }
            $row[] = $value;
        }
        fputcsv($output, $row);
    }
    
    fclose($output);
}

/**
 * Export to Excel format (CSV with UTF-8 BOM for Excel compatibility)
 */
function exportToExcel($users, $selectedFields, $fieldMappings, $filename) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Output UTF-8 BOM for Excel
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    
    // Write headers
    $headers = [];
    foreach ($selectedFields as $field) {
        $headers[] = $fieldMappings[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
    fputcsv($output, $headers, "\t");
    
    // Write data
    foreach ($users as $user) {
        $row = [];
        foreach ($selectedFields as $field) {
            $value = '';
            switch ($field) {
                case 'full_name':
                    $value = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                    break;
                case 'role':
                case 'role_name':
                    $value = $user['role_name'] ?? 'N/A';
                    break;
                case 'is_active':
                case 'status':
                    $value = $user['is_active'] ? 'Active' : 'Inactive';
                    break;
                case 'created_at':
                case 'updated_at':
                case 'last_login':
                    $value = $user[$field] ? date('Y-m-d H:i:s', strtotime($user[$field])) : 'N/A';
                    break;
                default:
                    $value = $user[$field] ?? 'N/A';
            }
            $row[] = $value;
        }
        fputcsv($output, $row, "\t");
    }
    
    fclose($output);
}

/**
 * Export to JSON format
 */
function exportToJSON($users, $selectedFields, $fieldMappings, $filename) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '.json"');
    header('Cache-Control: no-cache, must-revalidate');
    
    $exportData = [];
    
    foreach ($users as $user) {
        $userData = [];
        foreach ($selectedFields as $field) {
            $key = $fieldMappings[$field] ?? ucfirst(str_replace('_', ' ', $field));
            switch ($field) {
                case 'full_name':
                    $userData[$key] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                    break;
                case 'role':
                case 'role_name':
                    $userData[$key] = $user['role_name'] ?? 'N/A';
                    break;
                case 'is_active':
                case 'status':
                    $userData[$key] = $user['is_active'] ? 'Active' : 'Inactive';
                    break;
                case 'created_at':
                case 'updated_at':
                case 'last_login':
                    $userData[$key] = $user[$field] ? date('Y-m-d H:i:s', strtotime($user[$field])) : null;
                    break;
                default:
                    $userData[$key] = $user[$field] ?? null;
            }
        }
        $exportData[] = $userData;
    }
    
    echo json_encode([
        'export_info' => [
            'exported_at' => date('Y-m-d H:i:s'),
            'total_records' => count($exportData),
            'exported_by' => $_SESSION['username'] ?? 'Unknown'
        ],
        'users' => $exportData
    ], JSON_PRETTY_PRINT);
}

/**
 * Export to XML format
 */
function exportToXML($users, $selectedFields, $fieldMappings, $filename) {
    header('Content-Type: text/xml');
    header('Content-Disposition: attachment; filename="' . $filename . '.xml"');
    header('Cache-Control: no-cache, must-revalidate');
    
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><users_export></users_export>');
    
    // Add export info
    $exportInfo = $xml->addChild('export_info');
    $exportInfo->addChild('exported_at', date('Y-m-d H:i:s'));
    $exportInfo->addChild('total_records', count($users));
    $exportInfo->addChild('exported_by', $_SESSION['username'] ?? 'Unknown');
    
    // Add users
    $usersNode = $xml->addChild('users');
    
    foreach ($users as $userData) {
        $userNode = $usersNode->addChild('user');
        
        foreach ($selectedFields as $field) {
            $key = strtolower(str_replace(' ', '_', $fieldMappings[$field] ?? $field));
            switch ($field) {
                case 'full_name':
                    $value = trim(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? ''));
                    break;
                case 'role':
                case 'role_name':
                    $value = $userData['role_name'] ?? 'N/A';
                    break;
                case 'is_active':
                case 'status':
                    $value = $userData['is_active'] ? 'Active' : 'Inactive';
                    break;
                case 'created_at':
                case 'updated_at':
                case 'last_login':
                    $value = $userData[$field] ? date('Y-m-d H:i:s', strtotime($userData[$field])) : 'N/A';
                    break;
                default:
                    $value = $userData[$field] ?? 'N/A';
            }
            $userNode->addChild($key, htmlspecialchars($value));
        }
    }
    
    echo $xml->asXML();
}

// Page settings for header
$pageTitle = 'User Export';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'User Management', 'url' => BASE_URL . '/admin/users.php'],
    ['title' => 'Export Users']
];

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-export text-primary me-2"></i>
            Export Users
        </h1>
        <a href="users.php" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Users
        </a>
    </div>

    <!-- Statistics Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total'] ?? 0; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['active'] ?? 0; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">New This Month</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['this_month'] ?? 0; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Available Roles</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($roles ?? []); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tag fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Export Configuration Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-cogs me-2"></i>
                Export Configuration
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" id="export-form">
                <div class="row">
                    <!-- Export Format -->
                    <div class="col-md-6 mb-3">
                        <label for="export_type" class="form-label">
                            <i class="fas fa-file-export me-1"></i>
                            Export Format
                        </label>
                        <select class="form-select" id="export_type" name="export_type" required>
                            <option value="csv">CSV (Comma Separated Values)</option>
                            <option value="excel">Excel Compatible (.xls)</option>
                            <option value="json">JSON (JavaScript Object Notation)</option>
                            <option value="xml">XML (Extensible Markup Language)</option>
                        </select>
                        <div class="form-text">Choose the format for your exported data.</div>
                    </div>

                    <!-- Search Filter -->
                    <div class="col-md-6 mb-3">
                        <label for="search" class="form-label">
                            <i class="fas fa-search me-1"></i>
                            Search Filter
                        </label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Username, email, or name...">
                        <div class="form-text">Filter users by username, email, or name.</div>
                    </div>
                </div>

                <div class="row">
                    <!-- Role Filter -->
                    <div class="col-md-4 mb-3">
                        <label for="role" class="form-label">
                            <i class="fas fa-user-tag me-1"></i>
                            Role Filter
                        </label>
                        <select class="form-select" id="role" name="role">
                            <option value="">All Roles</option>
                            <?php if (!empty($roles)): ?>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>">
                                        <?php echo htmlspecialchars($role['display_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">
                            <i class="fas fa-toggle-on me-1"></i>
                            Status Filter
                        </label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="1">Active Only</option>
                            <option value="0">Inactive Only</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Created Date Range
                        </label>
                        <div class="input-group">
                            <input type="date" class="form-control" name="date_from" placeholder="From">
                            <span class="input-group-text">to</span>
                            <input type="date" class="form-control" name="date_to" placeholder="To">
                        </div>
                    </div>
                </div>

                <!-- Field Selection -->
                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-list-check me-1"></i>
                        Fields to Export
                    </label>
                    <div class="form-text mb-2">Select which user fields to include in the export.</div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="id" id="field_id" checked>
                                <label class="form-check-label" for="field_id">User ID</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="username" id="field_username" checked>
                                <label class="form-check-label" for="field_username">Username</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="email" id="field_email" checked>
                                <label class="form-check-label" for="field_email">Email</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="first_name" id="field_first_name">
                                <label class="form-check-label" for="field_first_name">First Name</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="last_name" id="field_last_name">
                                <label class="form-check-label" for="field_last_name">Last Name</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="full_name" id="field_full_name" checked>
                                <label class="form-check-label" for="field_full_name">Full Name</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="role_name" id="field_role" checked>
                                <label class="form-check-label" for="field_role">Role</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="is_active" id="field_status" checked>
                                <label class="form-check-label" for="field_status">Status</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="created_at" id="field_created" checked>
                                <label class="form-check-label" for="field_created">Created Date</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="updated_at" id="field_updated">
                                <label class="form-check-label" for="field_updated">Updated Date</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="last_login" id="field_last_login">
                                <label class="form-check-label" for="field_last_login">Last Login</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllFields()">
                            <i class="fas fa-check-square"></i> Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllFields()">
                            <i class="fas fa-square"></i> Deselect All
                        </button>
                    </div>
                </div>

                <!-- Export Actions -->
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Export will include users matching your selected criteria.
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-primary me-2" onclick="previewExport()">
                            <i class="fas fa-eye me-1"></i>
                            Preview
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download me-1"></i>
                            Export Users
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Tips -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-info">
                <i class="fas fa-lightbulb me-2"></i>
                Export Tips & Information
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary"><i class="fas fa-file-csv me-1"></i> CSV Format</h6>
                    <ul class="text-muted small">
                        <li>Best for spreadsheet applications like Excel or Google Sheets</li>
                        <li>Universal compatibility with most data analysis tools</li>
                        <li>Lightweight and fast to generate</li>
                    </ul>

                    <h6 class="text-success"><i class="fas fa-file-excel me-1"></i> Excel Format</h6>
                    <ul class="text-muted small">
                        <li>Optimized for Microsoft Excel</li>
                        <li>Includes UTF-8 encoding for special characters</li>
                        <li>Tab-separated for better Excel compatibility</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-warning"><i class="fas fa-file-code me-1"></i> JSON Format</h6>
                    <ul class="text-muted small">
                        <li>Perfect for API integrations and data processing</li>
                        <li>Includes export metadata and user information</li>
                        <li>Machine-readable structured data format</li>
                    </ul>

                    <h6 class="text-info"><i class="fas fa-file-code me-1"></i> XML Format</h6>
                    <ul class="text-muted small">
                        <li>Structured markup format for data exchange</li>
                        <li>Compatible with enterprise systems</li>
                        <li>Includes export metadata and validation</li>
                    </ul>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <i class="fas fa-shield-alt me-2"></i>
                <strong>Security Note:</strong> Exported data may contain sensitive information. 
                Handle exported files securely and ensure they are stored and transmitted according to your organization's data protection policies.
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>
                    Export Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="preview-content">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading preview...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="proceedWithExport()">
                    <i class="fas fa-download me-1"></i>
                    Proceed with Export
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Field selection functions
function selectAllFields() {
    document.querySelectorAll('input[name="fields[]"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAllFields() {
    document.querySelectorAll('input[name="fields[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}

// Preview function
function previewExport() {
    const form = document.getElementById('export-form');
    const formData = new FormData(form);
    formData.append('preview', '1');
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
    
    // Simulate preview loading (in real implementation, you'd make an AJAX call)
    setTimeout(() => {
        const selectedFields = [];
        document.querySelectorAll('input[name="fields[]"]:checked').forEach(checkbox => {
            selectedFields.push(checkbox.nextElementSibling.textContent);
        });
        
        const filters = {
            search: document.getElementById('search').value,
            role: document.getElementById('role').selectedOptions[0]?.text || 'All Roles',
            status: document.getElementById('status').selectedOptions[0]?.text || 'All Status',
            format: document.getElementById('export_type').selectedOptions[0]?.text
        };
        
        document.getElementById('preview-content').innerHTML = `
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i>Export Configuration Summary</h6>
                <p><strong>Format:</strong> ${filters.format}</p>
                <p><strong>Filters:</strong></p>
                <ul>
                    <li>Search: ${filters.search || 'None'}</li>
                    <li>Role: ${filters.role}</li>
                    <li>Status: ${filters.status}</li>
                </ul>
                <p><strong>Selected Fields (${selectedFields.length}):</strong></p>
                <div class="d-flex flex-wrap gap-1">
                    ${selectedFields.map(field => `<span class="badge bg-primary">${field}</span>`).join('')}
                </div>
            </div>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                This is a preview of your export configuration. Click "Proceed with Export" to download the actual data.
            </div>
        `;
    }, 1000);
}

// Proceed with export from preview
function proceedWithExport() {
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('previewModal')).hide();
    
    // Submit form
    document.getElementById('export-form').submit();
}

// Form validation
document.getElementById('export-form').addEventListener('submit', function(e) {
    const selectedFields = document.querySelectorAll('input[name="fields[]"]:checked');
    if (selectedFields.length === 0) {
        e.preventDefault();
        alert('Please select at least one field to export.');
        return false;
    }
});

// Auto-adjust date range
document.querySelector('input[name="date_from"]').addEventListener('change', function() {
    const toDate = document.querySelector('input[name="date_to"]');
    if (!toDate.value && this.value) {
        toDate.min = this.value;
    }
});

document.querySelector('input[name="date_to"]').addEventListener('change', function() {
    const fromDate = document.querySelector('input[name="date_from"]');
    if (!fromDate.value && this.value) {
        fromDate.max = this.value;
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
