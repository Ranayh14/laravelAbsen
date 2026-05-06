<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

$pdo = new PDO(
    "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_DATABASE'],
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if foto_masuk contains what we expect
$stmt = $pdo->query('SELECT id, jam_masuk_iso, foto_masuk, screenshot_masuk, ekspresi_masuk FROM attendance WHERE foto_masuk IS NOT NULL ORDER BY jam_masuk_iso DESC LIMIT 3');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "=== Data dengan foto_masuk ===\n";
foreach ($rows as $row) {
    $fotoType = substr($row['foto_masuk'], 0, 20);
    echo "ID: " . $row['id'] . "\n";
    echo "Date: " . $row['jam_masuk_iso'] . "\n";
    echo "Foto type: " . $fotoType . "\n";
    echo "Foto length: " . strlen($row['foto_masuk']) . "\n";
    echo "Ekspresi: " . $row['ekspresi_masuk'] . "\n";
    echo "---\n";
}

// Check recent attendance records
echo "\n=== Recent attendance records ===\n";
$stmt = $pdo->query('SELECT id, jam_masuk_iso, foto_masuk, screenshot_masuk, ekspresi_masuk FROM attendance ORDER BY jam_masuk_iso DESC LIMIT 5');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $hasFoto = !empty($row['foto_masuk']) ? 'YES' : 'NO';
    $hasScreenshot = !empty($row['screenshot_masuk']) ? 'YES' : 'NO';
    echo "ID: " . $row['id'] . ", Date: " . $row['jam_masuk_iso'] . ", Foto: $hasFoto, Screenshot: $hasScreenshot\n";
}
?>