<?php

namespace App\Domain\Employees\Services;

use App\Domain\Employees\Contracts\EmployeeRepositoryInterface;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class EmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {
    }

    public function findByCountry(string $country): Builder
    {
        return $this->employeeRepository->findByCountry($country);
    }

    public function findById(int $id): ?Employee
    {
        $employee = Employee::find($id);
        return $employee;
    }

    public function createFromEvent(array $eventData): void
    {
        $employee = $eventData['data']['employee'];
        $data = [
            'id' => $employee['id'],
            'name' => $employee['name'],
            'last_name' => $employee['last_name'],
            'salary' => $employee['salary'],
            'country' => $employee['country'],
            'ssn' => $employee['ssn'] ?? null,
            'address' => $employee['address'] ?? null,
            'goal' => $employee['goal'] ?? null,
            'tax_id' => $employee['tax_id'] ?? null,
        ];
        $this->employeeRepository->storeFromEvent($data);
    }

    public function updateFromEvent(array $eventData): void
    {
        $country = strtolower($eventData['country']);
        Cache::forget("checklist:$country");
        $employee = $eventData['data']['employee'];
        $data = [
            'id' => $employee['id'],
            'name' => $employee['name'],
            'last_name' => $employee['last_name'],
            'salary' => $employee['salary'],
            'country' => $employee['country'],
            'ssn' => $employee['ssn'] ?? null,
            'address' => $employee['address'] ?? null,
            'goal' => $employee['goal'] ?? null,
            'tax_id' => $employee['tax_id'] ?? null,
        ];
        $this->employeeRepository->updateFromEvent($data);
    }

    public function deleteFromEvent(int $employeeId): void
    {
        $this->employeeRepository->deleteFromEvent($employeeId);
    }
}
