<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ==========================================
// ROUTE PUBLIC (Bisa diakses siapa saja)
// ==========================================
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/device-tokens', [DeviceTokenController::class, 'store'])->middleware('auth:sanctum');

// ==========================================
// ROUTE PRIVATE (Harus bawa Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    // User Routes
    Route::get('/users/me', [AuthController::class, 'me']);
    Route::get('/users/profile', [UserController::class, 'Profile']);
    Route::get('/users/items', [UserController::class, 'Items']);
    Route::get('/users/announcements', [UserController::class, 'Announcement']);
    Route::patch('/users/profile', [UserController::class, 'UpdateProfile']);

    // Auth Routes
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route Items (CRUD)
    Route::get('/items', [ItemController::class, 'index']);
    Route::get('/items/{id}', [ItemController::class, 'show']);
    Route::patch('/items/{id}', [ItemController::class, 'update']);
    Route::post('/items', [ItemController::class, 'store']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);
    Route::patch('/items/{id}/location', [ItemController::class, 'updateItemLastSeen']);

    // Route Announcement (CRUD)
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::get('/announcements/pending', [AnnouncementController::class, 'showPending']);
    Route::get('/announcements/{id}', [AnnouncementController::class, 'show']);
    Route::patch('/announcements/{id}', [AnnouncementController::class, 'update']);
    Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);
    Route::post('/announcements', [AnnouncementController::class, 'store']);
    Route::post('/announcements/voice', [AnnouncementController::class, 'storeVoice']);
});
