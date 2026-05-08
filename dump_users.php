<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::whereNotNull('foto_base64')->first();
if ($user) {
    echo "ID: " . $user->id . "\n";
    echo "Nama: " . $user->nama . "\n";
    echo "Foto: " . $user->foto_base64 . "\n";
} else {
    echo "Tidak ada user dengan foto.\n";
}
