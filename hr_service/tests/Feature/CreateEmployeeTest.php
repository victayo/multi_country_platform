<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateEmployeeTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $payload = [
            'name' => 'John',
            'last_name' => 'Doe',
            'salary' => 70000,
            'country' => 'USA',
            'ssn' => '123-45-6789',
            'address' => 'New York',
            'goal' => 'goal',
            'tax_id' => 'tax_id',
        ];

        $response = $this->postJson('/api/employees', $payload);

        $response->assertCreated();

        $this->assertDatabaseHas('employees', [
            'name' => 'John',
            'country' => 'USA'
        ]);
    }
}
