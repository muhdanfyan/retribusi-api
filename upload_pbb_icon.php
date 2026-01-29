<?php

use App\Services\CloudinaryService;
use Illuminate\Support\Facades\File;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cloudinary = app(CloudinaryService::class);
$filePath = '/Users/pondokit/.gemini/antigravity/brain/849f30b2-f6e4-4642-b00f-f1f897da9048/pbb_icon_1769705570029.png';

try {
    if (!file_exists($filePath)) {
        die("File not found: $filePath\n");
    }

    // Create a temporary uploaded file object for the service
    $file = new \Illuminate\Http\UploadedFile($filePath, 'pbb_icon.png', 'image/png', null, true);
    
    $url = $cloudinary->upload($file, 'retribusi/mobile/icons');
    echo "UPLOAD_SUCCESS: $url\n";
} catch (\Exception $e) {
    echo "UPLOAD_ERROR: " . $e->getMessage() . "\n";
}
