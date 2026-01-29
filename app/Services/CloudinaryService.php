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
        $result = Cloudinary::upload($file->getRealPath(), [
            'folder' => $folder
        ]);
        
        return $result->getSecurePath();
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
            // Extract public ID from URL
            // Example: https://res.cloudinary.com/cloud_name/image/upload/v12345/folder/id.jpg
            $path = parse_url($url, PHP_URL_PATH);
            $segments = explode('/', $path);
            
            // The public ID is typically the last segments after 'upload/v...'
            // This is a simplified version, cloudinary-laravel has smarter ways but this is robust for basic use
            $idWithExtension = end($segments);
            $publicId = pathinfo($idWithExtension, PATHINFO_FILENAME);
            
            // If there are folders, we need those too
            $folderSegments = [];
            $foundUpload = false;
            foreach ($segments as $segment) {
                if ($foundUpload) {
                    $folderSegments[] = $segment;
                }
                if (strpos($segment, 'upload') === 0 || preg_match('/^v\d+$/', $segment)) {
                    // skip 'upload' and version segment
                    if (strpos($segment, 'upload') === 0) $foundUpload = true;
                    continue; 
                }
            }
            
            // public_id includes the folder structure
            $fullPublicId = implode('/', array_slice($folderSegments, 1)); // skip version if it was there
            $fullPublicId = pathinfo($fullPublicId, PATHINFO_FILENAME);

            return Cloudinary::destroy($fullPublicId);
        } catch (\Exception $e) {
            return false;
        }
    }
}
