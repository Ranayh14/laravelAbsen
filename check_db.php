<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "--- Column Types ---\n";
foreach(['foto_masuk', 'foto_pulang', 'screenshot_masuk', 'screenshot_pulang'] as $col) {
    $res = DB::select("SHOW COLUMNS FROM attendance LIKE '$col'");
    if ($res) {
        echo "$col: " . $res[0]->Type . "\n";
    }
}

echo "\n--- Data Lengths (Last 20) ---\n";
$records = DB::table('attendance')->orderBy('created_at', 'desc')->limit(20)->get();
foreach($records as $r) {
    echo "ID: {$r->id} | Created: {$r->created_at} | foto_len: " . strlen($r->foto_masuk) . " | screen_len: " . strlen($r->screenshot_masuk) . "\n";
    if (strlen($r->foto_masuk) > 0 && strlen($r->foto_masuk) < 500) {
        echo "  foto_masuk sample: " . substr($r->foto_masuk, 0, 50) . "...\n";
    }
    if (strlen($r->screenshot_masuk) > 0 && strlen($r->screenshot_masuk) < 500) {
        echo "  screenshot_masuk sample: " . substr($r->screenshot_masuk, 0, 50) . "...\n";
    }
}
