<?php

namespace App\Domain\Employees\Services;

use App\Domain\Checklist\Facades\CountryCache;
use App\Domain\Checklist\Services\ChecklistService;
use App\Domain\Employees\Contracts\EmployeeRepositoryInterface;
use App\Events\ChecklistUpdatedBroadcast;
use App\Events\EmployeeUpdatedBroadcast;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

class EmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private ChecklistService $checklistService
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

        $this->forgetChecklistCacheForCountries([$employee['country']]);
    }

    public function updateFromEvent(array $eventData): void
    {
        $employee = $eventData['data']['employee'];
        $employeeId = (int) $employee['id'];
        $previousCountry = $this->findById($employeeId)?->country;

        $data = [
            'id' => $employeeId,
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

        $newCountry = $eventData['country'];
        $broadcastCountry = $newCountry ?? $this->normalizeCountry($eventData['country'] ?? null);

        $this->forgetChecklistCacheForCountries([$previousCountry, $newCountry]);

        if ($broadcastCountry !== null) {
            broadcast(new EmployeeUpdatedBroadcast($employee, $broadcastCountry));
        }

        if ($newCountry === null) {
            return;
        }

        $checklistSummary = $this->checklistService->countrySummary($newCountry);

        broadcast(new ChecklistUpdatedBroadcast($newCountry, $checklistSummary));
    }

    public function deleteFromEvent(array $eventData): void
    {
        $employeeId = (int) $eventData['data']['employee']['id'];
        $country = $eventData['country'];

        $this->employeeRepository->deleteFromEvent($employeeId);

        $this->forgetChecklistCacheForCountries([$country]);
    }

    private function normalizeCountry(?string $country): ?string
    {
        if ($country === null || trim($country) === '') {
            return null;
        }

        return strtolower(trim($country));
    }

    private function forgetChecklistCacheForCountries(array $countries): void
    {
        $uniqueCountries = array_unique(array_filter($countries));

        foreach ($uniqueCountries as $country) {
            CountryCache::forget($country);
        }
    }
}
