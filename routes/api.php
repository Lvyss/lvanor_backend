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
use App\Http\Middleware\IsUser;
use App\Http\Middleware\IsAdmin;

Route::prefix('v1')->group(function () {

    Route::fallback(fn () => response()->json(['message' => 'Endpoint tidak ditemukan.'], 404));

    // 📌 Public Access
    Route::middleware('throttle:5,1')->post('/social-login', [AuthController::class, 'loginWithProvider']);
    Route::get('/explore-weblist', [AdminWeblistController::class, 'index']);
    Route::get('/category', [CategoryController::class, 'index']);
    Route::get('/user-profile/{id}', [UserController::class, 'publicProfile']);

    // 👤 Authenticated Users
    Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::controller(UserController::class)->group(function () {
            Route::get('/profile', 'profile');
            Route::put('/profile/update', 'updateProfile');
        });

        Route::get('/explore-weblist/{id}', [AdminWeblistController::class, 'show']);
Route::get('/public-weblist/{userId}', [UserWeblistController::class, 'publicList']);

        Route::apiResource('/my-weblist', UserWeblistController::class);
        Route::controller(UserWeblistDetailController::class)->group(function () {
            Route::post('/my-weblist/{id}/detail', 'updatedetail');
            Route::post('/my-weblist/{id}/images', 'storeimg')->middleware('throttle:10,1');
            Route::delete('/my-weblist/images/{imageId}', 'destroyimg');
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
