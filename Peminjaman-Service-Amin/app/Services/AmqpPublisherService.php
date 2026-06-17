<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AmqpPublisherService
{
    private string $publishUrl;

    public function __construct()
    {
        $this->publishUrl = env('SSO_URL', 'https://iae-sso.virtualfri.id') . '/api/v1/messages/publish';
    }

    /**
     * Publish event JSON ke RabbitMQ Dosen via REST endpoint SSO.
     *
     * Routing key = event name (dot notation)
     * Contoh: loan.book.returned
     */
    public function publish(string $eventName, $loan, string $bearerToken): bool
    {
        $payload = [
            'exchange' => 'iae.central.exchange',
            'routing_key' => $eventName,
            'message' => [
                'event' => $eventName,
                'timestamp' => now()->setTimezone('UTC')->format('Y-m-d\TH:i:sP'),
                'data' => [
                    'loan_id' => $loan->id ?? null,
                    'member_id' => $loan->member_id ?? null,
                    'book_id' => $loan->book_id ?? null,
                    'borrow_date' => $loan->borrow_date ?? null,
                    'return_date' => $loan->return_date ?? null,
                    'status' => $loan->status ?? 'returned'
                ]
            ]
        ];

        try {
            $response = Http::withToken($bearerToken)
                ->timeout(10)
                ->post($this->publishUrl, $payload);

            if ($response->successful()) {
                Log::info('AMQP event published', [
                    'event'   => $eventName,
                    'status'  => $response->status(),
                    'response' => substr($response->body(), 0, 300),
                ]);
                return true;
            }

            Log::warning('AMQP publish failed', [
                'event'  => $eventName,
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 300),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('AMQP publish exception: ' . $e->getMessage());
            return false;
        }
    }
}
