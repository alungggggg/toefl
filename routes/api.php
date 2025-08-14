<?php

use App\Http\Controllers\BundlerController;
use App\Http\Controllers\ExamsBundlerController;
use App\Http\Controllers\ExamsController;
use App\Http\Controllers\ReadingController;
use App\Http\Controllers\StructuringController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ListeningController;
use Illuminate\Support\Facades\Route;

// users datas path
Route::apiResource('users', UserController::class);

// question datas path
Route::prefix('question')->group(function () {
    Route::apiResource('listening', ListeningController::class);
    Route::apiResource('reading', ReadingController::class);
    Route::apiResource('structuring', controller: StructuringController::class);
    Route::apiResource('bundler', controller: BundlerController::class);
});

// exam
Route::apiResource('exam', ExamsController::class);
Route::apiResource('exam/bundler', ExamsBundlerController::class);

// point
