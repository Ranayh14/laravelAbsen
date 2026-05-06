<?php

/**
 * Backup Helper Functions
 */

if (!function_exists('createDatabaseBackup')) {
    function createDatabaseBackup() {
        global $pdo; // Use global PDO from core.php if available

        // Increase memory limit for large DB dumps
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $backupDir = __DIR__ . '/database_backup';
        
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $filename = 'absen_db_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = $backupDir . '/' . $filename;

        // Get database configuration
        $host = env('DB_HOST', '127.0.0.1');
        $user = env('DB_USERNAME', 'root');
        $pass = env('DB_PASSWORD', '');
        $name = env('DB_DATABASE', 'laravel');

        // Try to find mysqldump
        $mysqldump = 'mysqldump';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Check common XAMPP paths
            $xamppPaths = [
                'C:\xampp\mysql\bin\mysqldump.exe',
                'D:\xampp\mysql\bin\mysqldump.exe',
                'E:\xampp\mysql\bin\mysqldump.exe'
            ];
            foreach ($xamppPaths as $path) {
                if (file_exists($path)) {
                    $mysqldump = '"' . $path . '"';
                    break;
                }
            }
        }

        // Command for mysqldump
        $command = "{$mysqldump} --user={$user} " . ($pass ? "--password={$pass} " : "") . "--host={$host} {$name} > \"{$filePath}\" 2>&1";
        
        $output = [];
        $returnVar = -1;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            // Fallback: If mysqldump fails, try PHP-based dump
            if (isset($pdo)) {
                $phpBackup = createDatabaseBackupPHP($pdo);
                if ($phpBackup['ok']) {
                    file_put_contents($filePath, $phpBackup['sql_content']);
                    return [
                        'ok' => true,
                        'success' => true,
                        'message' => 'Backup berhasil dibuat (via PHP Fallback)',
                        'filename' => $filename,
                        'size' => filesize($filePath),
                        'path' => $filePath
                    ];
                }
            }
            
            return [
                'ok' => false,
                'success' => false,
                'message' => 'Gagal membuat backup. Pastikan mysqldump tersedia atau konfigurasi database benar.',
                'output' => implode("\n", $output)
            ];
        }

        return [
            'ok' => true,
            'success' => true,
            'message' => 'Backup berhasil dibuat: ' . $filename,
            'filename' => $filename,
            'size' => file_exists($filePath) ? filesize($filePath) : 0,
            'path' => $filePath
        ];
    }
}

if (!function_exists('getBackupInfo')) {
    function getBackupInfo() {
        $backupDir = __DIR__ . '/database_backup';
        $files = [];
        
        if (file_exists($backupDir)) {
            $scan = scandir($backupDir);
            foreach ($scan as $file) {
                if ($file === '.' || $file === '..') continue;
                $filePath = $backupDir . '/' . $file;
                $files[] = [
                    'name' => $file,
                    'size' => filesize($filePath),
                    'date' => date('Y-m-d H:i:s', filemtime($filePath))
                ];
            }
        }
        
        // Sort by date desc
        usort($files, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        return [
            'count' => count($files),
            'files' => $files,
            'last_backup' => count($files) > 0 ? $files[0]['date'] : 'Belum pernah'
        ];
    }
}

if (!function_exists('createDatabaseBackupPHP')) {
    function createDatabaseBackupPHP($pdo) {
        // Increase memory limit for large DB dumps
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        try {
            $tables = [];
            $result = $pdo->query("SHOW TABLES");
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $sql = "-- Database Backup (PHP Fallback)\n";
            $sql .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                // Structure
                $res = $pdo->query("SHOW CREATE TABLE `{$table}`");
                $row = $res->fetch(PDO::FETCH_NUM);
                $sql .= "\n\nDROP TABLE IF EXISTS `{$table}`;\n" . $row[1] . ";\n\n";

                // Data
                $res = $pdo->query("SELECT * FROM `{$table}`");
                while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                    $keys = array_keys($row);
                    $escaped_keys = array_map(function($k) { return "`$k`"; }, $keys);
                    $values = array_values($row);
                    $escaped_values = array_map(function($v) use ($pdo) {
                        if ($v === null) return 'NULL';
                        return $pdo->quote($v);
                    }, $values);
                    
                    $sql .= "INSERT INTO `{$table}` (" . implode(", ", $escaped_keys) . ") VALUES (" . implode(", ", $escaped_values) . ");\n";
                }
            }

            $sql .= "\nSET FOREIGN_KEY_CHECKS=1;\n";

            return [
                'ok' => true,
                'success' => true,
                'sql_content' => $sql,
                'message' => 'Backup berhasil dibuat (PHP Fallback)'
            ];
        } catch (Exception $e) {
            return [
                'ok' => false,
                'success' => false,
                'message' => 'Gagal membuat backup via PHP: ' . $e->getMessage()
            ];
        }
    }
}
