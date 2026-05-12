<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::whereNotNull('face_embedding')->get();
foreach($users as $u) {
    $arr = json_decode($u->face_embedding, true);
    $dim = is_array($arr) ? count($arr) : 0;
    echo "User: {$u->nama}, Length: " . strlen($u->face_embedding) . ", Dim: {$dim}\n";
}
