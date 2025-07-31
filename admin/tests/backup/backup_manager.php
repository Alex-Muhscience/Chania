<?php
/**
 * Backup and Recovery Manager
 * Handles automated backups and system recovery
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../classes/AdminLogger.php';

class BackupManager
{
    private $db;
    private $backupDir;
    private $logger;
    
    public function __construct()
    {
        $this->db = (new Database())->connect();
        $this->backupDir = __DIR__ . '/../../backups/';
        $this->logger = new AdminLogger($this->db);
        
        // Create backup directory if it doesn't exist
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Create a full system backup
     */
    public function createFullBackup($description = '')
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "full_backup_$timestamp";
        $backupPath = $this->backupDir . $backupName;
        
        // Create backup directory
        mkdir($backupPath, 0755, true);
        
        try {
            // 1. Database backup
            $this->createDatabaseBackup($backupPath . '/database.sql');
            
            // 2. Files backup
            $this->createFilesBackup($backupPath);
            
            // 3. Configuration backup
            $this->createConfigBackup($backupPath);
            
            // 4. Create backup manifest
            $this->createBackupManifest($backupPath, [
                'type' => 'full',
                'timestamp' => $timestamp,
                'description' => $description,
                'database_size' => filesize($backupPath . '/database.sql'),
                'total_files' => $this->countFiles($backupPath)
            ]);
            
            // 5. Create compressed archive
            $archivePath = $this->createArchive($backupPath, $backupName);
            
            // Clean up temporary directory
            $this->removeDirectory($backupPath);
            
            // Log backup creation
            $this->logger->log(
                $_SESSION['user_id'] ?? 0,
                'backup_created',
                "Full backup created: $backupName",
                ['backup_path' => $archivePath, 'description' => $description]
            );
            
            return [
                'success' => true,
                'backup_name' => $backupName,
                'backup_path' => $archivePath,
                'size' => filesize($archivePath)
            ];
            
        } catch (Exception $e) {
            // Clean up on error
            if (is_dir($backupPath)) {
                $this->removeDirectory($backupPath);
            }
            
            throw new Exception("Backup failed: " . $e->getMessage());
        }
    }
    
    /**
     * Create database-only backup
     */
    public function createDatabaseBackup($outputFile = null)
    {
        if (!$outputFile) {
            $timestamp = date('Y-m-d_H-i-s');
            $outputFile = $this->backupDir . "database_backup_$timestamp.sql";
        }
        
        // Get database configuration
        $host = DB_HOST;
        $database = DB_NAME;
        $username = DB_USER;
        $password = DB_PASS;
        
        // Use mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s --routines --triggers --single-transaction %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($outputFile)
        );
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception("Database backup failed with code: $returnCode");
        }
        
        if (!file_exists($outputFile) || filesize($outputFile) === 0) {
            throw new Exception("Database backup file was not created or is empty");
        }
        
        return $outputFile;
    }
    
    /**
     * Create files backup
     */
    public function createFilesBackup($backupPath)
    {
        $filesToBackup = [
            'uploads' => __DIR__ . '/../../uploads/',
            'admin_config' => __DIR__ . '/../includes/',
            'shared_config' => __DIR__ . '/../../config/',
            'media' => __DIR__ . '/../../assets/media/',
            'logs' => __DIR__ . '/../logs/'
        ];
        
        foreach ($filesToBackup as $name => $sourcePath) {
            if (is_dir($sourcePath)) {
                $destPath = $backupPath . '/files/' . $name;
                mkdir($destPath, 0755, true);
                $this->copyDirectory($sourcePath, $destPath);
            }
        }
    }
    
    /**
     * Create configuration backup
     */
    public function createConfigBackup($backupPath)
    {
        $configPath = $backupPath . '/config/';
        mkdir($configPath, 0755, true);
        
        // Copy important configuration files
        $configFiles = [
            __DIR__ . '/../config/config.php',
            __DIR__ . '/../../config/config.php',
            __DIR__ . '/../includes/header.php',
            __DIR__ . '/../includes/footer.php'
        ];
        
        foreach ($configFiles as $file) {
            if (file_exists($file)) {
                $filename = basename($file);
                $destPath = $configPath . $filename;
                copy($file, $destPath);
            }
        }
        
        // Export database schema
        $this->exportDatabaseSchema($configPath . 'schema.sql');
    }
    
    /**
     * List all available backups
     */
    public function listBackups()
    {
        $backups = [];
        $files = glob($this->backupDir . '*.zip');
        
        foreach ($files as $file) {
            $filename = basename($file, '.zip');
            $manifest = $this->readBackupManifest($file);
            
            $backups[] = [
                'name' => $filename,
                'path' => $file,
                'size' => filesize($file),
                'date' => filemtime($file),
                'manifest' => $manifest
            ];
        }
        
        // Sort by date (newest first)
        usort($backups, function($a, $b) {
            return $b['date'] - $a['date'];
        });
        
        return $backups;
    }
    
    /**
     * Restore from backup
     */
    public function restoreBackup($backupPath, $restoreOptions = [])
    {
        if (!file_exists($backupPath)) {
            throw new Exception("Backup file not found: $backupPath");
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $extractPath = $this->backupDir . "restore_temp_$timestamp/";
        
        try {
            // Extract backup archive
            $this->extractArchive($backupPath, $extractPath);
            
            // Read backup manifest
            $manifest = json_decode(file_get_contents($extractPath . 'manifest.json'), true);
            
            if (!$manifest) {
                throw new Exception("Invalid backup file - missing or corrupt manifest");
            }
            
            // Restore database if requested
            if ($restoreOptions['database'] ?? true) {
                $this->restoreDatabase($extractPath . 'database.sql');
            }
            
            // Restore files if requested
            if ($restoreOptions['files'] ?? true) {
                $this->restoreFiles($extractPath . 'files/');
            }
            
            // Restore configuration if requested
            if ($restoreOptions['config'] ?? false) {
                $this->restoreConfig($extractPath . 'config/');
            }
            
            // Clean up
            $this->removeDirectory($extractPath);
            
            // Log restore action
            $this->logger->log(
                $_SESSION['user_id'] ?? 0,
                'backup_restored',
                "Backup restored: " . basename($backupPath),
                ['backup_path' => $backupPath, 'options' => $restoreOptions]
            );
            
            return ['success' => true, 'restored_from' => $manifest];
            
        } catch (Exception $e) {
            // Clean up on error
            if (is_dir($extractPath)) {
                $this->removeDirectory($extractPath);
            }
            
            throw new Exception("Restore failed: " . $e->getMessage());
        }
    }
    
    /**
     * Delete old backups based on retention policy
     */
    public function cleanupOldBackups($retentionDays = 30, $maxBackups = 10)
    {
        $backups = $this->listBackups();
        $deletedCount = 0;
        $cutoffTime = time() - ($retentionDays * 24 * 60 * 60);
        
        // Keep at least the most recent backups
        $backupsToKeep = array_slice($backups, 0, $maxBackups);
        $backupsToCheck = array_slice($backups, $maxBackups);
        
        foreach ($backupsToCheck as $backup) {
            if ($backup['date'] < $cutoffTime) {
                if (unlink($backup['path'])) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * Get backup statistics
     */
    public function getBackupStats()
    {
        $backups = $this->listBackups();
        $totalSize = 0;
        $oldestBackup = null;
        $newestBackup = null;
        
        foreach ($backups as $backup) {
            $totalSize += $backup['size'];
            
            if (!$oldestBackup || $backup['date'] < $oldestBackup['date']) {
                $oldestBackup = $backup;
            }
            
            if (!$newestBackup || $backup['date'] > $newestBackup['date']) {
                $newestBackup = $backup;
            }
        }
        
        return [
            'total_backups' => count($backups),
            'total_size' => $totalSize,
            'oldest_backup' => $oldestBackup,
            'newest_backup' => $newestBackup,
            'backup_directory' => $this->backupDir
        ];
    }
    
    // Private helper methods
    
    private function createBackupManifest($backupPath, $data)
    {
        $manifest = [
            'version' => '1.0',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['username'] ?? 'system',
            'php_version' => PHP_VERSION,
            'mysql_version' => $this->db->getAttribute(PDO::ATTR_SERVER_VERSION),
            'backup_data' => $data
        ];
        
        file_put_contents($backupPath . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
    }
    
    private function readBackupManifest($backupPath)
    {
        $zip = new ZipArchive();
        if ($zip->open($backupPath) === TRUE) {
            $manifestContent = $zip->getFromName('manifest.json');
            $zip->close();
            return json_decode($manifestContent, true);
        }
        return null;
    }
    
    private function createArchive($sourcePath, $archiveName)
    {
        $archivePath = $this->backupDir . $archiveName . '.zip';
        $zip = new ZipArchive();
        
        if ($zip->open($archivePath, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("Cannot create archive: $archivePath");
        }
        
        $this->addDirectoryToZip($zip, $sourcePath, '');
        $zip->close();
        
        return $archivePath;
    }
    
    private function extractArchive($archivePath, $extractPath)
    {
        $zip = new ZipArchive();
        if ($zip->open($archivePath) !== TRUE) {
            throw new Exception("Cannot open archive: $archivePath");
        }
        
        if (!$zip->extractTo($extractPath)) {
            throw new Exception("Cannot extract archive to: $extractPath");
        }
        
        $zip->close();
    }
    
    private function addDirectoryToZip($zip, $sourcePath, $zipPath)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relativePath = $zipPath . substr($filePath, strlen($sourcePath));
            
            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    
    private function copyDirectory($source, $dest)
    {
        if (!is_dir($source)) return;
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $destPath = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                mkdir($destPath, 0755, true);
            } else {
                copy($item, $destPath);
            }
        }
    }
    
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) return;
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        
        rmdir($dir);
    }
    
    private function countFiles($directory)
    {
        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }
        
        return $count;
    }
    
    private function exportDatabaseSchema($outputFile)
    {
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s --no-data %s > %s',
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg(DB_NAME),
            escapeshellarg($outputFile)
        );
        
        exec($command);
    }
    
    private function restoreDatabase($sqlFile)
    {
        if (!file_exists($sqlFile)) {
            throw new Exception("Database backup file not found: $sqlFile");
        }
        
        $command = sprintf(
            'mysql --host=%s --user=%s --password=%s %s < %s',
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg(DB_NAME),
            escapeshellarg($sqlFile)
        );
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception("Database restore failed with code: $returnCode");
        }
    }
    
    private function restoreFiles($filesPath)
    {
        if (!is_dir($filesPath)) return;
        
        $restorePaths = [
            'uploads' => __DIR__ . '/../../uploads/',
            'media' => __DIR__ . '/../../assets/media/',
            'logs' => __DIR__ . '/../logs/'
        ];
        
        foreach ($restorePaths as $name => $destPath) {
            $sourcePath = $filesPath . $name . '/';
            if (is_dir($sourcePath)) {
                // Create backup of existing files before restore
                $backupPath = $destPath . '_backup_' . date('Y-m-d_H-i-s') . '/';
                if (is_dir($destPath)) {
                    rename($destPath, $backupPath);
                }
                
                // Restore files
                $this->copyDirectory($sourcePath, $destPath);
            }
        }
    }
    
    private function restoreConfig($configPath)
    {
        // This is dangerous and should only be done with extreme caution
        // For now, we'll just log what would be restored
        if (is_dir($configPath)) {
            $this->logger->log(
                $_SESSION['user_id'] ?? 0,
                'config_restore_skipped',
                'Configuration restore was skipped for security reasons',
                ['config_path' => $configPath]
            );
        }
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $action = $argv[1] ?? '';
    $backupManager = new BackupManager();
    
    switch ($action) {
        case 'create':
            echo "Creating full backup...\n";
            try {
                $result = $backupManager->createFullBackup($argv[2] ?? 'CLI backup');
                echo "Backup created successfully: {$result['backup_name']}\n";
                echo "Size: " . number_format($result['size'] / 1024 / 1024, 2) . " MB\n";
            } catch (Exception $e) {
                echo "Backup failed: " . $e->getMessage() . "\n";
                exit(1);
            }
            break;
            
        case 'list':
            echo "Available backups:\n";
            $backups = $backupManager->listBackups();
            foreach ($backups as $backup) {
                echo "- {$backup['name']} (" . date('Y-m-d H:i:s', $backup['date']) . ")\n";
                echo "  Size: " . number_format($backup['size'] / 1024 / 1024, 2) . " MB\n";
            }
            break;
            
        case 'cleanup':
            echo "Cleaning up old backups...\n";
            $deleted = $backupManager->cleanupOldBackups();
            echo "Deleted $deleted old backup(s)\n";
            break;
            
        case 'stats':
            $stats = $backupManager->getBackupStats();
            echo "Backup Statistics:\n";
            echo "Total backups: {$stats['total_backups']}\n";
            echo "Total size: " . number_format($stats['total_size'] / 1024 / 1024, 2) . " MB\n";
            if ($stats['newest_backup']) {
                echo "Newest backup: {$stats['newest_backup']['name']}\n";
            }
            break;
            
        default:
            echo "Usage: php backup_manager.php [create|list|cleanup|stats] [description]\n";
            break;
    }
}

?>
