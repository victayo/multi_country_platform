<?php

namespace App\Domain\UI\Schema;

use App\Domain\UI\Contracts\DashboardSchemaInterface;

class UsaDashboardSchema implements DashboardSchemaInterface
{
    public function widgets(): array
    {
        return [
            [
                "type" => "metric",
                "title" => "Employee Count",
                "data_source" => "/api/employees/count",
                "channel" => "dashboard.usa"
            ],
            [
                "type" => "metric",
                "title" => "Average Salary",
                "data_source" => "/api/dashboard/avg-salary",
                "channel" => "dashboard.usa"
            ],
            [
                "type" => "metric",
                "title" => "Completion Rate",
                "data_source" => "/api/checklists",
                "channel" => "checklist.usa"
            ]
        ];
    }
}
