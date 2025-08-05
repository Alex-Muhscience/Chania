<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/User.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    Utilities::redirect('/admin/public/login.php');
    exit();
}

$database = new Database();
$db = $database->connect();
$userModel = new User($db);

// Check permission
if (!$userModel->hasPermission($_SESSION['user_id'], 'reports') && !$userModel->hasPermission($_SESSION['user_id'], '*')) {
    $_SESSION['error'] = "You don't have permission to access this resource.";
    Utilities::redirect('/admin/public/index.php');
    exit();
}

require_once __DIR__ . '/../includes/header.php';

// Define available data sources for export
$dataSources = [
    'users' => [
        'display' => 'Users',
        'table' => 'users',
        'columns' => [
            'id' => 'ID',
            'username' => 'Username',
            'email' => 'Email',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'role' => 'Role',
            'is_active' => 'Active Status',
            'created_at' => 'Registration Date',
            'updated_at' => 'Last Updated'
        ]
    ],
    'applications' => [
        'display' => 'Applications',
        'table' => 'applications',
        'columns' => [
            'id' => 'ID',
            'program_id' => 'Program ID',
            'application_number' => 'Application Number',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'status' => 'Status',
            'submitted_at' => 'Submission Date',
            'updated_at' => 'Last Updated'
        ]
    ],
    'programs' => [
        'display' => 'Programs',
        'table' => 'programs',
        'columns' => [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'duration' => 'Duration',
            'fee' => 'Fee',
            'category' => 'Category',
            'difficulty_level' => 'Difficulty',
            'max_participants' => 'Max Participants',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'is_active' => 'Active Status',
            'is_published' => 'Published',
            'created_at' => 'Created Date'
        ]
    ],
    'events' => [
        'display' => 'Events',
        'table' => 'events',
        'columns' => [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'event_date' => 'Event Date',
            'location' => 'Location',
            'max_participants' => 'Max Participants'
        ]
    ],
    'email_campaigns' => [
        'display' => 'Email Campaigns',
        'table' => 'email_campaigns',
        'columns' => [
            'id' => 'ID',
            'name' => 'Campaign Name',
            'subject' => 'Subject',
            'status' => 'Status',
            'created_at' => 'Created Date',
            'sent_at' => 'Sent Date'
        ]
    ],
    'sms_campaigns' => [
        'display' => 'SMS Campaigns',
        'table' => 'sms_campaigns',
        'columns' => [
            'id' => 'ID',
            'name' => 'Campaign Name',
            'status' => 'Status',
            'created_at' => 'Created Date',
            'sent_at' => 'Sent Date'
        ]
    ],
    'user_activity_logs' => [
        'display' => 'User Activity Logs',
        'table' => 'user_activity_logs',
        'columns' => [
            'id' => 'ID',
            'user_id' => 'User ID',
            'action' => 'Action',
            'description' => 'Description',
            'ip_address' => 'IP Address',
            'user_agent' => 'User Agent',
            'created_at' => 'Date/Time'
        ]
    ]
];

// Handle export request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    $dataSource = $_POST['data_source'];
    $selectedColumns = $_POST['columns'] ?? [];
    $dateFrom = $_POST['date_from'] ?? '';
    $dateTo = $_POST['date_to'] ?? '';
    $format = $_POST['format'] ?? 'csv';

    if (!isset($dataSources[$dataSource])) {
        $_SESSION['error'] = "Invalid data source selected.";
    } elseif (empty($selectedColumns)) {
        $_SESSION['error'] = "Please select at least one column to export.";
    } else {
        $table = $dataSources[$dataSource]['table'];
        $allowedCols = array_keys($dataSources[$dataSource]['columns']);
        $validColumns = array_intersect($selectedColumns, $allowedCols);

        if (empty($validColumns)) {
            $_SESSION['error'] = "No valid columns selected.";
        } else {
            // Build and execute query
            $sql = "SELECT `" . implode("`, `", $validColumns) . "` FROM `$table`";
            $params = [];

            // Add date filter if provided
            if ($dateFrom || $dateTo) {
                $whereClause = [];
                // Determine the correct date column for this table
                $dateColumn = 'created_at';
                if ($dataSource === 'applications') {
                    $dateColumn = 'submitted_at';
                }
                
                if ($dateFrom) {
                    $whereClause[] = "$dateColumn >= ?";
                    $params[] = $dateFrom . ' 00:00:00';
                }
                if ($dateTo) {
                    $whereClause[] = "$dateColumn <= ?";
                    $params[] = $dateTo . ' 23:59:59';
                }
                if (!empty($whereClause)) {
                    $sql .= " WHERE " . implode(" AND ", $whereClause);
                }
            }

            // Order by the appropriate date column
            $orderColumn = ($dataSource === 'applications') ? 'submitted_at' : 'created_at';
            $sql .= " ORDER BY $orderColumn DESC";

            try {
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Generate export file
                if ($format === 'csv') {
                    exportToCSV($results, $validColumns, $dataSources[$dataSource]['columns'], $dataSource);
                } else {
                    exportToJSON($results, $dataSource);
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Error exporting data: " . $e->getMessage();
            }
        }
    }
}

function exportToCSV($data, $columns, $columnLabels, $dataSource) {
    $filename = $dataSource . '_export_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Write header row
    $headers = [];
    foreach ($columns as $col) {
        $headers[] = $columnLabels[$col];
    }
    fputcsv($output, $headers);
    
    // Write data rows
    foreach ($data as $row) {
        $rowData = [];
        foreach ($columns as $col) {
            $rowData[] = $row[$col] ?? '';
        }
        fputcsv($output, $rowData);
    }
    
    fclose($output);
    exit();
}

function exportToJSON($data, $dataSource) {
    $filename = $dataSource . '_export_' . date('Y-m-d_H-i-s') . '.json';
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo json_encode([
        'export_date' => date('Y-m-d H:i:s'),
        'data_source' => $dataSource,
        'record_count' => count($data),
        'data' => $data
    ], JSON_PRETTY_PRINT);
    exit();
}

?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Data Export Tools</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="reports.php">Reports</a></li>
        <li class="breadcrumb-item active">Data Export</li>
    </ol>

    <div class="row">
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <i class="fas fa-download me-1"></i>
                    Export Data
                </div>
                <div class="card-body">
                    <form method="POST">
                        <!-- Data Source Selection -->
                        <div class="mb-3">
                            <label for="data_source" class="form-label">Data Source <span class="text-danger">*</span></label>
                            <select class="form-control" id="data_source" name="data_source" required>
                                <option value="" disabled selected>-- Select Data Source --</option>
                                <?php foreach ($dataSources as $key => $source): ?>
                                    <option value="<?= $key ?>" data-columns='<?= json_encode($source['columns']) ?>'>
                                        <?= $source['display'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Column Selection -->
                        <div class="mb-3">
                            <label class="form-label">Columns to Export <span class="text-danger">*</span></label>
                            <div id="columns-container" class="border p-3 rounded">
                                <p class="text-muted">Select a data source to see available columns.</p>
                            </div>
                        </div>

                        <!-- Date Range Filter -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from">
                                <small class="form-text text-muted">Leave empty to include all records</small>
                            </div>
                            <div class="col-md-6">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to">
                                <small class="form-text text-muted">Leave empty to include all records</small>
                            </div>
                        </div>

                        <!-- Export Format -->
                        <div class="mb-3">
                            <label class="form-label">Export Format</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" id="format_csv" value="csv" checked>
                                <label class="form-check-label" for="format_csv">CSV (Comma Separated Values)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" id="format_json" value="json">
                                <label class="form-check-label" for="format_json">JSON (JavaScript Object Notation)</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="reports.php" class="btn btn-secondary">Back to Reports</a>
                            <button type="submit" name="export" class="btn btn-success">
                                <i class="fas fa-download"></i> Export Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Export Information
                </div>
                <div class="card-body">
                    <h6>Available Data Sources:</h6>
                    <ul class="list-unstyled">
                        <?php foreach ($dataSources as $key => $source): ?>
                            <li class="mb-2">
                                <strong><?= $source['display'] ?></strong>
                                <br><small class="text-muted"><?= count($source['columns']) ?> columns available</small>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <hr>

                    <h6>Export Tips:</h6>
                    <ul class="small">
                        <li>CSV format is best for spreadsheet applications like Excel</li>
                        <li>JSON format is ideal for programmatic data processing</li>
                        <li>Use date filters to limit the export to specific time periods</li>
                        <li>Large exports may take some time to process</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Data Statistics
                </div>
                <div class="card-body">
                    <?php
                    // Get record counts for each data source
                    foreach ($dataSources as $key => $source):
                        try {
                            $stmt = $db->query("SELECT COUNT(*) as count FROM `{$source['table']}`");
                            $count = $stmt->fetchColumn();
                        } catch (Exception $e) {
                            $count = 'N/A';
                        }
                    ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= $source['display'] ?>:</span>
                            <span class="badge badge-primary"><?= number_format($count) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dataSourceSelect = document.getElementById('data_source');
    const columnsContainer = document.getElementById('columns-container');

    dataSourceSelect.addEventListener('change', function() {
        updateColumns();
    });

    function updateColumns() {
        const selectedSource = dataSourceSelect.value;
        if (!selectedSource) {
            columnsContainer.innerHTML = '<p class="text-muted">Select a data source to see available columns.</p>';
            return;
        }

        const selectedOption = dataSourceSelect.options[dataSourceSelect.selectedIndex];
        const columns = JSON.parse(selectedOption.getAttribute('data-columns'));

        let html = '<div class="row">';
        html += '<div class="col-12 mb-2"><label><input type="checkbox" id="select-all"> <strong>Select All</strong></label></div>';
        
        Object.entries(columns).forEach(([key, display]) => {
            html += `
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input column-checkbox" type="checkbox" name="columns[]" value="${key}" id="col-${key}">
                        <label class="form-check-label" for="col-${key}">${display}</label>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        columnsContainer.innerHTML = html;

        // Add select all functionality
        const selectAllCheckbox = document.getElementById('select-all');
        const columnCheckboxes = document.querySelectorAll('.column-checkbox');

        selectAllCheckbox.addEventListener('change', function() {
            columnCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        columnCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(columnCheckboxes).every(cb => cb.checked);
                const noneChecked = Array.from(columnCheckboxes).every(cb => !cb.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
