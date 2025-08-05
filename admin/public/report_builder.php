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

$reportId = $_GET['id'] ?? null;
$report = null;
$config = null;

if ($reportId) {
    $stmt = $db->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->execute([$reportId]);
    $report = $stmt->fetch();

    // Security check: ensure user can edit this report
    if (!$report || $report['created_by'] != $_SESSION['user_id']) {
        $_SESSION['error'] = "Report not found or you don't have permission to edit it.";
        Utilities::redirect('/admin/public/reports.php');
        exit();
    }
    $config = json_decode($report['config'], true);
}

// Define available data sources and their columns
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
            'is_active' => 'Active',
            'created_at' => 'Registration Date'
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
            'status' => 'Status',
            'submitted_at' => 'Submission Date'
        ]
    ],
    'programs' => [
        'display' => 'Programs',
        'table' => 'programs',
        'columns' => [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'is_active' => 'Active'
        ]
    ],
    'events' => [
        'display' => 'Events',
        'table' => 'events',
        'columns' => [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'event_date' => 'Event Date'
        ]
    ],
    'email_campaigns' => [
        'display' => 'Email Campaigns',
        'table' => 'email_campaigns',
        'columns' => [
            'id' => 'ID',
            'name' => 'Name',
            'subject' => 'Subject',
            'status' => 'Status',
            'sent_at' => 'Sent Date'
        ]
    ]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportName = $_POST['report_name'];
    $description = $_POST['description'];
    $dataSource = $_POST['data_source'];
    $columns = $_POST['columns'];
    $filters = $_POST['filters'] ?? [];
    $chartType = $_POST['chart_type'];
    $isPublic = isset($_POST['is_public']) ? 1 : 0;

    $config = [
        'dataSource' => $dataSource,
        'columns' => $columns,
        'filters' => $filters,
        'chartType' => $chartType
    ];

    $jsonConfig = json_encode($config);

    if ($reportId) {
        // Update existing report
        $stmt = $db->prepare("UPDATE reports SET name = ?, description = ?, report_type = ?, config = ?, is_public = ? WHERE id = ?");
        $stmt->execute([$reportName, $description, $dataSource, $jsonConfig, $isPublic, $reportId]);
        $_SESSION['success'] = "Report updated successfully.";
    } else {
        // Create new report
        $stmt = $db->prepare("INSERT INTO reports (name, description, report_type, config, created_by, is_public) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$reportName, $description, $dataSource, $jsonConfig, $_SESSION['user_id'], $isPublic]);
        $reportId = $db->lastInsertId();
        $_SESSION['success'] = "Report created successfully.";
    }

    Utilities::redirect('/admin/public/report_view.php?id=' . $reportId);
    exit();
}

?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $reportId ? 'Edit' : 'Create' ?> Custom Report</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="reports.php">Reports</a></li>
        <li class="breadcrumb-item active"><?= $reportId ? 'Edit' : 'Create' ?> Report</li>
    </ol>

    <div class="card shadow mb-4">
        <div class="card-header">
            <i class="fas fa-chart-bar me-1"></i>
            Report Builder
        </div>
        <div class="card-body">
            <form id="reportBuilderForm" method="POST">
                <!-- Report Details -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="report_name" class="form-label">Report Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="report_name" name="report_name" 
                               value="<?= htmlspecialchars($report['name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description"
                               value="<?= htmlspecialchars($report['description'] ?? '') ?>">
                    </div>
                </div>

                <!-- Data Source -->
                <div class="mb-3">
                    <label for="data_source" class="form-label">Data Source <span class="text-danger">*</span></label>
                    <select class="form-control" id="data_source" name="data_source" required>
                        <option value="" disabled <?= !$config ? 'selected' : '' ?>>-- Select a Data Source --</option>
                        <?php foreach ($dataSources as $key => $source): ?>
                            <option value="<?= $key ?>" data-columns='<?= json_encode(array_keys($source['columns'])) ?>'
                                <?= ($config && $config['dataSource'] == $key) ? 'selected' : '' ?>>
                                <?= $source['display'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Columns -->
                <div class="mb-3">
                    <label class="form-label">Columns <span class="text-danger">*</span></label>
                    <div id="columns-container" class="border p-3 rounded">
                        <!-- Columns will be dynamically populated here based on data source -->
                        <p class="text-muted">Select a data source to see available columns.</p>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="mb-3">
                    <label class="form-label">Filters</label>
                    <div id="filters-container" class="border p-3 rounded">
                        <p class="text-muted">Select a data source to add filters.</p>
                    </div>
                    <button type="button" id="add-filter-btn" class="btn btn-sm btn-secondary mt-2" disabled>
                        <i class="fas fa-plus"></i> Add Filter
                    </button>
                </div>

                <!-- Chart Type -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="chart_type" class="form-label">Chart Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="chart_type" name="chart_type">
                            <option value="table" <?= ($config && $config['chartType'] == 'table') ? 'selected' : '' ?>>Table</option>
                            <option value="bar" <?= ($config && $config['chartType'] == 'bar') ? 'selected' : '' ?>>Bar Chart</option>
                            <option value="line" <?= ($config && $config['chartType'] == 'line') ? 'selected' : '' ?>>Line Chart</option>
                            <option value="pie" <?= ($config && $config['chartType'] == 'pie') ? 'selected' : '' ?>>Pie Chart</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="is_public" class="form-label">Visibility</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="is_public" name="is_public" 
                                   value="1" <?= ($report && $report['is_public']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_public">Make this report public</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="reports.php" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $reportId ? 'Update' : 'Create' ?> Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dataSourceSelect = document.getElementById('data_source');
    const columnsContainer = document.getElementById('columns-container');
    const filtersContainer = document.getElementById('filters-container');
    const addFilterBtn = document.getElementById('add-filter-btn');

    const dataSources = <?= json_encode($dataSources) ?>;
    const selectedConfig = <?= json_encode($config) ?>;

    function updateColumns() {
        const selectedSource = dataSourceSelect.value;
        if (!selectedSource) {
            columnsContainer.innerHTML = '<p class="text-muted">Select a data source to see available columns.</p>';
            addFilterBtn.disabled = true;
            return;
        }

        addFilterBtn.disabled = false;
        const sourceData = dataSources[selectedSource];
        let html = '<div class="row">';
        Object.entries(sourceData.columns).forEach(([key, display]) => {
            const isChecked = selectedConfig && selectedConfig.dataSource === selectedSource && selectedConfig.columns.includes(key);
            html += `
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="columns[]" value="${key}" id="col-${key}" ${isChecked ? 'checked' : ''}>
                        <label class="form-check-label" for="col-${key}">${display}</label>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        columnsContainer.innerHTML = html;
    }

    function addFilter(filter = {}) {
        const filterIndex = filtersContainer.children.length;
        const selectedSource = dataSourceSelect.value;
        if (!selectedSource) return;

        const sourceColumns = dataSources[selectedSource].columns;

        let columnOptions = '';
        Object.entries(sourceColumns).forEach(([key, display]) => {
            const isSelected = filter.column === key;
            columnOptions += `<option value="${key}" ${isSelected ? 'selected' : ''}>${display}</option>`;
        });

        const operators = {
            '=': 'Equals',
            '!=': 'Not Equals',
            '>': 'Greater Than',
            '<': 'Less Than',
            '>=': 'Greater Than or Equal',
            '<=': 'Less Than or Equal',
            'LIKE': 'Contains',
            'NOT LIKE': 'Does Not Contain',
            'IN': 'In List',
            'NOT IN': 'Not In List'
        };

        let operatorOptions = '';
        Object.entries(operators).forEach(([key, display]) => {
            const isSelected = filter.operator === key;
            operatorOptions += `<option value="${key}" ${isSelected ? 'selected' : ''}>${display}</option>`;
        });

        const filterHtml = `
            <div class="filter-row row mb-2 align-items-center">
                <div class="col-md-4">
                    <select class="form-control" name="filters[${filterIndex}][column]">${columnOptions}</select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="filters[${filterIndex}][operator]">${operatorOptions}</select>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="filters[${filterIndex}][value]" 
                           value="${filter.value || ''}" placeholder="Value">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger remove-filter-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        const filterNode = document.createElement('div');
        filterNode.innerHTML = filterHtml;
        
        if (filterIndex === 0 && filtersContainer.querySelector('p')) {
            filtersContainer.innerHTML = ''; // Clear placeholder
        }

        filtersContainer.appendChild(filterNode);
    }

    addFilterBtn.addEventListener('click', () => addFilter());

    filtersContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-filter-btn')) {
            e.target.closest('.filter-row').parentNode.remove();
            if (filtersContainer.children.length === 0) {
                 filtersContainer.innerHTML = '<p class="text-muted">Select a data source to add filters.</p>';
            }
        }
    });

    dataSourceSelect.addEventListener('change', function() {
        updateColumns();
        filtersContainer.innerHTML = '<p class="text-muted">Select a data source to add filters.</p>';
        addFilterBtn.disabled = !this.value;
    });

    // Initial setup
    if (dataSourceSelect.value) {
        updateColumns();
        if (selectedConfig && selectedConfig.filters) {
            filtersContainer.innerHTML = ''; // Clear placeholder
            selectedConfig.filters.forEach(filter => addFilter(filter));
        }
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
