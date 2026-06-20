<?php

namespace Tests\Feature;

use App\Services\SSOService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SSOServiceTest extends TestCase
{
    public function test_get_token_sends_api_key_and_nim(): void
    {
        Http::fake([
            '*/api/v1/auth/token' => Http::response(['access_token' => 'catalog-m2m-token'], 200),
        ]);

        $token = (new SSOService())->getToken();

        $this->assertSame('catalog-m2m-token', $token);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return $request->method() === 'POST'
                && str_ends_with($request->url(), '/api/v1/auth/token')
                && ($payload['api_key'] ?? null) === 'KEY-MHS-44'
                && ($payload['nim'] ?? null) === '102022430028';
        });
    }
}
