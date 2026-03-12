<?php

namespace App\Domain\Checklist\Validators;

use App\Domain\Checklist\Contracts\ChecklistValidatorInterface;

abstract class AbstractEmployeeChecklistValidator implements ChecklistValidatorInterface
{
    protected function getFillableFields(): array
    {
        return (new \App\Models\Employee())->getFillable();
    }

    protected function validateSalary(mixed $value): ?string
    {
        if (!is_numeric($value) || $value <= 0) {
            return 'salary must be a positive number';
        }

        return null;
    }
}
