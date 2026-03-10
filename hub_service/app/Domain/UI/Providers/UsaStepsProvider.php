<?php

namespace App\Domain\UI\Providers;

use App\Domain\UI\Contracts\StepsProviderInterface;

class UsaStepsProvider implements StepsProviderInterface
{
    public function steps(): array
    {
        return [
            [
                "id" => "dashboard",
                "label" => "Dashboard",
                "icon" => "dashboard",
                "path" => "/dashboard",
                "order" => 1
            ],
            [
                "id" => "employees",
                "label" => "Employees",
                "icon" => "users",
                "path" => "/employees",
                "order" => 2
            ]
        ];
    }
}
