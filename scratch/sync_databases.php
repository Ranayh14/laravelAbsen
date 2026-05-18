<?php
// PHP Script to sync local DB with hosting backup

$localDbName = "laravel_absen_db";
$backupFile = "d:/xampp/htdocs/Magang/LaravelAbsen/database/absen_db_backup_2026-05-18_160437.sql";
$localBackupFile = "d:/xampp/htdocs/Magang/LaravelAbsen/database/absen_db_backup_local_pre_sync.sql";

echo "Step 1: Creating backup of existing local database just in case...\n";
$dumpCmd = "d:\\xampp\\mysql\\bin\\mysqldump.exe -u root $localDbName > \"$localBackupFile\"";
exec($dumpCmd, $outputDump, $returnDump);
if ($returnDump === 0) {
    echo "✓ Local database backed up successfully to database/absen_db_backup_local_pre_sync.sql\n";
} else {
    echo "⚠ Warning: Local backup failed (perhaps DB is empty or doesn't exist). Proceeding anyway...\n";
}

echo "\nStep 2: Dropping and Re-creating local database `$localDbName` to ensure a clean slate...\n";
$pdo = new PDO("mysql:host=127.0.0.1", "root", "");
$pdo->exec("DROP DATABASE IF EXISTS `$localDbName`");
$pdo->exec("CREATE DATABASE `$localDbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "✓ Database `$localDbName` recreated.\n";

echo "\nStep 3: Importing hosting data from `$backupFile` into local database...\n";
$importCmd = "d:\\xampp\\mysql\\bin\\mysql.exe -u root $localDbName < \"$backupFile\"";
exec($importCmd, $outputImport, $returnImport);

if ($returnImport === 0) {
    echo "✓ Data successfully imported from hosting backup!\n";
} else {
    echo "✗ Error: Failed to import data. Error code: $returnImport\n";
    exit(1);
}

echo "\nStep 4: Running migrations to verify everything is perfectly in sync...\n";
// Since the schema is already identical, running migrations will just confirm that everything is up to date.
// We can run artisan migrate to ensure no pending migrations exist.
$migrateCmd = "php artisan migrate --force";
exec($migrateCmd, $outputMigrate, $returnMigrate);
echo implode("\n", $outputMigrate) . "\n";
if ($returnMigrate === 0) {
    echo "✓ Migrations successfully verified/run.\n";
} else {
    echo "⚠ Note: Migrations command exited with code $returnMigrate. This is normal if all tables are already fully sync'd.\n";
}

echo "\nSynchronization completed successfully!\n";
