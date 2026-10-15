<?php

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\GoogleAuthController;
use App\Http\Controllers\Api\V1\AppleAuthController;
use App\Http\Controllers\Api\V1\Admin\NaamLibraryController;
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

// Admin Panel
Route::prefix('v1/admin')
    ->middleware(['auth:api'])
    ->name('api.v1.admin.')
    ->group(function () {
        // List Naam Library
        Route::get(
            'naam-library',
            [NaamLibraryController::class, 'index']
        )->name('naam-library.index');

        // Create Naam
        Route::post(
            'naam-library',
            [NaamLibraryController::class, 'store']
        )->name('naam-library.store');

        // Bulk delete - keep before ID-based routes
        Route::post(
            'naam-library/bulk-delete',
            [NaamLibraryController::class, 'bulkDelete']
        )->name('naam-library.bulk-delete');

        // Show single Naam
        Route::get(
            'naam-library/{naam}',
            [NaamLibraryController::class, 'show']
        )
            ->whereNumber('naam')
            ->name('naam-library.show');

        // Update Naam
        Route::put(
            'naam-library/{naam}',
            [NaamLibraryController::class, 'update']
        )
            ->whereNumber('naam')
            ->name('naam-library.update');

        // Update status
        Route::patch(
            'naam-library/{naam}/status',
            [NaamLibraryController::class, 'updateStatus']
        )
            ->whereNumber('naam')
            ->name('naam-library.status');

        // Update sort order
        Route::patch(
            'naam-library/{naam}/sort-order',
            [NaamLibraryController::class, 'updateSortOrder']
        )
            ->whereNumber('naam')
            ->name('naam-library.sort-order');

        // Delete single Naam
        Route::delete(
            'naam-library/{naam}',
            [NaamLibraryController::class, 'destroy']
        )
            ->whereNumber('naam')
            ->name('naam-library.destroy');
    });


