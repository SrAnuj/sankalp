<?php

use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::get('/login', function () {
    return response()->json([
        'status' => true,
        'message' => 'Login API is working. Please use POST method for authentication.',
    ]);
});

Route::post('login', [UserController::class, 'login'])->name('login');
Route::post('register', [UserController::class, 'register']);
Route::apiResource('users', UserController::class);
