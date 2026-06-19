<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$payload = [
    'api_key' => env('IAE_API_KEY', 'KEY-MHS-44'),
    'nim' => env('SSO_NIM', '102022430028'),
];

echo "=== Test asJson ===\n";
$response = \Illuminate\Support\Facades\Http::asJson()->post('https://iae-sso.virtualfri.id/api/v1/auth/token', $payload);
echo "Status: " . $response->status() . "\n";
echo "Body: " . $response->body() . "\n\n";

echo "=== Test withBody raw JSON ===\n";
$response2 = \Illuminate\Support\Facades\Http::withHeaders([
    'Content-Type' => 'application/json',
    'Accept' => 'application/json',
])->withBody(json_encode($payload), 'application/json')->post('https://iae-sso.virtualfri.id/api/v1/auth/token');
echo "Status: " . $response2->status() . "\n";
echo "Body: " . $response2->body() . "\n\n";
