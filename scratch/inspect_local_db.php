<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=laravel_absen_db", "root", "");

// Get all tables
$tablesStmt = $pdo->query("SHOW TABLES");
$tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

$schema = [];
foreach ($tables as $table) {
    $columnsStmt = $pdo->query("SHOW COLUMNS FROM `$table`");
    $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
    $schema[$table] = array_map(function($c) {
        return $c['Field'];
    }, $columns);
}

echo json_encode($schema, JSON_PRETTY_PRINT);
