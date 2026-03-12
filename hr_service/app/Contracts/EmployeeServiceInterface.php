<?php

namespace App\Contracts;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EmployeeServiceInterface
{
    public function list(?string $country, int $page, int $perPage): LengthAwarePaginator;
    public function create(array $data): Employee;
    public function update(Employee $employee, array $data): Employee;
    public function delete(Employee $employee): bool;
    public function findById(int $id): ?Employee;
}
