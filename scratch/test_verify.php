<?php
/**
 * Diagnostic: Test actual distance between two different users
 * This will compute the real distance the Python backend calculates.
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Symfony\Component\Process\Process;

$rana = User::find(2);
$dini = User::where('nama', 'like', '%Dini%')->first();

echo "Testing: Dini's photo vs Rana's stored embedding\n";
echo "Rana ID: {$rana->id}, Foto: {$rana->foto_base64}\n";
echo "Dini found: " . ($dini ? $dini->nama : 'NOT FOUND') . "\n\n";

if (!$dini) {
    echo "Dini not found in database\n";
    exit;
}

$diniPhotoPath = storage_path('app/public/users/' . $dini->foto_base64);
if (!file_exists($diniPhotoPath)) {
    echo "Dini's photo not found: $diniPhotoPath\n";
    exit;
}

$pythonPath = 'C:\\Python313\\python.exe';
$cmdPython = file_exists($pythonPath) ? $pythonPath : 'python';
$facenetCli = base_path('scripts/facenet_cli.py');
$sitePackages = 'C:\\Python313\\Lib\\site-packages';

// Test 1: verify_face action (the fixed method)
$jsonArgs = json_encode([
    'action'    => 'verify_face',
    'image'     => $diniPhotoPath,
    'user_id'   => $rana->id,
    'threshold' => 0.5
]);

$process = new Process([$cmdPython, $facenetCli, $jsonArgs]);
$process->setEnv([
    'PYTHONPATH' => $sitePackages . ';C:\\Users\\Rana\\AppData\\Roaming\\Python\\Python313\\site-packages;' . base_path('scripts') . ';' . base_path('scripts/facenet-master/src'),
    'PATH' => 'C:\\Python313\\;' . getenv('PATH'),
    'SystemRoot' => 'C:\\Windows'
]);
$process->setTimeout(120);
$process->run();

echo "=== RESULT (Dini photo vs Rana ID) ===\n";
echo "STDOUT: " . $process->getOutput() . "\n";
if ($process->getErrorOutput()) {
    echo "STDERR (last 2000 chars): " . substr($process->getErrorOutput(), -2000) . "\n";
}
