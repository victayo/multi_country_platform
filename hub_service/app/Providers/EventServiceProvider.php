<?php

namespace App\Providers;

use App\Jobs\EmployeeCreatedJob;
use App\Jobs\EmployeeDeletedJob;
use App\Jobs\EmployeeUpdatedJob;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        App::bindMethod(EmployeeCreatedJob::class . '@handle', fn ($job) => $job->handle());
        App::bindMethod(EmployeeUpdatedJob::class . '@handle', fn ($job) => $job->handle());
        App::bindMethod(EmployeeDeletedJob::class . '@handle', fn ($job) => $job->handle());
    }
}
