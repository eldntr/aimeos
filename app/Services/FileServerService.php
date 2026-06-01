<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class FileServerService
{
    /**
     * Generate JWT HS256 Token
     */
    public function generateJwtToken(): string
    {
        $secret = env('JWT_SECRET');
        if (!$secret) {
            throw new \Exception("JWT_SECRET is not configured in .env");
        }
        
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode(['user_id' => 'user-1234', 'exp' => time() + 3600 * 24 * 365]);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Upload an uploaded file to the external file server.
     */
    public function uploadFile(UploadedFile $file): string
    {
        $secret = env('JWT_SECRET');
        $apiUrl = env('FILE_SERVER_URL');

        // Fallback to local storage (dummy mode) if file server is not configured
        if (!$secret || !$apiUrl) {
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            copy($file->getRealPath(), public_path('uploads/' . $filename));
            return url('uploads/' . $filename);
        }

        $token = $this->generateJwtToken();
        
        $filename = $file->getClientOriginalName();
        $filesize = $file->getSize();
        $mimeType = $file->getMimeType();
        
        // 1. Presign Upload
        $response = Http::withToken($token)
            ->post("$apiUrl/api/files/presign-upload", [
                'filename' => $filename,
                'mime_type' => $mimeType,
                'size' => $filesize
            ]);
            
        if (!$response->successful()) {
            throw new \Exception("Failed to get presigned URL: " . $response->body());
        }
        
        $data = $response->json();
        $uploadUrl = $data['upload_url'] ?? null;
        $fileId = $data['file_id'] ?? null;
        
        if (!$uploadUrl || !$fileId) {
            throw new \Exception("Invalid response from presign-upload API.");
        }
        
        // Sesuaikan dengan jaringan internal Docker (host.docker.internal)
        $uploadUrl = str_replace('minio:9000', 'host.docker.internal:9000', $uploadUrl);
        $uploadUrl = str_replace('localhost:9000', 'host.docker.internal:9000', $uploadUrl);
        
        // 2. Upload file biner ke MinIO menggunakan stream
        $fileStream = fopen($file->path(), 'r');
        $uploadResponse = Http::withHeaders([
            'Content-Type' => $mimeType,
            'Host' => 'minio:9000'
        ])->send('PUT', $uploadUrl, [
            'body' => $fileStream
        ]);
        
        if (is_resource($fileStream)) {
            fclose($fileStream);
        }
        
        if (!$uploadResponse->successful()) {
            throw new \Exception("Failed to upload file to MinIO: " . $uploadResponse->body());
        }
        
        // 3. Complete Upload
        $completeResponse = Http::withToken($token)
            ->post("$apiUrl/api/files/$fileId/complete-upload", []);
            
        if (!$completeResponse->successful()) {
            throw new \Exception("Failed to notify complete-upload: " . $completeResponse->body());
        }
        
        return "$apiUrl/api/files/$fileId/content?token=$token";
    }

    /**
     * Trigger background compression task on the file server.
     */
    public function triggerCompression(): void
    {
        $apiUrl = env('FILE_SERVER_URL');
        if ($apiUrl) {
            Http::withToken('2afc271012598e4f2d703a9b0ea637d6b7d625ff8b14c2fb46e872f9ba8d5a55')
                ->post("$apiUrl/internal/files/compress-pending");
        }
    }
}
