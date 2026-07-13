<?php
/**
 * Test script untuk verifikasi logika KPI Archive & Period Start
 * Akses: http://localhost/Magang/LaravelAbsen/public/test_kpi_fix.php
 */
$pdo = new PDO('mysql:host=127.0.0.1;dbname=laravel_absen_db;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

echo "<pre style='font-family:monospace;font-size:13px;background:#1a1a2e;color:#eee;padding:20px'>";
echo "=== TEST KPI FIX: Archive + Period Start Override ===\n\n";

// 1. Cek kelompok magang
$groups = $pdo->query("SELECT ig.*, (SELECT COUNT(*) FROM intern_group_members WHERE group_id=ig.id) as cnt FROM intern_groups ig ORDER BY ig.id")->fetchAll();
echo "--- Kelompok Magang ---\n";
foreach ($groups as $g) {
    $status = $g['is_archived'] ? '🔴 ARCHIVED' : '🟢 AKTIF';
    echo "$status | {$g['nama']} | mulai: {$g['tanggal_mulai']} | selesai: {$g['tanggal_selesai']} | {$g['cnt']} anggota\n";
    
    $members = $pdo->prepare("SELECT u.id, u.nama, DATE(u.created_at) as reg_date FROM users u JOIN intern_group_members igm ON igm.user_id=u.id WHERE igm.group_id=:gid ORDER BY u.nama");
    $members->execute([':gid' => $g['id']]);
    foreach ($members->fetchAll() as $m) {
        $startKPI = max($g['tanggal_mulai'], date('Y-m-d', strtotime($m['reg_date'])));
        echo "   - {$m['nama']} (registrasi: {$m['reg_date']}) → KPI start: " . $g['tanggal_mulai'] . "\n";
        
        // Cek apakah ada attendance sebelum tanggal_mulai kelompok
        $earlyAtt = $pdo->prepare("SELECT DATE(jam_masuk_iso) as tgl FROM attendance WHERE user_id=:uid AND DATE(jam_masuk_iso) < :mulai ORDER BY jam_masuk_iso LIMIT 5");
        $earlyAtt->execute([':uid' => $m['id'], ':mulai' => $g['tanggal_mulai']]);
        $earlyRows = $earlyAtt->fetchAll();
        if (!empty($earlyRows)) {
            echo "     ⚠️  Ada attendance SEBELUM tanggal_mulai: " . implode(', ', array_column($earlyRows, 'tgl')) . "\n";
            echo "     ✅  Dengan fix ini, tanggal tersebut TIDAK akan dihitung sebagai alpha\n";
        }
    }
}

// 2. Simulasikan logika archive
echo "\n--- Simulasi Logika Archive ---\n";
$groupRows = $pdo->query("
    SELECT igm.user_id, ig.id as group_id, ig.is_archived, ig.tanggal_mulai, ig.tanggal_selesai
    FROM intern_group_members igm
    JOIN intern_groups ig ON ig.id = igm.group_id
")->fetchAll();

$archivedUserIds    = [];
$userHasActiveGroup = [];
$userPeriodStartOverride = [];

foreach ($groupRows as $gr) {
    $uid = (int)$gr['user_id'];
    if (!$gr['is_archived']) {
        $userHasActiveGroup[$uid] = true;
        $grpStart = $gr['tanggal_mulai'];
        if (!isset($userPeriodStartOverride[$uid]) || $grpStart < $userPeriodStartOverride[$uid]) {
            $userPeriodStartOverride[$uid] = $grpStart;
        }
    } else {
        if (!isset($archivedUserIds[$uid])) {
            $archivedUserIds[$uid] = true;
        }
    }
}
// Hapus dari archived jika punya kelompok aktif
foreach (array_keys($userHasActiveGroup) as $uid) {
    unset($archivedUserIds[$uid]);
}

echo "Users di-skip dari KPI (archived, tidak ada kelompok aktif): ";
if (empty($archivedUserIds)) {
    echo "Tidak ada\n";
} else {
    $names = $pdo->query("SELECT id, nama FROM users WHERE id IN (" . implode(',', array_keys($archivedUserIds)) . ")")->fetchAll();
    echo implode(', ', array_column($names, 'nama')) . "\n";
}

echo "\nPer-user effective KPI start (dari tanggal_mulai kelompok):\n";
foreach ($userPeriodStartOverride as $uid => $start) {
    $name = $pdo->prepare("SELECT nama, DATE(created_at) as reg FROM users WHERE id=:id");
    $name->execute([':id' => $uid]);
    $u = $name->fetch();
    echo "  {$u['nama']}: KPI mulai dari $start (bukan registrasi: {$u['reg']})\n";
}

echo "\n=== Test selesai ===\n";
echo "</pre>";
