<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

/*
| URL Final: http://tu-dominio/api/v1/process
*/
Route::prefix('v1')->group(function () {
    Route::post('/process', [TransactionController::class, 'process']);
});

// Ruta de usuario opcional (Sanctum)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\CardController;


// Rutas protegidas (Requieren Login/Token)
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/my-cards', [CardController::class, 'index']); // Ver tarjetas
    Route::post('/cards/request', [CardController::class, 'store']); // Solicitar tarjeta

});

// Endpoint público para demostración (sin auth middleware para facilitar la prueba inmediata)
Route::get('/demo/user/{id}/transactions', [CardController::class, 'demoTransactions']);
