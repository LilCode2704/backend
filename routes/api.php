<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Endpoint Publik (Belum punya token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Debug endpoint - cek storage files
Route::get('/debug/storage', function () {
    $publicDisk = Storage::disk('public');

    return response()->json([
        'storage_path' => storage_path('app/public'),
        'public_path' => public_path('storage'),
        'profiles_exists' => $publicDisk->exists('profiles'),
        'all_files' => $publicDisk->allFiles('profiles'),
        'disk_config' => config('filesystems.disks.public'),
    ]);
});

// Serve storage files (photos)
Route::get('/storage/{path}', function ($path) {
    // Use public disk directly, don't add 'public/' prefix
    $publicDisk = Storage::disk('public');

    // Debug logging
    \Log::info('Storage request', [
        'requested_path' => $path,
        'exists_check' => $publicDisk->exists($path),
        'full_path' => $publicDisk->path($path),
        'all_files' => $publicDisk->allFiles('profiles'),
    ]);

    if (!$publicDisk->exists($path)) {
        return response()->json([
            'error' => 'File not found in storage',
            'path' => $path,
            'all_files' => $publicDisk->allFiles('profiles'),
        ], 404);
    }

    $fullPath = $publicDisk->path($path);

    if (!file_exists($fullPath)) {
        return response()->json([
            'error' => 'File exists in Storage but not on filesystem',
            'storage_path' => $fullPath,
        ], 404);
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
