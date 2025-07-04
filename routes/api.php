<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FundingController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/fund', [FundingController::class, 'fund']);
});
Route::middleware('auth:sanctum')->get('/referrals', [\App\Http\Controllers\ReferralController::class, 'index']);
