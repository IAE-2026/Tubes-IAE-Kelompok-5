<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Keanggotaan service is running',
        'data' => [
            'service' => 'keanggotaan',
            'version' => 'v1',
        ],
    ]);
});
