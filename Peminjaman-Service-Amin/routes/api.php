<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanController;

Route::prefix('v1')->group(function () {
    Route::middleware(['jwt'])->group(function () {
        Route::get('/loans', [LoanController::class, 'index']);
        Route::get('/loans/{id}', [LoanController::class, 'show']);
        Route::post('/loans', [LoanController::class, 'store']);
        Route::post('/loans/{id}/return', [LoanController::class, 'returnBook']);
    });
});