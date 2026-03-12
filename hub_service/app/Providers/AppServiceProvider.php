<?php

namespace App\Providers;

use App\Domain\Checklist\Services\CountryCacheService;
use App\Domain\Employees\Contracts\EmployeeRepositoryInterface;
use App\Domain\Employees\Repository\EmployeeRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CountryCacheService::class);

        $this->app->bind(
            EmployeeRepositoryInterface::class,
            EmployeeRepository::class
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
