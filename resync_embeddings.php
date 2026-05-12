<?php
/**
 * Face Embedding Resync Script
 * This script regenerates all 512-dim face embeddings using the current Python backend.
 * This ensures that the database is perfectly synced with the FaceNet PyTorch model.
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Symfony\Component\Process\Process;

$users = User::whereNotNull('foto_base64')->get();
$count = count($users);
echo "Found $count users with profile photos. Starting resync...\n";

$pythonPath = 'C:\\Python313\\python.exe';
$cmdPython = file_exists($pythonPath) ? $pythonPath : 'python';
$facenetCli = base_path('scripts/facenet_cli.py');
$sitePackages = 'C:\\Python313\\Lib\\site-packages';

foreach ($users as $index => $user) {
    $num = $index + 1;
    echo "[$num/$count] Processing {$user->nama}... ";
    
    $imagePath = storage_path('app/public/users/' . $user->foto_base64);
    if (!file_exists($imagePath)) {
        echo "Error: File not found at $imagePath\n";
        continue;
    }
    
    $jsonArgs = json_encode([
        'action' => 'generate_embedding',
        'image' => $imagePath
    ]);
    
    $process = new Process([$cmdPython, $facenetCli, $jsonArgs]);
    $process->setEnv([
        'PYTHONPATH' => $sitePackages . ';C:\\Users\\Rana\\AppData\\Roaming\\Python\\Python313\\site-packages;' . base_path('scripts') . ';' . base_path('scripts/facenet-master/src'),
        'PATH' => 'C:\\Python313\\;' . getenv('PATH'),
        'SystemRoot' => 'C:\\Windows'
    ]);
    
    $process->run();
    
    if (!$process->isSuccessful()) {
        echo "Error: Python process failed. " . $process->getErrorOutput() . "\n";
        continue;
    }
    
    $output = json_decode($process->getOutput(), true);
    if (isset($output['success']) && $output['success']) {
        $embedding = $output['data']['embedding'];
        $user->face_embedding = json_encode($embedding);
        $user->face_embedding_updated = now();
        $user->save();
        echo "Success (Dim: " . count($embedding) . ")\n";
    } else {
        echo "Error: " . ($output['error'] ?? 'Unknown error') . "\n";
    }
}

echo "Resync complete!\n";
