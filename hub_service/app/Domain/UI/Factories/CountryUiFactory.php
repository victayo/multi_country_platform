<?php

namespace App\Domain\UI\Factories;

use App\Domain\UI\Providers\GermanyStepsProvider;
use App\Domain\UI\Providers\UsaStepsProvider;
use App\Domain\UI\Schema\GermanyDashboardSchema;
use App\Domain\UI\Schema\GermanyEmployeeTableSchema;
use App\Domain\UI\Schema\UsaDashboardSchema;
use App\Domain\UI\Schema\UsaEmployeeTableSchema;
use Exception;

class CountryUiFactory
{
    public function stepsProvider(string $country)
    {
        $country = strtolower($country);
        return match ($country) {
            'usa' => app(UsaStepsProvider::class),
            'germany' => app(GermanyStepsProvider::class),
            default => throw new Exception("Unsupported country")
        };
    }

    public function employeeTable(string $country)
    {
        $country = strtolower($country);
        return match ($country) {
            'usa' => app(UsaEmployeeTableSchema::class),
            'germany' => app(GermanyEmployeeTableSchema::class),
            default => throw new Exception("Unsupported country")
        };
    }

    public function dashboardSchema(string $country)
    {
        $country = strtolower($country);
        return match ($country) {
            'usa' => app(UsaDashboardSchema::class),
            'germany' => app(GermanyDashboardSchema::class),
            default => throw new Exception("Unsupported country")
        };
    }
}
