<?php
// admin/public/backup.php
// Database Backup and Restore Management UI
require_once __DIR__ . '/../includes/config.php';
session_start();
Utilities::requireRole('admin');
$pageTitle = 'Database Backup & Restore';
$breadcrumbs = [
  ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
  ['title' => 'Backup Management']
];
$backupDir = __DIR__ . '/../backups/';
$errors = [];
$success = '';

// Handle backup download
if (isset($_GET['download']) && preg_match('/^[\w\-.]+\.sql(\.gz)?$/', $_GET['download'])) {
  $backupFile = realpath($backupDir . $_GET['download']);
  if ($backupFile && strpos($backupFile, realpath($backupDir)) === 0) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($backupFile) . '"');
    readfile($backupFile);
    exit;
  }
}
// Handle backup
if (isset($_POST['action']) && $_POST['action'] === 'backup') {
  $filename = 'chania_db_' . date('Ymd_His') . '.sql';
  $filePath = $backupDir . $filename;
  $cmd = '"C:\xampp\mysql\bin\mysqldump.exe" -u root chania_db > "' . $filePath . '"';
  exec($cmd, $output, $ret);
  if ($ret === 0) {
    $success = 'Backup created: ' . htmlspecialchars($filename);
  } else {
    $errors[] = 'Backup failed.';
  }
}
// Handle restore
if (isset($_POST['action']) && $_POST['action'] === 'restore' && !empty($_POST['backup_file'])) {
  $file = basename($_POST['backup_file']);
  $sqlPath = realpath($backupDir . $file);
  if ($sqlPath && strpos($sqlPath, realpath($backupDir)) === 0) {
    $cmd = '"C:\xampp\mysql\bin\mysql.exe" -u root chania_db < "' . $sqlPath . '"';
    exec($cmd, $output, $ret);
    if ($ret === 0) {
      $success = 'Restore completed from: ' . htmlspecialchars($file);
    } else {
      $errors[] = 'Restore failed.';
    }
  }
}
// Fetch backup files
$backupFiles = [];
foreach (glob($backupDir . '*.sql*') as $path) {
  $backupFiles[] = basename($path);
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid mt-4">
  <h1 class="h3 mb-4 text-gray-800"><?php echo $pageTitle; ?></h1>
  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $e) echo '<div>' . $e . '</div>'; ?>
    </div>
  <?php endif; ?>
  <div class="card mb-3">
    <div class="card-header"><strong>Backup Database</strong></div>
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="action" value="backup">
        <button class="btn btn-primary">Create Backup</button>
      </form>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header"><strong>Restore Database</strong></div>
    <div class="card-body">
      <?php if ($backupFiles): ?>
        <form method="post">
          <input type="hidden" name="action" value="restore">
          <div class="form-group">
            <select name="backup_file" class="form-control" required>
              <option value="">Select backup file</option>
              <?php foreach ($backupFiles as $f): ?>
                <option value="<?php echo htmlspecialchars($f); ?>"><?php echo htmlspecialchars($f); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-danger" onclick="return confirm('Are you sure you want to restore this backup? It will overwrite existing data.');">Restore Selected Backup</button>
        </form>
      <?php else: ?>
        <div>No backup files found.</div>
      <?php endif; ?>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header"><strong>Backup Files</strong></div>
    <div class="card-body">
      <?php if ($backupFiles): ?>
        <div class="table-responsive"><table class="table table-striped"><thead><tr><th>File</th><th>Download</th></tr></thead><tbody>
          <?php foreach ($backupFiles as $f): ?>
            <tr><td><?php echo htmlspecialchars($f); ?></td><td><a class="btn btn-sm btn-info" href="?download=<?php echo urlencode($f); ?>">Download</a></td></tr>
          <?php endforeach; ?>
        </table></div>
      <?php else: ?>
        <div>No backup files found.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

