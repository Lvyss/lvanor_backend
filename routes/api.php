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


    Route::middleware(['auth:sanctum', IsUser::class])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/explore-weblist', [AdminWeblistController::class, 'index']);
        Route::get('/explore-weblist/{id}', [AdminWeblistController::class, 'show']);

        Route::get('/category', [CategoryController::class, 'index']);

        Route::get('/profile', [UserController::class, 'profile']);

        Route::post('/profile/update', [UserController::class, 'updateProfile']);

        Route::get('/my-weblist', [UserWeblistController::class, 'index']);
        Route::post('/my-weblist', [UserWeblistController::class, 'store']);
        Route::get('/my-weblist/{id}', [UserWeblistController::class, 'show']); 
        Route::post('/my-weblist/{id}', [UserWeblistController::class, 'update']);
        Route::delete('/my-weblist/{id}', [UserWeblistController::class, 'destroy']);

        Route::post('/my-weblist/{id}/detail', [UserWeblistDetailController::class, 'updatedetail']);
        Route::post('/my-weblist/{id}/images', [UserWeblistDetailController::class, 'storeimg'])->middleware('throttle:10,1');
        Route::delete('/my-weblist/images/{imageId}', [UserWeblistDetailController::class, 'destroyimg']);
    });
 Route::apiResource('/weblist', AdminWeblistController::class);
    Route::middleware(['auth:sanctum', IsAdmin::class])->prefix('admin')->group(function () {

        Route::get('/profile', [AdminController::class, 'profile']);

        Route::post('/profile/update', [AdminController::class, 'updateProfile']);

        Route::get('/users', [UserController::class, 'getAllUsers']);

        Route::delete('/users/{id}', [UserController::class, 'deleteUser']);

        Route::apiResource('/category', CategoryController::class);

       

        Route::post('weblist/{id}/detail', [AdminWeblistDetailController::class, 'update']);

        Route::delete('weblist/images/{imageId}', [AdminWeblistImageController::class, 'destroy']);
    });
});
