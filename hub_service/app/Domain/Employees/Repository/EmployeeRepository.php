<?php

namespace App\Domain\Employees\Repository;

use App\Domain\Employees\Contracts\EmployeeRepositoryInterface;
use App\Models\Employee;
// use Illuminate\Database\Query\Builder;

class EmployeeRepository implements EmployeeRepositoryInterface
{
     public function storeFromEvent(array $employee): void
    {
        Employee::create($employee);
    }

    public function updateFromEvent(array $employee): void
    {
        Employee::where('id', $employee['id'])
            ->update($employee);
    }

    public function deleteFromEvent(int $employeeId): void
    {
        Employee::where('id', $employeeId)->delete();
    }

    public function findByCountry(string $country): \Illuminate\Database\Eloquent\Builder
    {
        return Employee::where('country', $country);
    }
}
