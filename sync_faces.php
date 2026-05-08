<?php
// Sync script to generate face embeddings from existing profile pictures
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
echo "App loaded.\n";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
echo "Kernel loaded.\n";
$kernel->bootstrap();
echo "Bootstrap finished.\n";

use App\Models\User;
use Symfony\Component\Process\Process;

echo "--- Face Recognition Sync System ---\n";

$users = User::whereNotNull('foto_base64')
             ->whereNull('face_embedding')
             ->get();

echo "Ditemukan " . $users->count() . " pegawai yang perlu diproses.\n\n";

$pythonPath = 'C:\\Python313\\python.exe';
$facenetCli = base_path('scripts/facenet_cli.py');
$sitePackages = 'C:\\Users\\Rana\\AppData\\Roaming\\Python\\Python313\\site-packages';

foreach ($users as $index => $user) {
    echo "[" . ($index + 1) . "/" . $users->count() . "] Memproses: " . $user->nama . "...\n";
    
    // Path ke foto profil (Ditemukan di lokasi unik ini)
    $imagePath = storage_path('app/private/public/users/' . $user->foto_base64);
    
    if (!file_exists($imagePath)) {
        echo "  - ERROR: File foto tidak ditemukan di: $imagePath\n";
        continue;
    }

    // Format JSON untuk CLI (Embedding)
    $jsonArgs = json_encode([
        'action' => 'generate_embedding',
        'image' => $imagePath
    ]);
    
    $process = new Process([$pythonPath, $facenetCli, $jsonArgs]);
    
    // Gunakan environment variables yang sudah kita temukan tadi
    $process->setEnv([
        'PYTHONPATH' => 'C:\\Python313\\Lib\\site-packages;' . $sitePackages . ';' . base_path('scripts') . ';' . base_path('scripts/facenet-master/src'),
        'PATH' => 'C:\\Python313\\;' . getenv('PATH'),
        'SystemRoot' => 'C:\\Windows',
        'SystemDrive' => 'C:',
        'USERPROFILE' => 'C:\\Users\\Rana',
        'HOME' => 'C:\\Users\\Rana',
        'APPDATA' => 'C:\\Users\\Rana\\AppData\\Roaming'
    ]);

    $process->run();

    if ($process->isSuccessful()) {
        $output = json_decode($process->getOutput(), true);
        if (isset($output['success']) && $output['success'] && isset($output['data']['embedding'])) {
            $user->face_embedding = json_encode($output['data']['embedding']);
            $user->face_embedding_updated = now();
            $user->save();
            echo "  - BERHASIL: Data wajah disimpan.\n";
        } else {
            echo "  - GAGAL: Python tidak bisa mendeteksi wajah di foto ini.\n";
        }
    } else {
        echo "  - ERROR SISTEM: " . $process->getErrorOutput() . "\n";
    }
}

echo "\n--- Selesai ---\n";
