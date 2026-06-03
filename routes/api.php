<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerAssetController;
use App\Http\Controllers\Api\ServiceReportController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentCounterController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UnitController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Public — used by UptimeRobot to keep Render + Neon awake
Route::get('/ping', function () {
    try {
        DB::select('SELECT 1');
        return response()->json(['status' => 'ok', 'db' => 'connected']);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'ok', 'db' => 'error'], 200);
    }
});

Route::post('/login', [AuthController::class, 'login']);

// Protected
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',     [AuthController::class, 'me']);

    Route::apiResource('units', UnitController::class);
    Route::apiResource('materials', MaterialController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('customer-assets', CustomerAssetController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('document-counters', DocumentCounterController::class)->only(['index', 'update']);

    // Explicit document routes must come BEFORE apiResource to avoid {document} param capture
    Route::get('documents/next-number',                        [DocumentController::class, 'nextNumber']);
    Route::get('documents/{document}/pdf',                     [DocumentController::class, 'pdf']);
    Route::get('documents/{document}/preview',                 [DocumentController::class, 'preview']);
    Route::patch('documents/{document}/payment-status',        [DocumentController::class, 'updatePaymentStatus']);
    Route::apiResource('documents', DocumentController::class);

    Route::get('dashboard/stats', [DashboardController::class, 'stats']);

    Route::get('service-reports/next-number',                   [ServiceReportController::class, 'nextNumber']);
    Route::post('service-reports/{serviceReport}/send-mail',    [ServiceReportController::class, 'sendMail']);
    Route::get('service-reports/{serviceReport}/pdf',           [ServiceReportController::class, 'pdf']);
    Route::apiResource('service-reports', ServiceReportController::class);
});
