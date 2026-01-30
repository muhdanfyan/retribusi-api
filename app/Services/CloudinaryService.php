<?php

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    /**
     * Upload a file to Cloudinary
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string Cloudinary URL
     */
    public function upload(UploadedFile $file, string $folder = 'retribusi'): string
    {
        /** @var \Cloudinary\Cloudinary $cloudinary */
        $cloudinary = app(\Cloudinary\Cloudinary::class);
        
        $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder
        ]);
        
        return $result['secure_url'];
    }

    /**
     * Delete a file from Cloudinary by URL
     *
     * @param string|null $url
     * @return bool
     */
    public function delete(?string $url): bool
    {
        if (!$url) return false;

        try {
            /** @var \Cloudinary\Cloudinary $cloudinary */
            $cloudinary = app(\Cloudinary\Cloudinary::class);

            // Extract public ID from URL
            $path = parse_url($url, PHP_URL_PATH);
            $segments = explode('/', $path);
            
            // Public ID is everything after 'upload/v...'
            $foundUpload = false;
            $publicIdSegments = [];
            foreach ($segments as $segment) {
                if ($foundUpload) {
                    $publicIdSegments[] = $segment;
                }
                if (strpos($segment, 'upload') === 0) {
                    $foundUpload = true;
                    // skip version segment if it follows
                }
            }

            // Remove version segment (v1234567) if present
            if (!empty($publicIdSegments) && str_starts_with($publicIdSegments[0], 'v') && is_numeric(substr($publicIdSegments[0], 1))) {
                array_shift($publicIdSegments);
            }

            $fullPublicId = implode('/', $publicIdSegments);
            $fullPublicId = pathinfo($fullPublicId, PATHINFO_DIRNAME) . '/' . pathinfo($fullPublicId, PATHINFO_FILENAME);
            $fullPublicId = ltrim($fullPublicId, './');

            $cloudinary->adminApi()->deleteAssets([$fullPublicId]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
