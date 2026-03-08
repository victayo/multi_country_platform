<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\EmployeeServiceInterface::class,
            \App\Services\EmployeeService::class
        );

        $this->app->bind(
            \App\Contracts\PublisherInterface::class,
            \App\Services\RabbitMQPublisher::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
