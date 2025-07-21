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
        $result = $this->cloudinary->uploadApi()->upload(
            $image->getRealPath(),
            ['folder' => $folder]
        );

        // Optional: log isi response saat debug
        // Log::info('Cloudinary upload result', $result);

        return [
            'secure_url' => $result['secure_url'] ?? null,
            'public_id'  => $result['public_id'] ?? null,
        ];
    }

    public function uploadWithRetry($image, $folder = 'default_folder', $maxRetry = 3)
    {
        for ($i = 0; $i < $maxRetry; $i++) {
            try {
                return $this->upload($image, $folder);
            } catch (\Exception $e) {
                Log::warning("Upload Cloudinary retry ke-" . ($i + 1), [
                    'error' => $e->getMessage(),
                    'file' => $image->getClientOriginalName() ?? 'unknown',
                ]);

                if ($i === $maxRetry - 1) {
                    throw $e;
                }

                sleep(1); // Delay antar percobaan
            }
        }
    }

    public function destroy(string $publicId): array
    {
        try {
            $response = $this->cloudinary->uploadApi()->destroy($publicId);
            $responseArray = $response->getArrayCopy();

            if (!isset($responseArray['result']) || $responseArray['result'] !== 'ok') {
                Log::warning('Gagal hapus file di Cloudinary', [
                    'public_id' => $publicId,
                    'response' => $responseArray,
                ]);
            }

            return $responseArray;
        } catch (\Exception $e) {
            Log::error('Exception saat hapus file Cloudinary', [
                'public_id' => $publicId,
                'error' => $e->getMessage(),
            ]);

            return ['result' => null, 'error' => $e->getMessage()];
        }
    }
}
