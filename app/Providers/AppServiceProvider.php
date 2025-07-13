<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate; // Import Facade Gate
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    // Gate untuk admin
    Gate::define('admin', function ($user) {
        return $user->role === 'admin';
    });

    // Gate untuk user
    Gate::define('user', function ($user) {
        return $user->role === 'user';

});

    }
}