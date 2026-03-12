<?php

namespace App\Services;

use App\Contracts\EmployeeServiceInterface;
use App\Jobs\EmployeeCreatedJob;
use App\Jobs\EmployeeDeletedJob;
use App\Jobs\EmployeeUpdatedJob;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmployeeService implements EmployeeServiceInterface
{
    private const EMPLOYEE_LIST_VERSION_KEY = 'employees:list:version';

    /**
     * Return a cached employee list with optional country filtering.
     */
    public function list(?string $country, int $page, int $perPage): LengthAwarePaginator
    {
        $version = (int) Cache::get(self::EMPLOYEE_LIST_VERSION_KEY, 1);
        $countryKey = $country ?: 'all';
        $cacheKey = "employees:list:v{$version}:country:{$countryKey}:page:{$page}:per:{$perPage}";

        return Cache::remember($cacheKey, $this->employeeListTtlSeconds(), function () use ($country, $page, $perPage): LengthAwarePaginator {
            return Employee::query()
                ->when($country, fn ($query) => $query->where('country', $country))
                ->orderBy('id')
                ->paginate($perPage, ['*'], 'page', $page);
        });
    }

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

        $this->invalidateEmployeeCaches($employee->id, null, $employee->country);
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
        $previousCountry = $employee->country;
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

        $this->invalidateEmployeeCaches($employee->id, $previousCountry, $employee->country);
        EmployeeUpdatedJob::dispatch($payload);
        return $employee;
    }

    /**
     * Delete an employee and dispatch the deletion event job when successful.
     */
    public function delete(Employee $employee): bool
    {
        $country = $employee->country;
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

            $this->invalidateEmployeeCaches($employee->id, $country, null);
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
        $cacheKey = "employee:{$id}";

        return Cache::remember($cacheKey, $this->employeeDetailTtlSeconds(), fn () => Employee::find($id));
    }

    /**
     * Invalidate employee detail and list caches, including dependent checklist summaries.
     */
    private function invalidateEmployeeCaches(int $employeeId, ?string $previousCountry, ?string $currentCountry): void
    {
        Cache::forget("employee:{$employeeId}");

        $version = (int) Cache::get(self::EMPLOYEE_LIST_VERSION_KEY, 1);
        Cache::forever(self::EMPLOYEE_LIST_VERSION_KEY, $version + 1);

        $this->forgetChecklistCache($previousCountry);

        if ($currentCountry !== $previousCountry) {
            $this->forgetChecklistCache($currentCountry);
        }
    }

    private function forgetChecklistCache(?string $country): void
    {
        if (!$country) {
            return;
        }

        Cache::forget("checklist:{$country}");
    }

    private function employeeListTtlSeconds(): int
    {
        return ((int) config('cache_ttl.employee_list_pages', 5)) * 60;
    }

    private function employeeDetailTtlSeconds(): int
    {
        return ((int) config('cache_ttl.employee_detail', 2)) * 60;
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
