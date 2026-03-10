<?php

namespace App\Checklist\Validators;

use App\Checklist\Contracts\ChecklistValidatorInterface;

class UsaChecklistValidator implements ChecklistValidatorInterface
{
    public function validate(array $employee): array
    {
        $missing = [];

        if (empty($employee['ssn'])) {
            $missing[] = 'ssn';
        }

        if (empty($employee['address'])) {
            $missing[] = 'address';
        }

        if (empty($employee['salary']) || $employee['salary'] <= 0) {
            $missing[] = 'salary';
        }

        return [
            'complete' => empty($missing),
            'missing' => $missing
        ];
    }
}
