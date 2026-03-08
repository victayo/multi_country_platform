<?php

namespace App\Services;

use App\Contracts\EmployeePublisherInterface;
use App\Contracts\EmployeeServiceInterface;
use App\Contracts\PublisherInterface;
use App\Models\Employee;
use Illuminate\Support\Str;

class EmployeeService implements EmployeeServiceInterface
{
    public function __construct(
        private readonly PublisherInterface $publisher
    ) {
    }

    public function create(array $data): Employee
    {
        $employee = Employee::create($data);
        $this->publisher->publish('EmployeeCreated', $this->employeePayload($employee));
        return $employee;
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);
        $this->publisher->publish('EmployeeUpdated', $this->employeePayload($employee));
        return $employee;
    }

    public function delete(Employee $employee): bool
    {
        $payload = $this->employeePayload($employee);
        $deleted = $employee->delete();

        if ($deleted) {
            $this->publisher->publish('EmployeeDeleted', $payload);
        }

        return $deleted;
    }

    public function findById(int $id): ?Employee
    {
        return Employee::find($id);
    }

    private function employeePayload(Employee $employee): array
    {
        $changedFields = array_keys($employee->getChanges());
        return [
            "event_type" => "EmployeeUpdated",
            "event_id" => Str::uuid(),
            "timestamp" => now()->toIso8601String(),
            "country" => $employee->country,
            "data" => [
                "employee_id" => $employee->id,
                "changed_fields" => $changedFields,
                "employee" => [
                    "id" => $employee->id,
                    "name" => $employee->name,
                    "last_name" => $employee->last_name,
                    "salary" => $employee->salary,
                    "ssn" => $employee->ssn,
                    "address" => $employee->address,
                    "country" => $employee->country
                ]
            ]
        ];
    }
}
