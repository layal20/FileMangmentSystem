<?php

namespace App\Providers;

use App\Models\File;
use App\Models\Group;
use App\Observers\FileObserver;
use App\Observers\GroupObserver;
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
        Group::observe(GroupObserver::class);
        File::observe(FileObserver::class);

    }
}
