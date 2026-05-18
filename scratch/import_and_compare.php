<?php
// PHP Script to compare local DB vs hosting backup

$localDbName = "laravel_absen_db";
$tempDbName = "absen_hosting_temp";
$backupFile = "d:/xampp/htdocs/Magang/LaravelAbsen/database/absen_db_backup_2026-05-18_160437.sql";

echo "Creating temp database...\n";
$pdo = new PDO("mysql:host=127.0.0.1", "root", "");
$pdo->exec("CREATE DATABASE IF NOT EXISTS `$tempDbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

echo "Importing backup file (this may take a few seconds as it is 41MB)...\n";
// Let's use mysql command line if available, otherwise read file (mysql command line is 50x faster)
$cmd = "d:\\xampp\\mysql\\bin\\mysql.exe -u root $tempDbName < \"$backupFile\"";
exec($cmd, $output, $returnVar);

if ($returnVar !== 0) {
    echo "CMD import failed with code $returnVar\n";
    exit(1);
}

echo "Databases imported. Comparing schemas...\n";

$localPdo = new PDO("mysql:host=127.0.0.1;dbname=$localDbName", "root", "");
$tempPdo = new PDO("mysql:host=127.0.0.1;dbname=$tempDbName", "root", "");

// Get local tables
$stmt = $localPdo->query("SHOW TABLES");
$localTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get temp (hosting) tables
$stmt = $tempPdo->query("SHOW TABLES");
$tempTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$missingTablesLocal = array_diff($localTables, $tempTables);
$missingTablesTemp = array_diff($tempTables, $localTables);

echo "\n--- MISSING TABLES IN HOSTING DATABASE ---\n";
print_r($missingTablesLocal);

echo "\n--- MISSING TABLES IN LOCAL DATABASE ---\n";
print_r($missingTablesTemp);

echo "\n--- DEEP COLUMN & TYPE COMPARISON ---\n";
foreach ($localTables as $table) {
    if (in_array($table, $tempTables)) {
        // Get local columns
        $stmt = $localPdo->query("SHOW COLUMNS FROM `$table`");
        $localCols = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            $localCols[$col['Field']] = $col;
        }

        // Get temp columns
        $stmt = $tempPdo->query("SHOW COLUMNS FROM `$table`");
        $tempCols = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            $tempCols[$col['Field']] = $col;
        }

        // Columns in local but not hosting
        $missingInHosting = array_diff(array_keys($localCols), array_keys($tempCols));
        if ($missingInHosting) {
            echo "Table `$table`: Columns missing in hosting DB: " . implode(', ', $missingInHosting) . "\n";
        }

        // Columns in hosting but not local
        $missingInLocal = array_diff(array_keys($tempCols), array_keys($localCols));
        if ($missingInLocal) {
            echo "Table `$table`: Columns missing in local DB: " . implode(', ', $missingInLocal) . "\n";
        }

        // Type mismatches for common columns
        foreach (array_intersect(array_keys($localCols), array_keys($tempCols)) as $colName) {
            $lCol = $localCols[$colName];
            $tCol = $tempCols[$colName];
            if ($lCol['Type'] !== $tCol['Type']) {
                echo "Table `$table` Column `$colName` type mismatch: Local is `{$lCol['Type']}`, Hosting is `{$tCol['Type']}`\n";
            }
        }
    }
}
echo "\nComparison finished!\n";
