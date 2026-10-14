<?php

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\GoogleAuthController;
use App\Http\Controllers\Api\V1\AppleAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');



Route::prefix('v1')->group(function () {

    Route::get('/login', function () {
    return response()->json([
        'status' => true,
        'message' => 'Login API is working. Please use POST method for authentication.',
    ]);
});


    Route::post('auth/login', [UserController::class, 'loginWithOtp']);
    Route::post('auth/google', [GoogleAuthController::class, 'login']);
    Route::post('auth/apple', [AppleAuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('auth/logout', [UserController::class, 'logout']);
        Route::patch('profile', [UserController::class, 'updateProfile']);
    });

    Route::post('login', [UserController::class, 'login'])->name('login');
    Route::post('register', [UserController::class, 'register']);

    Route::apiResource('users', UserController::class);
    Route::patch('user/{id}/status', [UserController::class, 'updateStatus']);
    Route::post('user/bulk-delete', [UserController::class, 'bulk_user_delete']);

});


