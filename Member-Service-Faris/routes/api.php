<?php

use App\Http\Controllers\MemberController;
use App\Http\Middleware\EnsureIaeApiKey;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(EnsureIaeApiKey::class)->group(function (): void {
    Route::get('/members', [MemberController::class, 'index']);
    Route::post('/members', [MemberController::class, 'store']);
    Route::get('/members/{id}', [MemberController::class, 'show'])
        ->whereNumber('id');
});
