<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Exception;

trait ImageOptimizer
{
    /**
     * Optimize and save base64 image as a compressed JPEG.
     * 
     * @param string $base64Data
     * @param string $path Directory path within 'public' disk
     * @param string $filename Custom filename (optional)
     * @param int $maxWidth Max width for the image
     * @param int $quality JPEG quality (0-100)
     * @return string|null The saved filename or null on failure
     */
    public function optimizeAndSaveBase64(string $base64Data, string $path, string $filename = null, int $maxWidth = 300, int $quality = 60): ?string
    {
        try {
            // Remove base64 header if exists
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, etc
            } else {
                $type = 'jpg'; // assume jpg if no header
            }

            $imageData = base64_decode($base64Data);
            if (!$imageData) return null;

            // Load image using GD
            $srcImage = \imagecreatefromstring($imageData);
            if (!$srcImage) return null;

            // Get original dimensions
            $width = \imagesx($srcImage);
            $height = \imagesy($srcImage);

            // Calculate new dimensions (maintain aspect ratio)
            $newWidth = $width;
            $newHeight = $height;
            if ($width > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = floor($height * ($maxWidth / $width));
            }

            // Create new true color image
            $dstImage = \imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNGs if needed (but we'll convert to JPG for size)
            \imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Generate filename if not provided
            if (!$filename) {
                $filename = uniqid('img_') . '_' . time() . '.jpg';
            }

            // Ensure directory exists
            if (!Storage::disk('public')->exists($path)) {
                Storage::disk('public')->makeDirectory($path);
            }

            $fullPath = storage_path('app/public/' . $path . '/' . $filename);

            // Save as JPEG with compression
            \imagejpeg($dstImage, $fullPath, $quality);

            // Free memory
            \imagedestroy($srcImage);
            \imagedestroy($dstImage);

            return $filename;
        } catch (Exception $e) {
            \Log::error('Image Optimization Failed: ' . $e->getMessage());
            return null;
        }
    }
}
