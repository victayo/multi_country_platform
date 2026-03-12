<?php

namespace App\Domain\Employees\Services;

use App\Domain\Checklist\Facades\CountryCache;
use App\Domain\Checklist\Services\ChecklistService;
use App\Domain\Employees\Contracts\EmployeeRepositoryInterface;
use App\Events\ChecklistUpdatedBroadcast;
use App\Events\EmployeeUpdatedBroadcast;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

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
        $eventType = $eventData['event_type'] ?? $eventData['event'] ?? 'unknown';
        $eventId = $eventData['event_id'] ?? null;

        Log::info('Applying employee created event to hub service', [
            'event_type' => $eventType,
            'event_id' => $eventId,
            'employee_id' => $employee['id'] ?? null,
            'country' => $eventData['country'] ?? null,
        ]);

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

        Log::info('Employee created event applied in hub service', [
            'event_type' => $eventType,
            'event_id' => $eventId,
            'employee_id' => $employee['id'] ?? null,
        ]);
    }

    public function updateFromEvent(array $eventData): void
    {
        $employee = $eventData['data']['employee'];
        $eventType = $eventData['event_type'] ?? $eventData['event'] ?? 'unknown';
        $eventId = $eventData['event_id'] ?? null;
        $employeeId = (int) $employee['id'];

        Log::info('Applying employee updated event to hub service', [
            'event_type' => $eventType,
            'event_id' => $eventId,
            'employee_id' => $employeeId,
            'country' => $eventData['country'] ?? null,
        ]);

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
            Log::info('Broadcasting employee updated event', [
                'event_type' => $eventType,
                'event_id' => $eventId,
                'employee_id' => $employeeId,
                'broadcast_event' => EmployeeUpdatedBroadcast::class,
                'country' => $broadcastCountry,
            ]);

            broadcast(new EmployeeUpdatedBroadcast($employee, $broadcastCountry));
        }

        if ($newCountry === null) {
            Log::info('Employee updated event applied without checklist broadcast', [
                'event_type' => $eventType,
                'event_id' => $eventId,
                'employee_id' => $employeeId,
            ]);

            return;
        }

        $checklistSummary = $this->checklistService->countrySummary($newCountry);

        Log::info('Broadcasting checklist updated event', [
            'event_type' => $eventType,
            'event_id' => $eventId,
            'employee_id' => $employeeId,
            'broadcast_event' => ChecklistUpdatedBroadcast::class,
            'country' => $newCountry,
        ]);

        broadcast(new ChecklistUpdatedBroadcast($newCountry, $checklistSummary));

        Log::info('Employee updated event applied and broadcasts emitted', [
            'event_type' => $eventType,
            'event_id' => $eventId,
            'employee_id' => $employeeId,
            'country' => $newCountry,
        ]);
    }

    public function deleteFromEvent(array $eventData): void
    {
        $eventType = $eventData['event_type'] ?? $eventData['event'] ?? 'unknown';
        $eventId = $eventData['event_id'] ?? null;
        $employeeId = (int) $eventData['data']['employee']['id'];
        $country = $eventData['country'];

        Log::info('Applying employee deleted event to hub service', [
            'event_type' => $eventType,
            'event_id' => $eventId,
            'employee_id' => $employeeId,
            'country' => $country,
        ]);

        $this->employeeRepository->deleteFromEvent($employeeId);

        $this->forgetChecklistCacheForCountries([$country]);

        Log::info('Employee deleted event applied in hub service', [
            'event_type' => $eventType,
            'event_id' => $eventId,
            'employee_id' => $employeeId,
            'country' => $country,
        ]);
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
