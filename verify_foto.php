<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

$pdo = new PDO(
    "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_DATABASE'],
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD']
);

$stmt = $pdo->query('SELECT id, foto_masuk FROM attendance WHERE id IN (2451, 2447, 2445) ORDER BY id DESC');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo 'ID: ' . $r['id'] . ', Foto: ' . (empty($r['foto_masuk']) ? 'EMPTY' : substr($r['foto_masuk'], 0, 50)) . PHP_EOL;
}
?>