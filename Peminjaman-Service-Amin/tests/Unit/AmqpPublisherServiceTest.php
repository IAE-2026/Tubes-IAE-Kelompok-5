<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AmqpPublisherService;
use Illuminate\Support\Facades\Http;
use App\Models\Loan;

class AmqpPublisherServiceTest extends TestCase
{
    public function test_publish_payload_structure()
    {
        Http::fake([
            '*/api/v1/messages/publish' => Http::response(['success' => true], 200),
        ]);

        $loan = new Loan([
            'member_id' => 456,
            'book_id' => 789,
            'borrow_date' => '2026-06-01',
            'return_date' => '2026-06-12',
            'status' => 'returned',
        ]);
        $loan->id = 123; // Set the ID explicitly

        $service = new AmqpPublisherService();
        $result = $service->publish('library.loan.returned', $loan, 'test-bearer-token');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            $payload = $request->data();
            
            return $request->hasHeader('Authorization', 'Bearer test-bearer-token') &&
                ($payload['exchange'] ?? null) === 'iae.central.exchange' &&
                ($payload['routing_key'] ?? null) === 'library.loan.returned' &&
                ($payload['message']['event'] ?? null) === 'library.loan.returned' &&
                isset($payload['message']['timestamp']) &&
                ($payload['message']['data']['loan_id'] ?? null) === 123 &&
                ($payload['message']['data']['member_id'] ?? null) === 456 &&
                ($payload['message']['data']['book_id'] ?? null) === 789 &&
                ($payload['message']['data']['borrow_date'] ?? null) === '2026-06-01' &&
                ($payload['message']['data']['return_date'] ?? null) === '2026-06-12' &&
                ($payload['message']['data']['status'] ?? null) === 'returned';
        });
    }
}
