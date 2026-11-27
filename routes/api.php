<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Endpoint Publik (Belum punya token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Serve storage files (photos)
Route::get('/storage/{path}', function ($path) {
    $filePath = 'public/' . $path;

    // Debug logging
    \Log::info('Storage request', [
        'path' => $path,
        'fullPath' => $filePath,
        'exists' => Storage::exists($filePath),
        'storagePath' => Storage::path($filePath),
        'allFiles' => Storage::disk('public')->allFiles('profiles'),
    ]);

    if (!Storage::exists($filePath)) {
        abort(404, 'File not found in storage');
    }

    $fullPath = Storage::path($filePath);

    if (!file_exists($fullPath)) {
        abort(404, 'File path exists in Storage but not on filesystem');
    }

    return response()->file($fullPath);
})->where('path', '.*');
// Endpoint Terproteksi (Wajib membawa Header Authorization: Bearer <token>)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
