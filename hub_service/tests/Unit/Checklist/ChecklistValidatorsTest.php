<?php

namespace Tests\Unit\Checklist;

use App\Domain\Checklist\Validators\GermanyChecklistValidator;
use App\Domain\Checklist\Validators\UsaChecklistValidator;
use PHPUnit\Framework\TestCase;

class ChecklistValidatorsTest extends TestCase
{
    public function test_usa_validator_marks_employee_complete_when_all_required_fields_are_valid(): void
    {
        $validator = new UsaChecklistValidator();

        $result = $validator->validate([
            'id' => 1,
            'name' => 'John',
            'last_name' => 'Doe',
            'salary' => 50000,
            'country' => 'usa',
            'ssn' => '123-45-6789',
            'address' => '123 Main St',
        ]);

        $this->assertTrue($result['complete']);
        $this->assertSame([], $result['missing']);
        $this->assertContains('ssn', $result['completed']);
        $this->assertContains('salary', $result['completed']);
    }

    public function test_usa_validator_detects_invalid_salary_and_ssn(): void
    {
        $validator = new UsaChecklistValidator();

        $result = $validator->validate([
            'id' => 1,
            'name' => 'John',
            'last_name' => 'Doe',
            'salary' => 0,
            'country' => 'usa',
            'ssn' => 'invalid',
            'address' => '123 Main St',
        ]);

        $this->assertFalse($result['complete']);

        $this->assertContains(
            ['field' => 'salary', 'message' => 'salary must be a positive number'],
            $result['missing']
        );

        $this->assertContains(
            ['field' => 'ssn', 'message' => 'ssn must be in 123-45-6789 format'],
            $result['missing']
        );
    }

    public function test_germany_validator_detects_missing_goal_and_invalid_tax_id(): void
    {
        $validator = new GermanyChecklistValidator();

        $result = $validator->validate([
            'id' => 2,
            'name' => 'Erika',
            'last_name' => 'Mustermann',
            'salary' => 62000,
            'country' => 'germany',
            'goal' => '',
            'tax_id' => '123456789',
        ]);

        $this->assertFalse($result['complete']);
        $this->assertContains(
            ['field' => 'goal', 'message' => 'goal is required'],
            $result['missing']
        );
        $this->assertContains(
            ['field' => 'tax_id', 'message' => 'tax_id must be in DE123456789 format'],
            $result['missing']
        );
    }
}
