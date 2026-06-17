<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;

Route::prefix('v1')
    ->middleware(['iae.key', 'jwt.auth'])
    ->group(function () {

    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{id}', [BookController::class, 'show']);
    Route::post('/books', [BookController::class, 'store']);
    Route::post('/books/{id}/stock/borrow', [BookController::class, 'borrowStock']);
    Route::post('/books/{id}/stock/return', [BookController::class, 'returnStock']);

});