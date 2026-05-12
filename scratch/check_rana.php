<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = \App\Models\User::where('nama', 'Rana Yoda Hanifah')->first();
if($u) {
    $arr = json_decode($u->face_embedding, true);
    $dim = is_array($arr) ? count($arr) : 0;
    echo "Name: {$u->nama}, Role: {$u->role}, Dim: {$dim}, Length: " . strlen($u->face_embedding) . ", Foto: " . $u->foto_base64 . "\n";
} else {
    echo "User not found\n";
}
