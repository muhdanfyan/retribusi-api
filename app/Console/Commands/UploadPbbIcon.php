<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UploadPbbIcon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:upload-pbb-icon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload PBB icon to Cloudinary';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = '/Users/pondokit/.gemini/antigravity/brain/849f30b2-f6e4-4642-b00f-f1f897da9048/pbb_icon_1769705570029.png';

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        $this->info("Uploading PBB icon...");

        try {
            $result = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::upload($filePath, [
                'folder' => 'retribusi/mobile/icons',
                'public_id' => 'pbb'
            ]);
            
            $url = $result->getSecurePath();
            $this->info("UPLOAD_SUCCESS: $url");
        } catch (\Exception $e) {
            $this->error("UPLOAD_ERROR: " . $e->getMessage());
            return 1;
        }
    }
}
