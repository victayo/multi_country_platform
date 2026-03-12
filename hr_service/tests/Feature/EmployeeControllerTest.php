<?php

namespace Tests\Feature;

use App\Contracts\EmployeeServiceInterface;
use App\Http\Controllers\EmployeeController;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class EmployeeControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_returns_paginated_employees_from_service(): void
    {
        $mockService = Mockery::mock(EmployeeServiceInterface::class);
        $employees = collect([
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Anna'],
        ]);

        $mockService->shouldReceive('list')
            ->once()
            ->with('USA', 2, 20)
            ->andReturn(new LengthAwarePaginator($employees, 2, 20, 2));

        $controller = new EmployeeController($mockService);
        $request = Request::create('/api/employees', 'GET', [
            'country' => 'USA',
            'page' => 2,
            'per_page' => 20,
        ]);

        $response = $controller->index($request);

        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('employees', $payload);
        $this->assertSame(2, $payload['employees']['current_page']);
        $this->assertCount(2, $payload['employees']['data']);
    }

    public function test_store_returns_created_employee_payload(): void
    {
        $mockService = Mockery::mock(EmployeeServiceInterface::class);
        $data = [
            'name' => 'John',
            'last_name' => 'Doe',
            'salary' => 70000,
            'country' => 'USA',
            'ssn' => '123-45-6789',
            'address' => 'New York',
            'goal' => null,
            'tax_id' => null,
        ];

        $employee = new Employee($data);
        $employee->id = 10;

        $request = Mockery::mock(CreateEmployeeRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn($data);

        $mockService->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($employee);

        $controller = new EmployeeController($mockService);

        $response = $controller->store($request);

        $payload = $response->getData(true);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame(10, $payload['employee']['id']);
        $this->assertSame('John', $payload['employee']['name']);
    }

    public function test_show_returns_not_found_when_employee_does_not_exist(): void
    {
        $mockService = Mockery::mock(EmployeeServiceInterface::class);
        $mockService->shouldReceive('findById')
            ->once()
            ->with('123')
            ->andReturnNull();

        $controller = new EmployeeController($mockService);

        $response = $controller->show('123');

        $payload = $response->getData(true);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Employee not found', $payload['error']);
    }

    public function test_update_returns_updated_employee_payload(): void
    {
        $mockService = Mockery::mock(EmployeeServiceInterface::class);
        $employee = new Employee([
            'name' => 'John',
            'last_name' => 'Doe',
            'salary' => 70000,
            'country' => 'USA',
        ]);
        $employee->id = 42;

        $data = ['salary' => 80000];

        $request = Mockery::mock(UpdateEmployeeRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn($data);

        $updatedEmployee = clone $employee;
        $updatedEmployee->salary = 80000;

        $mockService->shouldReceive('update')
            ->once()
            ->with($employee, $data)
            ->andReturn($updatedEmployee);

        $controller = new EmployeeController($mockService);

        $response = $controller->update($request, $employee);

        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(80000, $payload['employee']['salary']);
    }

    public function test_destroy_returns_success_message(): void
    {
        $mockService = Mockery::mock(EmployeeServiceInterface::class);
        $employee = new Employee([
            'name' => 'John',
            'last_name' => 'Doe',
            'salary' => 70000,
            'country' => 'USA',
        ]);
        $employee->id = 55;

        $mockService->shouldReceive('delete')
            ->once()
            ->with($employee)
            ->andReturnTrue();

        $controller = new EmployeeController($mockService);

        $response = $controller->destroy($employee);

        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Employee deleted successfully', $payload['message']);
    }
}
