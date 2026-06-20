<?php

namespace Tests\Feature;

use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SSOM2MTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_return_flow_fetches_m2m_token_with_api_key_and_nim(): void
    {
        Cache::forget('sso_m2m_token');

        Http::fake(function (Request $request) {
            $url = $request->url();

            if (str_ends_with($url, '/api/v1/auth/token')) {
                return Http::response(['access_token' => 'loan-m2m-token'], 200);
            }

            if (str_contains($url, '/stock/return')) {
                return Http::response(['data' => ['available_stock' => 3]], 200);
            }

            if (str_ends_with($url, '/soap/v1/audit')) {
                return Http::response('<ReceiptNumber>IAE-LOG-TEST</ReceiptNumber>', 200);
            }

            if (str_ends_with($url, '/api/v1/messages/publish')) {
                return Http::response(['success' => true], 200);
            }

            return Http::response([], 404);
        });

        $loan = Loan::create([
            'member_id' => 1,
            'book_id' => 5,
            'borrow_date' => '2026-06-19',
            'status' => 'active',
        ]);

        $response = $this->withoutMiddleware()
            ->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertOk();

        Http::assertSent(function (Request $request) {
            $payload = $request->data();

            return str_ends_with($request->url(), '/api/v1/auth/token')
                && ($payload['api_key'] ?? null) === 'KEY-MHS-122'
                && ($payload['nim'] ?? null) === '102022400110';
        });
    }
}
