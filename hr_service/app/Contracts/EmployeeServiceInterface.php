<?php

namespace App\Contracts;

use App\Models\Employee;

interface EmployeeServiceInterface
{
    public function create(array $data): Employee;
    public function update(Employee $employee, array $data): Employee;
    public function delete(Employee $employee): bool;
    public function findById(int $id): ?Employee;
}
