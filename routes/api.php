<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExamsController;
use App\Http\Controllers\BundlerController;
use App\Http\Controllers\ReadingController;
use App\Http\Controllers\ListeningController;
use App\Http\Controllers\ExamsScoresController;
use App\Http\Controllers\ScoreDetailController;
use App\Http\Controllers\StructuringController;
use App\Http\Controllers\ExamsBundlerController;

// users datas path

// auth
Route::post('/login', [AuthController::class, 'login']);
Route::delete('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::apiResource('users', UserController::class);
});

// question datas path
Route::prefix('question')->group(function () {
    Route::apiResource('listening', ListeningController::class);
    Route::apiResource('reading', ReadingController::class);
    Route::apiResource('structuring', controller: StructuringController::class);
    Route::apiResource('bundler', controller: BundlerController::class);
});

// exam
Route::prefix('exam')->group(function () {
    Route::apiResource('/', ExamsController::class)->parameters(['' => 'exam']);
    Route::apiResource('/bundlers', ExamsBundlerController::class);
});

// point
Route::apiResource('/scores', ExamsScoresController::class);
Route::apiResource('/scores/detail', ScoreDetailController::class);

