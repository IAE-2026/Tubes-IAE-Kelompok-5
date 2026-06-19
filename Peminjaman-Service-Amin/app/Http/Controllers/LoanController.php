<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Info(
 * title="Service Peminjaman API",
 * version="1.0.0",
 * description="Dokumentasi layanan peminjaman buku untuk Tugas 2 IAE"
 * )
 * @OA\Server(
 * url="http://127.0.0.1:8000",
 * description="Local Server"
 * )
 */
class LoanController extends Controller
{
    private function formatResponse($status, $message, $data = null, $code = 200)
    {
        $response = [
            'status' => $status,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($status === 'success') {
            $response['meta'] = [
                'service_name' => 'Peminjaman-Service',
                'api_version' => 'v1'
            ];
        }

        return response()->json($response, $code);
    }

    /**
     * @OA\Get(
     * path="/api/v1/loans",
     * summary="Ambil semua transaksi peminjaman",
     * tags={"Loans"},
     * @OA\Parameter(
     * name="Authorization",
     * in="header",
     * required=true,
     * description="Bearer Token (JWT) dari SSO Warga",
     * @OA\Schema(type="string", default="Bearer <token>")
     * ),
     * @OA\Response(
     * response=200,
     * description="Data retrieved successfully"
     * )
     * )
     */
    public function index()
    {
        $loans = Loan::all();
        return $this->formatResponse('success', 'Data retrieved successfully', $loans, 200);
    }

    /**
     * @OA\Get(
     * path="/api/v1/loans/{id}",
     * summary="Ambil detail satu transaksi",
     * tags={"Loans"},
     * @OA\Parameter(
     * name="Authorization",
     * in="header",
     * required=true,
     * description="Bearer Token (JWT) dari SSO Warga",
     * @OA\Schema(type="string", default="Bearer <token>")
     * ),
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID Transaksi Peminjaman",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(response=200, description="Data retrieved successfully"),
     * @OA\Response(response=404, description="Resource not found")
     * )
     */
    public function show($id)
    {
        $loan = Loan::find($id);

        if (!$loan) {
            return $this->formatResponse('error', 'Resource not found', null, 404);
        }

        return $this->formatResponse('success', 'Data retrieved successfully', $loan, 200);
    }

    /**
     * @OA\Post(
     * path="/api/v1/loans",
     * summary="Buat transaksi peminjaman baru",
     * tags={"Loans"},
     * @OA\Parameter(
     * name="Authorization",
     * in="header",
     * required=true,
     * description="Bearer Token (JWT) dari SSO Warga",
     * @OA\Schema(type="string", default="Bearer <token>")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"member_id","book_id"},
     * @OA\Property(property="member_id", type="integer", example=1),
     * @OA\Property(property="book_id", type="integer", example=5)
     * )
     * ),
     * @OA\Response(response=201, description="Data created successfully")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer',
            'book_id' => 'required|integer',
        ]);

        $memberResult = $this->fetchMember((int) $request->member_id);

        if (! $memberResult['ok']) {
            return $this->formatResponse(
                'error',
                $memberResult['message'],
                $memberResult['data'],
                $memberResult['code']
            );
        }

        $member = $memberResult['data'];
        if (($member['is_active'] ?? false) !== true || ($member['status'] ?? null) !== 'active') {
            return $this->formatResponse('error', 'Member is not active', [
                'member_id' => (int) $request->member_id,
                'member_status' => $member['status'] ?? null,
            ], 422);
        }

        $bookResult = $this->fetchBook((int) $request->book_id, $request->bearerToken());

        if (! $bookResult['ok']) {
            return $this->formatResponse(
                'error',
                $bookResult['message'],
                $bookResult['data'],
                $bookResult['code']
            );
        }

        $book = $bookResult['data'];
        if ((int) ($book['available_stock'] ?? 0) < 1) {
            return $this->formatResponse('error', 'Book stock is not available', [
                'book_id' => (int) $request->book_id,
                'available_stock' => (int) ($book['available_stock'] ?? 0),
            ], 422);
        }

        $stockResult = $this->postCatalogStockAction((int) $request->book_id, 'borrow', $request->bearerToken());

        if (! $stockResult['ok']) {
            return $this->formatResponse(
                'error',
                $stockResult['message'],
                $stockResult['data'],
                $stockResult['code']
            );
        }

        $loan = Loan::create([
            'member_id' => $request->member_id,
            'book_id' => $request->book_id,
            'borrow_date' => now()->format('Y-m-d'),
            'status' => 'active'
        ]);

        return $this->formatResponse('success', 'Data created successfully', [
            'loan' => $loan,
            'validated_member' => [
                'id' => $member['id'] ?? $request->member_id,
                'status' => $member['status'] ?? null,
                'is_active' => $member['is_active'] ?? null,
            ],
            'validated_book' => [
                'id' => $book['id'] ?? $request->book_id,
                'title' => $book['title'] ?? null,
                'available_stock_before' => (int) ($book['available_stock'] ?? 0),
                'available_stock_after' => $stockResult['data']['available_stock'] ?? null,
            ],
        ], 201);
    }

    /**
     * @OA\Post(
     * path="/api/v1/loans/{id}/return",
     * summary="Proses pengembalian buku",
     * tags={"Loans"},
     * @OA\Parameter(
     * name="Authorization",
     * in="header",
     * required=true,
     * description="Bearer Token (JWT) dari SSO Warga",
     * @OA\Schema(type="string", default="Bearer <token>")
     * ),
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID Transaksi Peminjaman",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(response=200, description="Book returned successfully"),
     * @OA\Response(response=404, description="Resource not found")
     * )
     */
    public function returnBook(Request $request, $id)
    {
        $loan = Loan::find($id);

        if (!$loan) {
            return $this->formatResponse('error', 'Resource not found', null, 404);
        }

        if ($loan->status === 'returned') {
            return $this->formatResponse('error', 'Book is already returned', null, 400);
        }

        $stockResult = $this->postCatalogStockAction((int) $loan->book_id, 'return', $request->bearerToken());

        if (! $stockResult['ok']) {
            return $this->formatResponse(
                'error',
                $stockResult['message'],
                $stockResult['data'],
                $stockResult['code']
            );
        }

        $loan->update([
            'return_date' => now()->format('Y-m-d'),
            'status' => 'returned'
        ]);

        // SOAP Audit & AMQP Publisher integration (Tugas 3)
        // Fetch M2M Bearer Token using the API_KEY and NIM
        $m2mToken = \Illuminate\Support\Facades\Cache::remember('sso_m2m_token', 3000, function () {
            try {
                $response = \Illuminate\Support\Facades\Http::acceptJson()
                    ->timeout(10)
                    ->post(env('SSO_URL', 'https://iae-sso.virtualfri.id') . '/api/v1/auth/token', [
                        'api_key' => env('API_KEY'),
                        'nim' => env('NIM'),
                    ]);
                if ($response->successful()) {
                    return $response->json('token') ?? $response->json('access_token');
                }
                \Illuminate\Support\Facades\Log::error('Failed to fetch M2M token: ' . $response->body());
                return null;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Exception fetching M2M token: ' . $e->getMessage());
                return null;
            }
        });

        $auditData = [
            'loan_id' => $loan->id,
            'member_id' => $loan->member_id,
            'book_id' => $loan->book_id,
            'borrow_date' => $loan->borrow_date,
            'return_date' => $loan->return_date,
            'status' => $loan->status,
        ];

        // 1. Send SOAP Audit Log
        $soapService = new \App\Services\SoapAuditService();
        $receiptNumber = null;
        if ($m2mToken) {
            $receiptNumber = $soapService->sendAudit('BookReturned', $auditData, $m2mToken);
            if ($receiptNumber) {
                $loan->update(['receipt_number' => $receiptNumber]);
            }
        }

        // 2. Publish AMQP Event Notification
        if ($m2mToken) {
            $amqpService = new \App\Services\AmqpPublisherService();
            $amqpService->publish('library.loan.returned', $loan, $m2mToken);
        }

        return $this->formatResponse('success', 'Book returned successfully', [
            'loan' => $loan->fresh(),
            'catalog_stock' => $stockResult['data'],
            'audit' => [
                'receipt_number' => $receiptNumber,
                'soap_audit_attempted' => (bool) $m2mToken,
                'rabbitmq_publish_attempted' => (bool) $m2mToken,
            ],
        ], 200);
    }

    private function fetchMember(int $memberId): array
    {
        $baseUrl = rtrim(env('MEMBER_SERVICE_URL', 'http://member-service:8000'), '/');
        $apiKey = env('MEMBER_SERVICE_API_KEY', '102022400255');

        try {
            $response = Http::acceptJson()
                ->withHeaders(['X-IAE-KEY' => $apiKey])
                ->timeout(10)
                ->get("{$baseUrl}/api/v1/members/{$memberId}");

            if ($response->status() === 404) {
                return [
                    'ok' => false,
                    'message' => 'Member not found in member service',
                    'data' => ['member_id' => $memberId],
                    'code' => 404,
                ];
            }

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'message' => 'Member service validation failed',
                    'data' => [
                        'member_id' => $memberId,
                        'member_service_status' => $response->status(),
                        'member_service_body' => $response->json() ?? $response->body(),
                    ],
                    'code' => 502,
                ];
            }

            return [
                'ok' => true,
                'message' => 'Member validated',
                'data' => $response->json('data') ?? [],
                'code' => 200,
            ];
        } catch (\Throwable $e) {
            Log::warning('Member service validation exception: ' . $e->getMessage());

            return [
                'ok' => false,
                'message' => 'Member service is unreachable',
                'data' => ['member_id' => $memberId],
                'code' => 502,
            ];
        }
    }

    private function fetchBook(int $bookId, ?string $bearerToken): array
    {
        $baseUrl = rtrim(env('CATALOG_SERVICE_URL', 'http://katalog-buku-service:8000'), '/');
        $apiKey = env('CATALOG_SERVICE_API_KEY', 'KEY-MHS-44');

        try {
            $request = Http::acceptJson()
                ->withHeaders(['X-IAE-KEY' => $apiKey])
                ->timeout(10);

            if ($bearerToken) {
                $request = $request->withToken($bearerToken);
            }

            $response = $request->get("{$baseUrl}/api/v1/books/{$bookId}");

            if ($response->status() === 404) {
                return [
                    'ok' => false,
                    'message' => 'Book not found in catalog service',
                    'data' => ['book_id' => $bookId],
                    'code' => 404,
                ];
            }

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'message' => 'Catalog service validation failed',
                    'data' => [
                        'book_id' => $bookId,
                        'catalog_service_status' => $response->status(),
                        'catalog_service_body' => $response->json() ?? $response->body(),
                    ],
                    'code' => 502,
                ];
            }

            return [
                'ok' => true,
                'message' => 'Book validated',
                'data' => $response->json('data') ?? [],
                'code' => 200,
            ];
        } catch (\Throwable $e) {
            Log::warning('Catalog service validation exception: ' . $e->getMessage());

            return [
                'ok' => false,
                'message' => 'Catalog service is unreachable',
                'data' => ['book_id' => $bookId],
                'code' => 502,
            ];
        }
    }

    private function postCatalogStockAction(int $bookId, string $action, ?string $bearerToken): array
    {
        $baseUrl = rtrim(env('CATALOG_SERVICE_URL', 'http://katalog-buku-service:8000'), '/');
        $apiKey = env('CATALOG_SERVICE_API_KEY', 'KEY-MHS-44');

        try {
            $request = Http::acceptJson()
                ->withHeaders(['X-IAE-KEY' => $apiKey])
                ->timeout(10);

            if ($bearerToken) {
                $request = $request->withToken($bearerToken);
            }

            $response = $request->post("{$baseUrl}/api/v1/books/{$bookId}/stock/{$action}");

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'message' => "Catalog stock {$action} failed",
                    'data' => [
                        'book_id' => $bookId,
                        'catalog_service_status' => $response->status(),
                        'catalog_service_body' => $response->json() ?? $response->body(),
                    ],
                    'code' => $response->status() === 404 ? 404 : 502,
                ];
            }

            return [
                'ok' => true,
                'message' => "Catalog stock {$action} succeeded",
                'data' => $response->json('data') ?? [],
                'code' => 200,
            ];
        } catch (\Throwable $e) {
            Log::warning("Catalog stock {$action} exception: " . $e->getMessage());

            return [
                'ok' => false,
                'message' => 'Catalog stock service is unreachable',
                'data' => [
                    'book_id' => $bookId,
                    'action' => $action,
                ],
                'code' => 502,
            ];
        }
    }
}
