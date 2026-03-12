<?php

namespace Tests\Unit;

use App\Jobs\EmployeeCreatedJob;
use App\Jobs\EmployeeDeletedJob;
use App\Jobs\EmployeeUpdatedJob;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmployeeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_by_id_caches_employee(): void
    {
        $employee = Employee::create($this->employeeData([
            'ssn' => '111-11-1111',
            'tax_id' => null,
        ]));

        $service = new EmployeeService();

        $first = $service->findById($employee->id);
        Employee::whereKey($employee->id)->update(['name' => 'Changed in DB']);
        $second = $service->findById($employee->id);

        $this->assertNotNull($first);
        $this->assertTrue(Cache::has("employee:{$employee->id}"));
        $this->assertSame($first->name, $second?->name);
    }

    public function test_list_uses_cache_and_create_invalidates_list_version(): void
    {
        Queue::fake();

        Employee::create($this->employeeData([
            'name' => 'John',
            'ssn' => '111-11-1111',
            'tax_id' => null,
        ]));
        Employee::create($this->employeeData([
            'name' => 'Anna',
            'ssn' => '222-22-2222',
            'tax_id' => null,
        ]));

        Cache::forever('employees:list:version', 1);

        $service = new EmployeeService();

        $firstPage = $service->list(null, 1, 15);
        Employee::create($this->employeeData([
            'name' => 'Inserted after cache',
            'ssn' => '333-33-3333',
            'tax_id' => null,
        ]));
        $cachedPage = $service->list(null, 1, 15);

        $service->create($this->employeeData([
            'name' => 'Created through service',
            'ssn' => '444-44-4444',
            'tax_id' => null,
        ]));

        $freshPage = $service->list(null, 1, 15);

        $this->assertSame(2, $firstPage->total());
        $this->assertSame(2, $cachedPage->total());
        $this->assertSame(4, $freshPage->total());
        $this->assertSame(2, Cache::get('employees:list:version'));
        Queue::assertPushed(EmployeeCreatedJob::class);
    }

    public function test_update_invalidates_employee_and_country_related_caches(): void
    {
        Queue::fake();

        $employee = Employee::create($this->employeeData([
            'country' => 'USA',
            'ssn' => '111-11-1111',
            'tax_id' => null,
        ]));

        Cache::put("employee:{$employee->id}", ['stale' => true], 600);
        Cache::put('checklist:USA', ['stale' => true], 600);
        Cache::put('checklist:Germany', ['stale' => true], 600);
        Cache::forever('employees:list:version', 5);

        $service = new EmployeeService();

        $service->update($employee, [
            'country' => 'Germany',
            'name' => 'Updated',
            'last_name' => 'Doe',
            'salary' => 91000,
            'ssn' => null,
            'address' => null,
            'goal' => 'Growth',
            'tax_id' => 'DE123456789',
        ]);

        $this->assertFalse(Cache::has("employee:{$employee->id}"));
        $this->assertFalse(Cache::has('checklist:USA'));
        $this->assertFalse(Cache::has('checklist:Germany'));
        $this->assertSame(6, Cache::get('employees:list:version'));
        Queue::assertPushed(EmployeeUpdatedJob::class);
    }

    public function test_delete_invalidates_caches_and_dispatches_deleted_job(): void
    {
        Queue::fake();

        $employee = Employee::create($this->employeeData([
            'country' => 'USA',
            'ssn' => '111-11-1111',
            'tax_id' => null,
        ]));

        Cache::put("employee:{$employee->id}", ['stale' => true], 600);
        Cache::put('checklist:USA', ['stale' => true], 600);
        Cache::forever('employees:list:version', 7);

        $service = new EmployeeService();

        $deleted = $service->delete($employee);

        $this->assertTrue($deleted);
        $this->assertFalse(Cache::has("employee:{$employee->id}"));
        $this->assertFalse(Cache::has('checklist:USA'));
        $this->assertSame(8, Cache::get('employees:list:version'));
        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
        Queue::assertPushed(EmployeeDeletedJob::class);
    }

    private function employeeData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'John',
            'last_name' => 'Doe',
            'salary' => 70000,
            'country' => 'USA',
            'ssn' => null,
            'address' => 'New York',
            'goal' => null,
            'tax_id' => null,
        ], $overrides);
    }
}
