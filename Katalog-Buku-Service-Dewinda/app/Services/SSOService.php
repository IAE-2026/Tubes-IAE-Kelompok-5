<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SSOService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('SSO_URL', 'https://iae-sso.virtualfri.id'), '/');
    }

    public function getToken(): string
    {
        // M2M token via JSON api_key and nim for the current SSO contract.
        $response = Http::asJson()->acceptJson()->post("{$this->baseUrl}/api/v1/auth/token", [
            'api_key' => env('IAE_API_KEY', 'KEY-MHS-44'),
            'nim' => env('SSO_NIM', env('IAE_API_KEY', 'KEY-MHS-44')),
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
            'email'    => env('SSO_EMAIL', 'warga05@ktp.iae.id'),
            'password' => env('SSO_PASSWORD', 'KtpDigital2026!'),
        ]);

        if ($response->failed()) {
            throw new \Exception('SSO user login gagal: ' . $response->body());
        }

        return $response->json('access_token');
    }
}
