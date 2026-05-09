<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentCounterController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/ping', fn () => response()->json(['status' => 'ok', 'message' => 'API is running']));

Route::post('/login', [AuthController::class, 'login']);

// Protected
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',     [AuthController::class, 'me']);

    Route::apiResource('materials', MaterialController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('document-counters', DocumentCounterController::class)->only(['index', 'update']);

    // Explicit document routes must come BEFORE apiResource to avoid {document} param capture
    Route::get('documents/next-number',        [DocumentController::class, 'nextNumber']);
    Route::get('documents/{document}/pdf',     [DocumentController::class, 'pdf']);
    Route::get('documents/{document}/preview', [DocumentController::class, 'preview']);
    Route::apiResource('documents', DocumentController::class);

    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
});
