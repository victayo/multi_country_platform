<?php

namespace Tests\Unit\Events;

use App\Domain\Employees\Services\EmployeeService;
use App\Jobs\EmployeeCreatedJob;
use App\Jobs\EmployeeDeletedJob;
use App\Jobs\EmployeeUpdatedJob;
use Mockery;
use Tests\TestCase;

class EmployeeEventJobsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_created_job_forwards_event_payload_to_employee_service(): void
    {
        $payload = $this->employeePayload('employee.created', 'usa');

        $service = Mockery::mock(EmployeeService::class);
        $service->shouldReceive('createFromEvent')->once()->with($payload);
        $this->app->instance(EmployeeService::class, $service);

        (new EmployeeCreatedJob($payload))->handle();

        $this->addToAssertionCount(1);
    }

    public function test_updated_job_forwards_event_payload_to_employee_service(): void
    {
        $payload = $this->employeePayload('employee.updated', 'germany');

        $service = Mockery::mock(EmployeeService::class);
        $service->shouldReceive('updateFromEvent')->once()->with($payload);
        $this->app->instance(EmployeeService::class, $service);

        (new EmployeeUpdatedJob($payload))->handle();

        $this->addToAssertionCount(1);
    }

    public function test_deleted_job_forwards_event_payload_to_employee_service(): void
    {
        $payload = $this->employeePayload('employee.deleted', 'usa');

        $service = Mockery::mock(EmployeeService::class);
        $service->shouldReceive('deleteFromEvent')->once()->with($payload);
        $this->app->instance(EmployeeService::class, $service);

        (new EmployeeDeletedJob($payload))->handle();

        $this->addToAssertionCount(1);
    }

    private function employeePayload(string $eventType, string $country): array
    {
        return [
            'event' => $eventType,
            'country' => $country,
            'data' => [
                'employee' => [
                    'id' => 1001,
                    'name' => 'Jane',
                    'last_name' => 'Smith',
                    'salary' => 85000,
                    'country' => $country,
                    'ssn' => '123-45-6789',
                    'address' => 'Berlin Street 1',
                    'goal' => 'Sales growth',
                    'tax_id' => 'DE123456789',
                ],
            ],
        ];
    }
}
