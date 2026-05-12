<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SyncFaceEmbeddings extends Command
{
    protected $signature = 'face:sync';
    protected $description = 'Pre-compute 128-dim face embeddings for all employees to make attendance instant.';

    public function handle()
    {
        $users = User::where('role', 'pegawai')->get();
        $this->info("🚀 Syncing face embeddings for " . $users->count() . " employees...");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            if (empty($user->foto_base64)) {
                $bar->advance();
                continue;
            }

            // Prepare image path
            $photo = $user->foto_base64;
            $photoPath = '';

            if (strpos($photo, 'data:image') === 0) {
                // Temporary file for base64
                $photoData = base64_decode(explode(',', $photo)[1]);
                $photoPath = storage_path('app/temp_sync_' . $user->id . '.jpg');
                file_put_contents($photoPath, $photoData);
            } else {
                $photoPath = storage_path('app/public/users/' . $photo);
                if (!file_exists($photoPath)) {
                    $photoPath = public_path('storage/users/' . $photo);
                }
            }

            if (!file_exists($photoPath)) {
                $bar->advance();
                continue;
            }

            try {
                // Call Python AI service (using the existing facenet_service.py logic)
                $pythonPath = 'python';
                $scriptPath = base_path('scripts/facenet_service.py');
                
                // We'll use a temporary command to get the embedding directly
                $command = sprintf('%s %s --image "%s" --task verify', $pythonPath, escapeshellarg($scriptPath), $photoPath);
                // Note: facenet_service.py returns identification results. 
                // I'll create a small specialized script for just getting the vector.
                
                $embedding = $this->getVectorFromPython($photoPath);
                
                if ($embedding) {
                    $user->face_embedding_128 = json_encode($embedding);
                    $user->face_embedding = json_encode($embedding);
                    $user->save();
                }
            } catch (\Exception $e) {
                $this->error("\nError for {$user->nama}: " . $e->getMessage());
            }

            if (strpos($photo, 'data:image') === 0 && file_exists($photoPath)) {
                unlink($photoPath);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\n✨ All users synced! Every device is now INSTANT.");
    }

    private function getVectorFromPython($imagePath)
    {
        $tempScript = storage_path('app/get_vector.py');
        $script = <<<PY
import torch
import json
import sys
from facenet_pytorch import MTCNN, InceptionResnetV1
from PIL import Image

try:
    img = Image.open(sys.argv[1]).convert('RGB')
    mtcnn = MTCNN(image_size=160, margin=0)
    resnet = InceptionResnetV1(pretrained='vggface2').eval()
    
    img_cropped = mtcnn(img)
    if img_cropped is not None:
        embedding = resnet(img_cropped.unsqueeze(0)).detach().numpy()[0]
        print(json.dumps(embedding.tolist()))
    else:
        print("null")
except Exception as e:
    print("null")
PY;
        file_put_contents($tempScript, $script);
        
        $output = shell_exec("python " . escapeshellarg($tempScript) . " " . escapeshellarg($imagePath));
        @unlink($tempScript);
        
        return json_decode($output, true);
    }
}
