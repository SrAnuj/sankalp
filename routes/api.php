<?php

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\GoogleAuthController;
use App\Http\Controllers\Api\V1\AppleAuthController;
use App\Http\Controllers\Api\V1\Admin\NaamLibraryController;
use App\Http\Controllers\Api\V1\SankalpProgressController;
use App\Http\Controllers\Api\V1\Admin\SankalpController;
use App\Http\Controllers\Api\V1\Admin\DailyTargetController;
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


// Admin Panel Routes

Route::group([
    'prefix' => 'v1/admin',
    'middleware' => ['auth:api'],
    'as' => 'api.v1.admin.',
], function () {

    // =========================
    // Naam Library APIs
    // =========================

    Route::get('naam-library', [
        NaamLibraryController::class, 'index'
    ])->name('naam-library.index');

    Route::post('naam-library', [
        NaamLibraryController::class, 'store'
    ])->name('naam-library.store');

    Route::post('naam-library/bulk-delete', [
        NaamLibraryController::class, 'bulkDelete'
    ])->name('naam-library.bulk-delete');

    Route::get('naam-library/{naam}', [
        NaamLibraryController::class, 'show'
    ])->whereNumber('naam')->name('naam-library.show');

    Route::put('naam-library/{naam}', [
        NaamLibraryController::class, 'update'
    ])->whereNumber('naam')->name('naam-library.update');

    Route::patch('naam-library/{naam}/status', [
        NaamLibraryController::class, 'updateStatus'
    ])->whereNumber('naam')->name('naam-library.status');

    Route::patch('naam-library/{naam}/sort-order', [
        NaamLibraryController::class, 'updateSortOrder'
    ])->whereNumber('naam')->name('naam-library.sort-order');

    Route::delete('naam-library/{naam}', [
        NaamLibraryController::class, 'destroy'
    ])->whereNumber('naam')->name('naam-library.destroy');


    // =========================
    // Sankalp Admin APIs
    // =========================

    Route::get('sankalps/summary', [
        SankalpController::class, 'summary'
    ])->name('sankalps.summary');

    Route::get('sankalps', [
        SankalpController::class, 'index'
    ])->name('sankalps.index');

    Route::post('sankalps', [
        SankalpController::class, 'store'
    ])->name('sankalps.store');


    Route::get('sankalps/{sankalp}', [
        SankalpController::class, 'show'
    ])->whereNumber('sankalp')->name('sankalps.show');

    Route::put('sankalps/{sankalp}', [SankalpController::class, 'update'])
        ->whereNumber('sankalp')
        ->name('sankalps.update');

    Route::patch('sankalps/{sankalp}/status', [
        SankalpController::class, 'updateStatus'
    ])->whereNumber('sankalp')->name('sankalps.status');

    Route::delete('sankalps/{sankalp}', [SankalpController::class, 'destroy'])
        ->whereNumber('sankalp')
        ->name('sankalps.destroy');

        Route::post('sankalps/bulk-delete', [SankalpController::class, 'bulkDelete'])
        ->name('sankalps.bulk-delete');

    // =========================
    // Sankalp Progress APIs
    // =========================
  Route::get(
    'sankalp-progress',
    [SankalpProgressController::class, 'index']
);

Route::post(
    'sankalp-progress',
    [SankalpProgressController::class, 'store']
);

Route::post(
    'sankalp-progress/{sankalp}/progress',
    [SankalpProgressController::class, 'addProgress']
)->whereNumber('sankalp');

Route::get(
    'sankalp-progress/{sankalp}/daily-progress',
    [SankalpProgressController::class, 'dailyProgress']
)->whereNumber('sankalp');

// =========================
    // Sankalp Daily Target APIs
    // =========================

 Route::get(
        'daily-targets',
        [DailyTargetController::class, 'index']
    );

    Route::post(
        'daily-targets',
        [DailyTargetController::class, 'store']
    );

    Route::get(
        'daily-targets/{sankalp}',
        [DailyTargetController::class, 'show']
    )->whereNumber('sankalp');

    Route::put(
        'daily-targets/{sankalp}',
        [DailyTargetController::class, 'update']
    )->whereNumber('sankalp');

    Route::get(
        'daily-targets/{sankalp}/history',
        [DailyTargetController::class, 'history']
    )->whereNumber('sankalp');

    Route::get(
        'daily-targets/{sankalp}/today',
        [DailyTargetController::class, 'today']
    )->whereNumber('sankalp');

    
});

