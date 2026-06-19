<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SSOService
{
    public function __construct()
    {
        $this->baseUrl = env('SSO_URL', 'https://iae-sso.virtualfri.id');
    }

    private string $baseUrl;

    public function getToken(): string
    {
        // M2M token via api_key dan nim (sesuai update dosen, format JSON)
        $response = Http::acceptJson()->post("{$this->baseUrl}/api/v1/auth/token", [
            'api_key' => env('IAE_API_KEY', 'KEY-MHS-44'),
            'nim'     => env('NIM', '102022430028'),
        ]);

        if ($response->failed()) {
            throw new \Exception('SSO login gagal: ' . $response->body());
        }

        $token = $response->json('token') ?? $response->json('access_token');
        
        if (!$token) {
            throw new \Exception('Token tidak ditemukan dalam response SSO: ' . $response->body());
        }
        
        return $token;
    }

    public function getUserToken(): string
    {
        $response = Http::post("{$this->baseUrl}/api/v1/auth/token", [
            'email'    => env('SSO_EMAIL'),
            'password' => env('SSO_PASSWORD'),
        ]);

        if ($response->failed()) {
            throw new \Exception('SSO user login gagal: ' . $response->body());
        }

        return $response->json('access_token');
    }
}