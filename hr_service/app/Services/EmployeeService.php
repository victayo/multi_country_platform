<?php

namespace App\Services;

use App\Contracts\EmployeeServiceInterface;
use App\Jobs\EmployeeCreatedJob;
use App\Jobs\EmployeeDeletedJob;
use App\Jobs\EmployeeUpdatedJob;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmployeeService implements EmployeeServiceInterface
{
    /**
     * Create a new employee and dispatch the creation event job.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Employee
    {
        $employee = Employee::create($data);
        Log::info('Employee created', [
            'employee_id' => $employee->id,
            'country' => $employee->country,
        ]);

        $payload = $this->employeePayload($employee, 'EmployeeCreated');
        Log::debug('Dispatching employee created job', [
            'employee_id' => $employee->id,
            'event_type' => $payload['event_type'],
            'event_id' => $payload['event_id'],
        ]);

        EmployeeCreatedJob::dispatch($payload);
        return $employee;
    }

    /**
     * Update an employee and dispatch the update event job.
     *
     * @param array<string, mixed> $data
     */
    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);
        Log::info('Employee updated', [
            'employee_id' => $employee->id,
            'country' => $employee->country,
            'changed_fields' => array_keys($employee->getChanges()),
        ]);

        $payload = $this->employeePayload($employee, 'EmployeeUpdated');
        Log::debug('Dispatching employee updated job', [
            'employee_id' => $employee->id,
            'event_type' => $payload['event_type'],
            'event_id' => $payload['event_id'],
        ]);

        EmployeeUpdatedJob::dispatch($payload);
        return $employee;
    }

    /**
     * Delete an employee and dispatch the deletion event job when successful.
     */
    public function delete(Employee $employee): bool
    {
        $payload = $this->employeePayload($employee, 'EmployeeDeleted');
        $deleted = $employee->delete();

        if ($deleted) {
            Log::info('Employee deleted', [
                'employee_id' => $employee->id,
                'country' => $employee->country,
            ]);

            Log::debug('Dispatching employee deleted job', [
                'employee_id' => $employee->id,
                'event_type' => $payload['event_type'],
                'event_id' => $payload['event_id'],
            ]);

            EmployeeDeletedJob::dispatch($payload);
        } else {
            Log::warning('Employee delete failed', [
                'employee_id' => $employee->id,
                'country' => $employee->country,
            ]);
        }

        return $deleted;
    }

    /**
     * Find an employee by its primary key.
     */
    public function findById(int $id): ?Employee
    {
        $employee = Employee::find($id);
        return $employee;
    }

    /**
     * Build an event payload for employee synchronization jobs.
     *
     * @return array<string, mixed>
     */
    private function employeePayload(Employee $employee, string $eventType): array
    {
        $changedFields = collect($employee->getChanges())
            ->keys()
            ->reject(fn($field) => $field === 'updated_at') // Exclude updated_at from changed fields
            ->values()
            ->toArray();
        $payload = [
            'event_type' => $eventType,
            'event_id' => (string) Str::uuid(),
            'timestamp' => now()->toIso8601String(),
            'country' => $employee->country,
            'data' => [
                'employee_id' => $employee->id,
                'changed_fields' => $changedFields,
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'last_name' => $employee->last_name,
                    'salary' => $employee->salary,
                    'ssn' => $employee->ssn,
                    'address' => $employee->address,
                    'country' => $employee->country,
                ],
            ],
        ];

        Log::debug('Built employee event payload', [
            'employee_id' => $employee->id,
            'event_type' => $payload['event_type'],
            'changed_fields' => $changedFields,
        ]);

        return $payload;
    }
}
