<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\SSOService;
use App\Services\SOAPAuditService;
use App\Services\RabbitMQPublisherService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    protected SSOService $ssoService;
    protected SOAPAuditService $soapService;
    protected RabbitMQPublisherService $mqService;

    public function __construct(
        SSOService $ssoService,
        SOAPAuditService $soapService,
        RabbitMQPublisherService $mqService
    ) {
        $this->ssoService  = $ssoService;
        $this->soapService = $soapService;
        $this->mqService   = $mqService;
    }

    /**
     * GET /api/v1/books
     */
    public function index(): JsonResponse
    {
        $books = Book::all();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data buku berhasil diambil.',
            'data'    => $books,
            'meta'    => [
                'service_name' => 'catalog-service',
                'api_version'  => 'v1',
                'total'        => $books->count(),
            ],
        ], 200);
    }

    /**
     * GET /api/v1/books/{id}
     */
    public function show(int $id): JsonResponse
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'status'  => 'error',
                'message' => "Buku dengan ID {$id} tidak ditemukan.",
                'errors'  => null,
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail buku berhasil diambil.',
            'data'    => $book,
            'meta'    => [
                'service_name' => 'catalog-service',
                'api_version'  => 'v1',
            ],
        ], 200);
    }

    /**
     * POST /api/v1/books
     */
    public function store(Request $request): JsonResponse
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'title'     => 'required|string|max:255',
            'author'    => 'required|string|max:255',
            'isbn'      => 'required|string|unique:books,isbn',
            'publisher' => 'required|string|max:255',
            'year'      => 'required|integer|min:1000|max:9999',
            'stock'     => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Simpan buku ke database
        $book = Book::create([
            'title'           => $request->title,
            'author'          => $request->author,
            'isbn'            => $request->isbn,
            'publisher'       => $request->publisher,
            'year'            => $request->year,
            'stock'           => $request->stock,
            'available_stock' => $request->stock,
        ]);

        try {
            // Step 1: Login SSO → dapat JWT
            $jwtToken = $this->ssoService->getToken();

            // Step 2: Kirim SOAP audit → dapat ReceiptNumber
            $receiptNumber = $this->soapService->sendAudit($book->toArray(), $jwtToken);

            // Step 3: Simpan ReceiptNumber ke database
            $book->update(['receipt_number' => $receiptNumber]);

            // Step 4: Publish event ke RabbitMQ
            $this->mqService->publish($book->toArray(), $receiptNumber, $jwtToken);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() . ' (Line: ' . $e->getLine() . ' in ' . basename($e->getFile()) . ')',
                'data'    => $book,
                'meta'    => [
                    'service_name' => 'catalog-service',
                    'api_version'  => 'v1',
                ],
            ], 201);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Buku berhasil ditambahkan.',
            'data'    => $book->fresh(),
            'meta'    => [
                'service_name' => 'catalog-service',
                'api_version'  => 'v1',
            ],
        ], 201);
    }

    /**
     * POST /api/v1/books/{id}/stock/borrow
     */
    public function borrowStock(int $id): JsonResponse
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'status'  => 'error',
                'message' => "Buku dengan ID {$id} tidak ditemukan.",
                'errors'  => null,
            ], 404);
        }

        if ((int) $book->available_stock < 1) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Stok buku tidak tersedia.',
                'data'    => [
                    'book_id'         => $book->id,
                    'available_stock' => (int) $book->available_stock,
                ],
            ], 422);
        }

        $book->decrement('available_stock');

        return response()->json([
            'status'  => 'success',
            'message' => 'Stok buku berhasil dikurangi untuk peminjaman.',
            'data'    => [
                'book_id'         => $book->id,
                'available_stock' => (int) $book->fresh()->available_stock,
            ],
            'meta'    => [
                'service_name' => 'catalog-service',
                'api_version'  => 'v1',
            ],
        ], 200);
    }

    /**
     * POST /api/v1/books/{id}/stock/return
     */
    public function returnStock(int $id): JsonResponse
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'status'  => 'error',
                'message' => "Buku dengan ID {$id} tidak ditemukan.",
                'errors'  => null,
            ], 404);
        }

        if ((int) $book->available_stock >= (int) $book->stock) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Stok buku sudah penuh.',
                'data'    => [
                    'book_id'         => $book->id,
                    'available_stock' => (int) $book->available_stock,
                ],
                'meta'    => [
                    'service_name' => 'catalog-service',
                    'api_version'  => 'v1',
                ],
            ], 200);
        }

        $book->increment('available_stock');

        return response()->json([
            'status'  => 'success',
            'message' => 'Stok buku berhasil dikembalikan.',
            'data'    => [
                'book_id'         => $book->id,
                'available_stock' => (int) $book->fresh()->available_stock,
            ],
            'meta'    => [
                'service_name' => 'catalog-service',
                'api_version'  => 'v1',
            ],
        ], 200);
    }
}
