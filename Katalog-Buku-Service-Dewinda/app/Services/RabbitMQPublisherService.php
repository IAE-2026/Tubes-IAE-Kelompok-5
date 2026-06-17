<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RabbitMQPublisherService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('SSO_URL', 'https://iae-sso.virtualfri.id'), '/');
    }

    public function publish(array $book, string $receiptNumber, string $jwtToken): void
    {
        $payload = [
            'exchange' => 'iae.central.exchange',
            'routing_key' => 'library.catalog.book.created',
            'message' => [
                'event'   => 'library.catalog.book.created',
                'service' => 'catalog-service',
                'team_id' => env('IAE_TEAM_ID', 'TEAM-05'),
                'nim'     => env('IAE_API_KEY', 'KEY-MHS-44'),
                'data'    => [
                    'book_id'        => $book['id'],
                    'title'          => $book['title'],
                    'author'         => $book['author'],
                    'isbn'           => $book['isbn'],
                    'stock'          => $book['stock'],
                    'receipt_number' => $receiptNumber,
                    'timestamp'      => now()->toIso8601String(),
                ],
            ],
        ];

        try {
            $response = Http::withToken($jwtToken)
                ->post("{$this->baseUrl}/api/v1/messages/publish", $payload);

            if ($response->failed()) {
                \Illuminate\Support\Facades\Log::error('RabbitMQ publish gagal: ' . $response->body());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Koneksi ke RabbitMQ API gagal: ' . $e->getMessage());
        }
    }
}
