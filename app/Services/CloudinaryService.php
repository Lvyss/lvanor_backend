<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key'    => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
        ]);
    }

    public function upload($image, $folder = 'default_folder')
    {
        return $this->cloudinary->uploadApi()->upload(
            $image->getRealPath(),
            ['folder' => $folder]
        );
    }

    public function destroy($publicId)
    {
        return $this->cloudinary->uploadApi()->destroy($publicId);
    }

    public function uploadWithRetry($image, $folder = 'default_folder', $maxRetry = 3)
    {
        for ($i = 0; $i < $maxRetry; $i++) {
            try {
                return $this->upload($image, $folder);
            } catch (\Exception $e) {
                Log::warning("Upload Cloudinary retry ke-" . ($i + 1), ['error' => $e->getMessage()]);

                if ($i === $maxRetry - 1) {
                    throw $e;
                }

                sleep(1); // Delay antar percobaan
            }
        }
    }
}
