<?php

use App\Models\RetributionType;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cloudinary = app(CloudinaryService::class);
$iconPath = public_path('storage/icons');

if (!File::exists($iconPath)) {
    echo "Icon path does not exist: $iconPath\n";
    exit(1);
}

$files = File::files($iconPath);
$mapping = [];

foreach ($files as $file) {
    if ($file->getExtension() !== 'png') continue;
    
    echo "Uploading " . $file->getFilename() . "...\n";
    
    // Create a mock UploadedFile for the service
    $uploadedFile = new UploadedFile(
        $file->getRealPath(),
        $file->getFilename(),
        'image/png',
        null,
        true
    );
    
    try {
        $url = $cloudinary->upload($uploadedFile, 'retribusi/icons');
        $mapping[$file->getFilename()] = $url;
        echo "Uploaded: $url\n";
    } catch (\Exception $e) {
        echo "Failed to upload " . $file->getFilename() . ": " . $e->getMessage() . "\n";
    }
}

// Update Database
foreach ($mapping as $filename => $url) {
    $slug = str_replace('.png', '', $filename);
    
    // Map filename to DB records
    // Example: reklame.png -> 'Pajak Reklame'
    $types = RetributionType::all();
    foreach ($types as $type) {
        if (str_contains(strtolower($type->icon), $slug)) {
            $type->icon = $url;
            $type->save();
            echo "Updated DB for: " . $type->name . "\n";
        }
    }
}

echo "\nDone!\n";
print_r($mapping);
