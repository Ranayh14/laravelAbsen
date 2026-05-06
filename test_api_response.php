<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

$pdo = new PDO(
    "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_DATABASE'],
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD']
);

// Simulate get_attendance API response (admin view)
$sql = "SELECT a.id, a.user_id, a.jam_masuk, a.jam_masuk_iso, a.ekspresi_masuk, a.foto_masuk, a.screenshot_masuk, a.landmark_masuk,
    a.jam_pulang, a.jam_pulang_iso, a.ekspresi_pulang, a.foto_pulang, a.screenshot_pulang, a.landmark_pulang,
    a.status, a.ket, a.alasan_wfa, a.alasan_izin_sakit, a.daily_report_id, a.created_at,
    u.nim, u.nama, u.startup,
    IF((a.foto_masuk IS NOT NULL AND a.foto_masuk != '') OR (a.screenshot_masuk IS NOT NULL AND a.screenshot_masuk != '') OR (a.landmark_masuk IS NOT NULL AND a.landmark_masuk != ''), 1, 0) as has_sm,
    IF((a.foto_pulang IS NOT NULL AND a.foto_pulang != '') OR (a.screenshot_pulang IS NOT NULL AND a.screenshot_pulang != '') OR (a.landmark_pulang IS NOT NULL AND a.landmark_pulang != ''), 1, 0) as has_sp
    FROM attendance a 
    JOIN users u ON u.id=a.user_id 
    WHERE a.id IN (2451, 2447, 2445)
    ORDER BY a.jam_masuk_iso DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check what fields are in response
foreach ($rows as $row) {
    echo "=== ID: {$row['id']} ===\n";
    echo "Foto Masuk (length): " . strlen($row['foto_masuk']) . "\n";
    echo "Foto Masuk (first 50): " . substr($row['foto_masuk'], 0, 50) . "\n";
    echo "Has SM: {$row['has_sm']}\n";
    echo "Ekspresi Masuk: {$row['ekspresi_masuk']}\n";
    echo "Jam Masuk ISO: {$row['jam_masuk_iso']}\n";
    echo "\n";
}
?>