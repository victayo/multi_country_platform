<?php

namespace Tests\Unit\Checklist;

use App\Domain\Checklist\Facades\CountryCache;
use App\Domain\Checklist\Services\ChecklistService;
use App\Domain\Employees\Contracts\EmployeeRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ChecklistServiceCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_summary_uses_cached_value_after_first_source_fetch(): void
    {
        CountryCache::forget('usa');

        $employeeRepository = Mockery::mock(EmployeeRepositoryInterface::class);
        $builder = Mockery::mock(Builder::class);

        $builder
            ->shouldReceive('get')
            ->once()
            ->andReturn(collect([
                [
                    'id' => 1,
                    'name' => 'John',
                    'last_name' => 'Doe',
                    'salary' => 50000,
                    'country' => 'usa',
                    'ssn' => '123-45-6789',
                    'address' => 'Main St',
                    'goal' => null,
                    'tax_id' => null,
                ],
            ]));

        $employeeRepository
            ->shouldReceive('findByCountry')
            ->once()
            ->with('usa')
            ->andReturn($builder);

        $this->app->instance(EmployeeRepositoryInterface::class, $employeeRepository);

        $service = $this->app->make(ChecklistService::class);

        $firstResult = $service->countrySummary('usa');
        $secondResult = $service->countrySummary('usa');

        $this->assertSame($firstResult, $secondResult);
        $this->assertTrue(CountryCache::hasCountry('usa'));
    }

    public function test_country_summary_returns_cached_value_without_hitting_source(): void
    {
        CountryCache::put('germany', [
            'total_employees' => 2,
            'completed' => 1,
            'completion_rate' => 50,
            'employees' => [
                ['employee_id' => 1, 'complete' => true, 'missing_fields' => [], 'completed_fields' => ['goal', 'tax_id']],
                ['employee_id' => 2, 'complete' => false, 'missing_fields' => [['field' => 'tax_id', 'message' => 'tax_id is required']], 'completed_fields' => ['goal']],
            ],
        ]);

        $employeeRepository = Mockery::mock(EmployeeRepositoryInterface::class);
        $employeeRepository
            ->shouldReceive('findByCountry')
            ->never();

        $this->app->instance(EmployeeRepositoryInterface::class, $employeeRepository);

        $service = $this->app->make(ChecklistService::class);

        $result = $service->countrySummary('germany');

        $this->assertSame(2, $result['total_employees']);
        $this->assertSame(50, $result['completion_rate']);
    }
}
