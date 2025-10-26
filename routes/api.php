<?php

use App\Http\Controllers\Api\V1\{AuthController, ProfileController, LinkController, PublicController, EventController};
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Private (butuh token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
         Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
         Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar']);


        Route::get('/links', [LinkController::class, 'index']);
        Route::post('/links', [LinkController::class, 'store']);
        Route::put('/links/{id}', [LinkController::class, 'update']);
        Route::delete('/links/{id}', [LinkController::class, 'destroy']);
        Route::post('/links/reorder', [LinkController::class, 'reorder']);
    });

    // Public
    Route::get('/u/{username}', [PublicController::class, 'profile']);
    Route::post('/click', [EventController::class, 'click']); // {link_id}
});
