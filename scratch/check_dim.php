<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::whereNotNull('face_embedding')->first();
if ($user) {
    $e = json_decode($user->face_embedding);
    echo "Count: " . count($e) . "\n";
} else {
    echo "No user found\n";
}
