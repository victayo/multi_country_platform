<?php

namespace Tests\Feature\Api;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_checklists_endpoint_returns_summary_for_country(): void
    {
        Employee::create([
            'id' => 11,
            'name' => 'Anna',
            'last_name' => 'Fisher',
            'salary' => 58000,
            'country' => 'usa',
            'ssn' => '123-45-6789',
            'address' => '1 Broadway',
            'goal' => null,
            'tax_id' => null,
        ]);

        Employee::create([
            'id' => 12,
            'name' => 'Mark',
            'last_name' => 'Lee',
            'salary' => 0,
            'country' => 'usa',
            'ssn' => 'invalid',
            'address' => '2 Broadway',
            'goal' => null,
            'tax_id' => null,
        ]);

        $response = $this->getJson('/api/checklists?country=usa');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_employees',
                    'completed',
                    'completion_rate',
                    'employees' => [
                        '*' => ['employee_id', 'complete', 'missing_fields', 'completed_fields'],
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Checklist summary retrieved successfully',
                'data' => [
                    'total_employees' => 2,
                    'completed' => 1,
                    'completion_rate' => 50,
                ],
            ]);
    }

    public function test_checklists_endpoint_requires_supported_country(): void
    {
        $response = $this->getJson('/api/checklists?country=spain');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['country']);
    }

    public function test_steps_endpoint_returns_country_specific_steps(): void
    {
        $response = $this->getJson('/api/steps?country=germany');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Steps retrieved successfully')
            ->assertJsonCount(3, 'data')
            ->assertJsonFragment(['id' => 'documentation']);
    }

    public function test_employees_endpoint_returns_paginated_employee_data(): void
    {
        Employee::create([
            'id' => 21,
            'name' => 'Greta',
            'last_name' => 'Klein',
            'salary' => 64000,
            'country' => 'germany',
            'ssn' => null,
            'address' => null,
            'goal' => 'Expand market',
            'tax_id' => 'DE123456789',
        ]);

        $response = $this->getJson('/api/employees?country=germany');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'columns',
                    'data',
                    'pagination' => ['total', 'page'],
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Employees retrieved successfully')
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.data.0.id', 21);
    }
}
