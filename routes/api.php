<?php

use App\Http\Controllers\Api\AllocationController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\ShiftMessageController;
use App\Http\Controllers\Api\WorkerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('workers', WorkerController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('shifts', ShiftController::class);

    Route::post('/shift-messages', [ShiftMessageController::class, 'store']);

    Route::post('/allocation/run', [AllocationController::class, 'run']);
    Route::get('/allocation/history', [AllocationController::class, 'history']);

    Route::patch('/assignments/{assignment}/confirm', [AssignmentController::class, 'confirm']);
    Route::patch('/assignments/{assignment}/cancel', [AssignmentController::class, 'cancel']);

    Route::get('/reports/hours', [ReportController::class, 'hours']);
    Route::get('/reports/workers', [ReportController::class, 'workers']);
    Route::get('/reports/shifts', [ReportController::class, 'shifts']);
});
