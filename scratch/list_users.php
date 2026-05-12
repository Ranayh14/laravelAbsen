<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::whereIn('id', [1, 2, 3, 4, 5])->get();
foreach($users as $u) {
    echo "ID: {$u->id}, Name: {$u->nama}, Role: {$u->role}, Foto: {$u->foto_base64}\n";
}
