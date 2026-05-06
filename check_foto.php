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

$stmt = $pdo->query('SELECT id, jam_masuk_iso, foto_masuk, ekspresi_masuk FROM attendance WHERE foto_masuk IS NOT NULL ORDER BY jam_masuk_iso DESC LIMIT 5');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    echo 'ID: ' . $row['id'] . ', Date: ' . $row['jam_masuk_iso'] . ', Foto: ' . substr($row['foto_masuk'], 0, 50) . '..., Ekspresi: ' . $row['ekspresi_masuk'] . PHP_EOL;
}
?>