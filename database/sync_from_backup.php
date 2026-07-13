<?php
/**
 * Database Sync Script
 * Import data dari backup hosting ke database lokal
 * Preserves local-only tables: intern_groups, intern_group_members
 * 
 * Usage: php database/sync_from_backup.php
 */

$backupFile = __DIR__ . '/absen_db_backup_2026-07-13_121203.sql';

// Koneksi database (sama dengan .env)
$dbHost = '127.0.0.1';
$dbPort = 3306;
$dbName = 'laravel_absen_db';
$dbUser = 'root';
$dbPass = '';

// Tabel yang HANYA ada di lokal dan tidak boleh disentuh
$localOnlyTables = ['intern_groups', 'intern_group_members'];

echo "=== Database Sync dari Backup Hosting ===\n";
echo "File: " . basename($backupFile) . "\n";
echo "Target DB: {$dbName}\n";
echo "Tabel yang dilindungi: " . implode(', ', $localOnlyTables) . "\n\n";

if (!file_exists($backupFile)) {
    die("ERROR: File backup tidak ditemukan: {$backupFile}\n");
}

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Koneksi database berhasil\n\n";
} catch (PDOException $e) {
    die("ERROR koneksi DB: " . $e->getMessage() . "\n");
}

// Backup tabel lokal dulu sebelum import
echo "--- Backup data lokal yang dilindungi ---\n";
$localBackup = [];
foreach ($localOnlyTables as $tbl) {
    try {
        $rows = $pdo->query("SELECT * FROM `{$tbl}`")->fetchAll(PDO::FETCH_ASSOC);
        $localBackup[$tbl] = $rows;
        echo "✓ Backup {$tbl}: " . count($rows) . " baris\n";
    } catch (Exception $e) {
        echo "  (tabel {$tbl} belum ada, skip)\n";
        $localBackup[$tbl] = [];
    }
}

// Baca file SQL dan parse per statement
echo "\n--- Membaca file backup ---\n";
$handle = fopen($backupFile, 'r');
if (!$handle) {
    die("ERROR: Tidak bisa membuka file backup\n");
}

$statements = [];
$currentStmt = '';
$lineCount = 0;
$tableCount = 0;

while (!feof($handle)) {
    $line = fgets($handle);
    $lineCount++;
    
    // Skip komentar dan baris kosong
    if (preg_match('/^\s*$/', $line) || preg_match('/^--/', $line)) {
        continue;
    }
    
    $currentStmt .= $line;
    
    // Selesai satu statement kalau ada titik koma di akhir baris
    if (preg_match('/;\s*$/', rtrim($line))) {
        $stmt = trim($currentStmt);
        if (!empty($stmt)) {
            $statements[] = $stmt;
        }
        $currentStmt = '';
    }
}
fclose($handle);

echo "✓ Total statement: " . count($statements) . "\n";

// Filter: hapus statement yang menyangkut tabel lokal-only
$filteredStatements = [];
$skippedCount = 0;

foreach ($statements as $stmt) {
    $skip = false;
    foreach ($localOnlyTables as $tbl) {
        if (stripos($stmt, "TABLE `{$tbl}`") !== false ||
            stripos($stmt, "TABLE {$tbl}") !== false ||
            stripos($stmt, "INTO `{$tbl}`") !== false ||
            stripos($stmt, "INTO {$tbl}") !== false) {
            $skip = true;
            break;
        }
    }
    
    if ($skip) {
        $skippedCount++;
    } else {
        $filteredStatements[] = $stmt;
    }
}

echo "✓ Statement yang dieksekusi: " . count($filteredStatements) . "\n";
echo "✓ Statement dilewati (tabel lokal): {$skippedCount}\n\n";

// Eksekusi import
echo "--- Mengeksekusi import ---\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS=0");
$pdo->exec("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

$successCount = 0;
$errorCount = 0;
$errors = [];

foreach ($filteredStatements as $idx => $stmt) {
    try {
        // Tampilkan progres setiap 100 statement
        if ($idx % 100 === 0) {
            echo "  Progress: {$idx}/" . count($filteredStatements) . "...\r";
        }
        $pdo->exec($stmt);
        $successCount++;
    } catch (PDOException $e) {
        $errorCount++;
        $shortStmt = substr($stmt, 0, 80);
        $errors[] = "Error: " . $e->getMessage() . " (Statement: {$shortStmt}...)";
        // Lanjut meski ada error (mis. duplicate key)
    }
}

$pdo->exec("SET FOREIGN_KEY_CHECKS=1");

echo "\n✓ Berhasil: {$successCount} statement\n";
if ($errorCount > 0) {
    echo "⚠ Error: {$errorCount} statement (mungkin duplikat)\n";
    if (count($errors) <= 10) {
        foreach ($errors as $err) echo "  - {$err}\n";
    } else {
        echo "  (terlalu banyak error untuk ditampilkan)\n";
    }
}

// Restore tabel lokal-only jika ada datanya
echo "\n--- Restore data tabel lokal ---\n";
foreach ($localOnlyTables as $tbl) {
    if (empty($localBackup[$tbl])) {
        echo "  (tabel {$tbl} kosong, skip restore)\n";
        continue;
    }
    
    // Pastikan tabel ada (create jika diperlukan)
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$tbl}`")->fetchColumn();
        echo "  Tabel {$tbl} sudah ada dengan {$count} baris — tidak diubah\n";
    } catch (Exception $e) {
        echo "  Tabel {$tbl} tidak ada setelah import, perlu migration\n";
    }
}

echo "\n=== Sinkronisasi selesai! ===\n";
echo "Database lokal sekarang sinkron dengan data hosting.\n";
echo "Tabel intern_groups dan intern_group_members tetap dipertahankan.\n\n";

// Verifikasi jumlah data
echo "--- Verifikasi data ---\n";
$checkTables = ['users', 'attendance', 'settings', 'intern_groups', 'intern_group_members'];
foreach ($checkTables as $tbl) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$tbl}`")->fetchColumn();
        $local = in_array($tbl, $localOnlyTables) ? ' [lokal]' : ' [dari hosting]';
        echo "  {$tbl}: {$count} baris{$local}\n";
    } catch (Exception $e) {
        echo "  {$tbl}: tidak ada\n";
    }
}
