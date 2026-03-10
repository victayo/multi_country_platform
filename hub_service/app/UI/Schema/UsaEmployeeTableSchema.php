<?php

namespace App\UI\Schema;

use App\UI\Contracts\EmployeeTableSchemaInterface;

class UsaEmployeeTableSchema implements EmployeeTableSchemaInterface
{
    public function columns(): array
    {
        return [
            ["key" => "name", "label" => "Name"],
            ["key" => "last_name", "label" => "Last Name"],
            ["key" => "salary", "label" => "Salary"],
            ["key" => "ssn", "label" => "SSN", "masked" => true]
        ];
    }
}
