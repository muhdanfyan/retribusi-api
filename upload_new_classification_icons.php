<?php

use App\Models\RetributionClassification;
use App\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cloudinary = app(CloudinaryService::class);

$newIcons = [
    'electricity' => '/Users/pondokit/.gemini/antigravity/brain/3b338178-13aa-4d3f-bbbf-4865819c59a5/icon_electricity_isometric_1769971838585.png',
    'motorcycle' => '/Users/pondokit/.gemini/antigravity/brain/3b338178-13aa-4d3f-bbbf-4865819c59a5/icon_motorcycle_isometric_1769971853309.png',
    'restaurant' => '/Users/pondokit/.gemini/antigravity/brain/3b338178-13aa-4d3f-bbbf-4865819c59a5/icon_restaurant_isometric_1769971869077.png',
    'hotel' => '/Users/pondokit/.gemini/antigravity/brain/3b338178-13aa-4d3f-bbbf-4865819c59a5/icon_hotel_isometric_1769971888729.png',
    'entertainment' => '/Users/pondokit/.gemini/antigravity/brain/3b338178-13aa-4d3f-bbbf-4865819c59a5/icon_entertainment_isometric_1769971904043.png',
];

$urls = [];

foreach ($newIcons as $key => $path) {
    echo "Uploading $key ($path)...\n";
    $file = new UploadedFile($path, basename($path), 'image/png', null, true);
    try {
        $urls[$key] = $cloudinary->upload($file, 'retribusi/icons/classification');
        echo "Uploaded $key: " . $urls[$key] . "\n";
    } catch (\Exception $e) {
        echo "Error uploading $key: " . $e->getMessage() . "\n";
    }
}

// Mapping Keys to Classification Name Patterns
$mapping = [
    'electricity' => ['Tenaga Listrik'],
    'motorcycle' => ['Motor'],
    'restaurant' => ['Restoran', 'Makan dan Minum', 'Pujasera'],
    'hotel' => ['Hotel', 'Perhotelan'],
    'entertainment' => ['Hiburan', 'Kesenian'],
];

foreach ($urls as $key => $url) {
    if (!isset($mapping[$key])) continue;
    
    foreach ($mapping[$key] as $pattern) {
        $classifications = RetributionClassification::where('name', 'LIKE', "%$pattern%")->get();
        foreach ($classifications as $cls) {
            $cls->icon = $url;
            $cls->save();
            echo "Updated classification '{$cls->name}' with icon.\n";
        }
    }
}

echo "\nSummary of URLs:\n";
print_r($urls);
echo "\nProcess completed!\n";
