<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/User.php';

require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->connect();
$userModel = new User($db);

// Check permission
if (!$userModel->hasPermission($_SESSION['user_id'], 'reports') && !$userModel->hasPermission($_SESSION['user_id'], '*')) {
    $_SESSION['error'] = "You don't have permission to access this resource.";
    header('Location: index.php');
    exit();
}

$reportId = $_GET['id'] ?? null;
if (!$reportId) {
    $_SESSION['error'] = "No report specified.";
    header('Location: reports.php');
    exit();
}

// Fetch report details
$stmt = $db->prepare("SELECT * FROM reports WHERE id = ?");
$stmt->execute([$reportId]);
$report = $stmt->fetch();

if (!$report) {
    $_SESSION['error'] = "Report not found.";
    header('Location: reports.php');
    exit();
}

// Security check: only creator or public reports are viewable
if (!$report['is_public'] && $report['created_by'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "You do not have permission to view this report.";
    header('Location: reports.php');
    exit();
}

$config = json_decode($report['config'], true);
$dataSourceKey = $config['dataSource'];

// Define available data sources
$dataSources = [
    'users' => ['table' => 'users', 'columns' => ['id', 'username', 'email', 'first_name', 'last_name', 'role', 'is_active', 'created_at']],
    'applications' => ['table' => 'applications', 'columns' => ['id', 'program_id', 'application_number', 'first_name', 'last_name', 'email', 'status', 'submitted_at']],
    'programs' => ['table' => 'programs', 'columns' => ['id', 'title', 'description', 'is_active', 'created_at']],
    'events' => ['table' => 'events', 'columns' => ['id', 'title', 'description', 'event_date', 'created_at']],
    'email_campaigns' => ['table' => 'email_campaigns', 'columns' => ['id', 'name', 'subject', 'status', 'sent_at']]
];

if (!isset($dataSources[$dataSourceKey])) {
    $_SESSION['error'] = "Invalid data source in report config.";
    header('Location: reports.php');
    exit();
}

// Build query
$table = $dataSources[$dataSourceKey]['table'];
$allowedCols = $dataSources[$dataSourceKey]['columns'];
$selectedCols = array_intersect($config['columns'], $allowedCols);

if (empty($selectedCols)) {
     $_SESSION['error'] = "No valid columns selected for this report.";
    header('Location: reports.php');
    exit();
}

$sql = "SELECT `" . implode("`, `", $selectedCols) . "` FROM `$table`";
$params = [];

// Add filters
if (!empty($config['filters'])) {
    $sql .= " WHERE ";
    $filterClauses = [];
    foreach ($config['filters'] as $filter) {
        if (in_array($filter['column'], $allowedCols) && !empty($filter['value'])) {
            $operator = $filter['operator'];
            
            if ($operator === 'IN' || $operator === 'NOT IN') {
                $values = array_map('trim', explode(',', $filter['value']));
                $placeholders = implode(',', array_fill(0, count($values), '?'));
                $filterClauses[] = "`{$filter['column']}` $operator ($placeholders)";
                $params = array_merge($params, $values);
            } else {
                $filterClauses[] = "`{$filter['column']}` $operator ?";
                $params[] = ($operator === 'LIKE' || $operator === 'NOT LIKE') 
                    ? '%' . $filter['value'] . '%' 
                    : $filter['value'];
            }
        }
    }
    $sql .= implode(" AND ", $filterClauses);
}

// Execute query
$stmt = $db->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= htmlspecialchars($report['name']) ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="reports.php">Reports</a></li>
        <li class="breadcrumb-item active">View Report</li>
    </ol>

    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                Report Results
            </div>
            <div>
                 <?php if ($report['created_by'] == $_SESSION['user_id']): ?>
                    <a href="report_builder.php?id=<?= $reportId ?>" class="btn btn-sm btn-outline-secondary" title="Edit Report">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                <?php endif; ?>
                <a href="#" class="btn btn-sm btn-outline-success" id="export-csv-btn">
                    <i class="fas fa-download"></i> Export to CSV
                </a>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted"><?= htmlspecialchars($report['description']) ?></p>
            
            <!-- Chart -->
            <?php if ($config['chartType'] !== 'table'): ?>
                <div class="chart-container mb-4">
                     <canvas id="reportChart"></canvas>
                </div>
            <?php endif; ?>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="reportTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <?php foreach ($selectedCols as $col): ?>
                                <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $col))) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($results)): ?>
                            <tr>
                                <td colspan="<?= count($selectedCols) ?>" class="text-center">No data available for this report.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <?php foreach ($selectedCols as $col): ?>
                                        <td><?= htmlspecialchars($row[$col] ?? 'N/A') ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartType = '<?= $config["chartType"] ?>';
    const reportData = <?= json_encode($results) ?>;
    const columns = <?= json_encode($selectedCols) ?>;

    if (chartType !== 'table' && reportData.length > 0) {
        renderChart();
    }

    function renderChart() {
        const ctx = document.getElementById('reportChart').getContext('2d');
        
        // For simplicity, using the first column for labels and the second for data
        // This can be made more configurable in the report builder
        const labels = reportData.map(row => row[columns[0]]);
        const data = reportData.map(row => parseFloat(row[columns[1]])); // Assuming second column is numeric

        new Chart(ctx, {
            type: chartType,
            data: {
                labels: labels,
                datasets: [{
                    label: '<?= htmlspecialchars($report["name"]) ?>',
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // CSV Export
    document.getElementById('export-csv-btn').addEventListener('click', function(e) {
        e.preventDefault();
        exportTableToCSV('report.csv');
    });

    function exportTableToCSV(filename) {
        let csv = [];
        const rows = document.querySelectorAll("#reportTable tr");
        
        for (const row of rows) {
            let rowData = [];
            const cols = row.querySelectorAll("td, th");
            
            for (const col of cols) {
                // Escape quotes and wrap in quotes if it contains a comma
                let data = col.innerText.replace(/"/g, '""');
                if (data.includes(',')) {
                    data = `"${data}"`;
                }
                rowData.push(data);
            }
            csv.push(rowData.join(','));
        }

        const csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
        const downloadLink = document.createElement("a");
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

