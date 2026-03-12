<?php

namespace Tests\Feature\Integration;

use App\Domain\Checklist\Facades\CountryCache;
use App\Events\ChecklistUpdatedBroadcast;
use App\Events\EmployeeUpdatedBroadcast;
use App\Jobs\EmployeeUpdatedJob;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class EmployeeUpdatedEventFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_updated_job_refreshes_cache_and_broadcasts_updates(): void
    {
        Event::fake([
            EmployeeUpdatedBroadcast::class,
            ChecklistUpdatedBroadcast::class,
        ]);

        Employee::create([
            'id' => 1,
            'name' => 'John',
            'last_name' => 'Doe',
            'salary' => 55000,
            'country' => 'usa',
            'ssn' => '123-45-6789',
            'address' => 'Old Address',
            'goal' => null,
            'tax_id' => null,
        ]);

        CountryCache::put('usa', [
            'total_employees' => 99,
            'completed' => 0,
            'completion_rate' => 0,
            'employees' => [],
        ]);

        $payload = [
            'event' => 'employee.updated',
            'country' => 'usa',
            'data' => [
                'employee' => [
                    'id' => 1,
                    'name' => 'John',
                    'last_name' => 'Doe',
                    'salary' => 65000,
                    'country' => 'usa',
                    'ssn' => '123-45-6789',
                    'address' => 'New Address',
                ],
            ],
        ];

        (new EmployeeUpdatedJob($payload))->handle();

        $this->assertTrue(CountryCache::hasCountry('usa'));
        $this->assertSame(1, CountryCache::get('usa')['total_employees']);

        Event::assertDispatched(EmployeeUpdatedBroadcast::class, function (EmployeeUpdatedBroadcast $event) {
            return $event->country === 'usa'
                && $event->employee['id'] === 1
                && $event->employee['salary'] === 65000;
        });

        Event::assertDispatched(ChecklistUpdatedBroadcast::class, function (ChecklistUpdatedBroadcast $event) {
            return $event->country === 'usa'
                && isset($event->summary['total_employees'])
                && isset($event->summary['completion_rate'])
                && $event->summary['total_employees'] >= 1;
        });
    }
}
