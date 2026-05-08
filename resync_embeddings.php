<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());
set_time_limit(0);

use App\Models\User;
use Symfony\Component\Process\Process;

$users = User::whereNotNull('foto_base64')->get();

echo "Memulai re-sync untuk " . count($users) . " user...\n";

$facenetCli = base_path('scripts/facenet_cli.py');
$pythonPath = 'C:\\Python313\\python.exe';
$sitePackages = 'C:\\Python313\\Lib\\site-packages';
$cmdPython = file_exists($pythonPath) ? $pythonPath : 'python';

foreach ($users as $user) {
    echo "Processing " . $user->nama . "... ";
    
    $imagePath = storage_path('app/public/users/' . $user->foto_base64);
    
    if (!file_exists($imagePath)) {
        echo "FAILED: File foto tidak ditemukan di $imagePath\n";
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
        'USERNAME' => 'Rana',
        'USER' => 'Rana',
        'SystemRoot' => 'C:\\Windows'
    ]);

    try {
        $process->run();
        
        if (!$process->isSuccessful()) {
            echo "FAILED: Python error: " . $process->getErrorOutput() . "\n";
            continue;
        }

        $output = json_decode($process->getOutput(), true);
        
        if (isset($output['success']) && $output['success'] && isset($output['data']['embedding'])) {
            $user->face_embedding = json_encode($output['data']['embedding']);
            $user->face_embedding_updated = now();
            $user->save();
            echo "SUCCESS (Normalized)\n";
        } else {
            echo "FAILED: No embedding generated.\n";
        }
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

echo "Semua proses selesai!\n";
