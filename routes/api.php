<?php

use Illuminate\Http\Request;
use App\Http\Middleware\IsUser;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;

use App\Http\Controllers\Api\User\{
    UserController,
    UserWeblistController,
    UserWeblistDetailController,
};

use App\Http\Controllers\Api\Admin\{
    AdminController,
    AdminWeblistController,
    AdminWeblistDetailController,
    AdminWeblistImageController
};

Route::prefix('v1')->group(function () {

    Route::fallback(function () {
        return response()->json(['message' => 'Endpoint tidak ditemukan.'], 404);
    });

    Route::middleware('throttle:5,1')->group(function () {

        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [UserController::class, 'register']);
    });

Route::get('/explore-weblist', [AdminWeblistController::class, 'index']);
    Route::middleware(['auth:sanctum', IsUser::class])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        
        Route::get('/explore-weblist/{id}', [AdminWeblistController::class, 'show']);

        Route::get('/category', [CategoryController::class, 'index']);

        Route::get('/profile', [UserController::class, 'profile']);

        Route::put('/profile/update', [UserController::class, 'updateProfile']);

        Route::apiResource('/my-weblist', UserWeblistController::class);

        Route::post('/weblist/{id}/like', [UserWeblistDetailController::class, 'like']);

        Route::post('/my-weblist/{id}/detail', [UserWeblistDetailController::class, 'updatedetail']);
        Route::post('/my-weblist/{id}/images', [UserWeblistDetailController::class, 'storeimg'])->middleware('throttle:10,1');
        Route::delete('/my-weblist/images/{imageId}', [UserWeblistDetailController::class, 'destroyimg']);
    });

    Route::middleware(['auth:sanctum', IsAdmin::class])->prefix('admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AdminController::class, 'profile']);

        Route::put('/profile/update', [AdminController::class, 'updateProfile']);

        Route::get('/users', [UserController::class, 'getAllUsers']);

        Route::delete('/users/{id}', [UserController::class, 'deleteUser']);

        Route::apiResource('/category', CategoryController::class);

        Route::apiResource('/weblist', AdminWeblistController::class);
    });
});
