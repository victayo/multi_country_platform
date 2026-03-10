<?php

namespace App\Domain\Checklist\Validators;

use App\Domain\Checklist\Contracts\ChecklistValidatorInterface;

class GermanyChecklistValidator implements ChecklistValidatorInterface
{
    public function validate(array $employee): array
    {
        $requiredFields = [
            'name',
            'last_name',
            'salary',
            'country',
            'address',
        ];

        $missing = [];
        $complete = [];
        foreach ($requiredFields as $field) {
            if (empty($employee[$field])) {
                $missing[] = $field;
            }else {
                $complete[] = $field;
            }
        }

        if (empty($employee['goal'])) {
            $missing[] = 'goal';
        } else {
            $complete[] = 'goal';
        }

        if (empty($employee['salary']) || $employee['salary'] <= 0) {
            $missing[] = 'salary';
        } else {
            $complete[] = 'salary';
        }

        if (!preg_match('/^DE\d{9}$/', $employee['tax_id'] ?? '')) {
            $missing[] = 'tax_id';
        } else {
            $complete[] = 'tax_id';
        }

        return [
            'completed' => empty($missing),
            'missing' => $missing,
            'complete' => $complete
        ];
    }
}
