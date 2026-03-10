<?php

namespace App\Domain\UI\Schema;

use App\Domain\UI\Contracts\EmployeeTableSchemaInterface;

class GermanyEmployeeTableSchema implements EmployeeTableSchemaInterface
{
    public function columns(): array
    {
        return [
            ["key" => "name", "label" => "Name"],
            ["key" => "last_name", "label" => "Last Name"],
            ["key" => "salary", "label" => "Salary"],
            ["key" => "goal", "label" => "Goal"]
        ];
    }
}
