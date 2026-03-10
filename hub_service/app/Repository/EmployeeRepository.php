<?php

namespace App\Repository;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;

class EmployeeRepository implements \App\Contracts\EmployeeRepositoryInterface
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

    public function findByCountry(string $country): Collection
    {
        return Employee::where('country', $country)->get();
    }
}
