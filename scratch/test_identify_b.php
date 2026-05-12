<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Symfony\Component\Process\Process;

// Ganti 'Dian Safitri' dengan nama pegawai B yang dimaksud user jika berbeda
$nameB = 'Dian Safitri'; 
$userB = User::where('nama', $nameB)->first();

if (!$userB) {
    echo "User $nameB not found\n";
    exit;
}

echo "Testing Identity for {$userB->nama} (ID: {$userB->id})\n";

$photoPath = storage_path('app/public/users/' . $userB->foto_base64);
$facenetCli = base_path('scripts/facenet_cli.py');
$pythonPath = 'C:\\Python313\\python.exe';
$sitePackages = 'C:\\Python313\\Lib\\site-packages';

$cmdPython = file_exists($pythonPath) ? $pythonPath : 'python';
$jsonArgs = json_encode([
    'action' => 'recognize_face',
    'image' => $photoPath,
    'threshold' => 0.6 // Coba naikkan sedikit
]);

$process = new Process([$cmdPython, $facenetCli, $jsonArgs]);
$process->setEnv([
    'PYTHONPATH' => $sitePackages . ';C:\\Users\\Rana\\AppData\\Roaming\\Python\\Python313\\site-packages;' . base_path('scripts') . ';' . base_path('scripts/facenet-master/src'),
    'PATH' => 'C:\\Python313\\;' . getenv('PATH'),
    'SystemRoot' => 'C:\\Windows'
]);

$process->run();
echo "Output: " . $process->getOutput() . "\n";
echo "Error: " . $process->getErrorOutput() . "\n";
