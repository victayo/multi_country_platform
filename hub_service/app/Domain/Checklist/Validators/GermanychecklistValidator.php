<?php

namespace App\Domain\Checklist\Validators;

use App\Domain\Checklist\Contracts\ChecklistValidatorInterface;

class GermanyChecklistValidator extends AbstractEmployeeChecklistValidator
{
    public function validate(array $employee): array
    {
        $missing = [];
        $completed = [];
        $requiredFields = [
            'name',
            'last_name',
            'salary',
            'country',
            'goal',
            'tax_id',
        ];
        $requiredFields = array_values(array_intersect($requiredFields, $this->getFillableFields()));

        foreach ($requiredFields as $field) {
            $value = $employee[$field] ?? null;
            $validationMessage = $this->validateField($field, $value);

            if ($validationMessage !== null) {
                $missing[] = [
                    'field' => $field,
                    'message' => $validationMessage,
                ];
                continue;
            }

            $completed[] = $field;
        }

        return [
            'complete' => empty($missing),
            'missing' => $missing,
            'completed' => $completed,
        ];
    }

    private function validateField(string $field, mixed $value): ?string
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === null || $value === '') {
            return $field . ' is required';
        }

        return match ($field) {
            'salary' => $this->validateSalary($value),
            'tax_id' => $this->validateTaxId((string) $value),
            default => null,
        };
    }

    private function validateTaxId(string $taxId): ?string
    {
        if (!preg_match('/^DE\d{9}$/', trim($taxId))) {
            return 'tax_id must be in DE123456789 format';
        }

        return null;
    }
}
