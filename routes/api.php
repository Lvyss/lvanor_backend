<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\User\{
    UserController,
    UserWeblistController,
    UserWeblistDetailController
};
use App\Http\Controllers\Api\Admin\{
    AdminController,
    AdminWeblistController
};

Route::prefix('v1')->group(function () {

    Route::fallback(fn() => response()->json(['message' => 'Endpoint tidak ditemukan.'], 404));

    // 📌 Public Access
    Route::middleware('throttle:5,1')->post('/social-login', [AuthController::class, 'loginWithProvider']);
    Route::get('/indexWeblist', [UserWeblistController::class, 'indexWeblist']);
    Route::get('/category', [CategoryController::class, 'index']);


    // 👤 Authenticated Users
    Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::controller(UserController::class)->group(function () {
            Route::get('/profile', 'profile');
            Route::put('/updateProfile', 'updateProfile');
            Route::get('/publicProfile/{id}', 'publicProfile');
        });

        Route::apiResource('/weblist', UserWeblistController::class);
                Route::controller(UserWeblistController::class)->group(function () {
            Route::get('/showWeblist/{id}', 'showWeblist');
            Route::get('/publicWeblist/{id}', 'publicWeblist');
        });

        Route::controller(UserWeblistDetailController::class)->group(function () {
            Route::post('/weblist/{id}/updateDetail', 'updateDetail');
            Route::post('/weblist/{id}/storeImg', 'storeImg')->middleware('throttle:10,1');
            Route::delete('/weblist/{id}/destroyImg', 'destroyImg');
        });
    });

    // 🛡️ Admin Routes
    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::controller(AdminController::class)->group(function () {
            Route::get('/profile', 'profile');
            Route::put('/profile/update', 'updateProfile');
        });

        Route::controller(UserController::class)->group(function () {
            Route::get('/users', 'getAllUsers');
            Route::delete('/users/{id}', 'deleteUser');
        });

        Route::apiResource('/category', CategoryController::class);
        Route::apiResource('/weblist', AdminWeblistController::class);
    });
});
