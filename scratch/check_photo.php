<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$filename = 'face_2_1778148256.jpg';
$path = storage_path('app/public/users/' . $filename);
echo "Path: " . $path . "\n";
echo "Exists: " . (file_exists($path) ? "YES" : "NO") . "\n";
echo "Project Root: " . base_path() . "\n";
