<?php

/**
 * MANUAL DATABASE MIGRATION SCRIPT
 * Migrating from: d:\xampp\htdocs\Magang\Absen\database_backup\absen_db_backup.sql
 * Target: laravel_absen_db
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// 1. Load Laravel Bootstrap
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- START MANUAL DATABASE MIGRATION ---\n";

// Configuration
$sqlPath = 'd:\\xampp\\htdocs\\Magang\\Absen\\database_backup\\absen_db_backup.sql';

if (!file_exists($sqlPath)) {
    die("ERROR: Source SQL file not found at $sqlPath\n");
}

try {
    // 2. Backup Current Admins
    echo "Backing up current Admin accounts...\n";
    $preservedAdmins = DB::table('users')->where('role', 'admin')->get()->toArray();
    echo "Found " . count($preservedAdmins) . " admin(s).\n";

    // 3. Prepare Connection
    echo "Configuring DB session (No Strict Mode, No FK Checks)...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::statement("SET sql_mode = '';");
    DB::connection()->disableQueryLog();
    ini_set('memory_limit', '2048M');
    set_time_limit(0);

    // 4. Read and Clean SQL
    echo "Reading source SQL file (this may take a moment for 65MB)...\n";
    $sql = file_get_contents($sqlPath);
    
    echo "Cleaning SQL (Stripping Constraints)...\n";
    // Remove CONSTRAINT in CREATE TABLE
    $sql = preg_replace('/,\s*CONSTRAINT\s+[`"\'\w]+?\s+FOREIGN KEY\s+.*?\n/is', "\n", $sql);
    // Remove standalone FOREIGN KEY
    $sql = preg_replace('/,\s*FOREIGN KEY\s+.*?\n/is', "\n", $sql);
    // Remove ALTER TABLE ADD CONSTRAINT
    $sql = preg_replace('/ALTER TABLE\s+[`"\'\w]+?\s+ADD\s+CONSTRAINT\s+.*?;/is', '', $sql);

    // 5. Execute Migration
    echo "Executing SQL Dump... (Please wait)\n";
    DB::unprepared($sql);
    echo "SQL execution completed successfully.\n";

    // 6. Restore Admins
    echo "Restoring preserved Admin accounts...\n";
    foreach ($preservedAdmins as $admin) {
        $adminArray = (array)$admin;
        
        // Remove 'id' if you want to avoid clashes, but better check if ID or Email exists
        $exists = DB::table('users')->where('email', $admin->email)->first();
        if ($exists) {
            DB::table('users')->where('email', $admin->email)->update([
                'password_hash' => $admin->password_hash,
                'role' => 'admin'
            ]);
        } else {
            // Check ID clash
            $idClash = DB::table('users')->where('id', $admin->id)->exists();
            if ($idClash) {
                // If ID is taken, let database handle AUTO_INCREMENT but keep data
                unset($adminArray['id']);
            }
            DB::table('users')->insert($adminArray);
        }
    }
    echo "Admin restoration completed.\n";

    // 7. Cleanup
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "--- MIGRATION FINISHED SUCCESSFULLY ---\n";

} catch (\Exception $e) {
    echo "CRITICAL ERROR DURING MIGRATION:\n";
    echo $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
