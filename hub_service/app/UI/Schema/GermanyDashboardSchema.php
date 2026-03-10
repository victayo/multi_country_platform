<?php

namespace App\UI\Schema;

use App\UI\Contracts\DashboardSchemaInterface;

class GermanyDashboardSchema implements DashboardSchemaInterface
{
    public function widgets(): array
    {
        return [
            [
                "type" => "metric",
                "title" => "Employee Count",
                "data_source" => "/api/employees/count",
                "channel" => "dashboard.germany"
            ],
            [
                "type" => "metric",
                "title" => "Goal Tracking",
                "data_source" => "/api/goals",
                "channel" => "goals.germany"
            ]
        ];
    }
}
