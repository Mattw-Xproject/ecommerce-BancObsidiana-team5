<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\CardController;

// routes/api.php
use App\Http\Controllers\Api\TransactionController;

Route::prefix('v1')->group(function () {
    Route::post('/transaction/process', [TransactionController::class, 'process']);
});


// Rutas protegidas (Requieren Login/Token)
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/my-cards', [CardController::class, 'index']); // Ver tarjetas
    Route::post('/cards/request', [CardController::class, 'store']); // Solicitar tarjeta

});

// Endpoint público para demostración (sin auth middleware para facilitar la prueba inmediata)
Route::get('/demo/user/{id}/transactions', [CardController::class, 'demoTransactions']);


// Endpoint público para demostración (sin auth middleware para facilitar la prueba inmediata)
Route::get('/demo/user/{id}/transactions', [CardController::class, 'demoTransactions']);
