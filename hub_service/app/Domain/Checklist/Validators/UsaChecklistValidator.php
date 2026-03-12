<?php

namespace App\Domain\Checklist\Validators;

class UsaChecklistValidator extends AbstractEmployeeChecklistValidator
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
            'ssn',
            'address',
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
            'ssn' => $this->validateSsn((string) $value),
            'country' => $this->validateCountry((string) $value),
            default => null,
        };
    }

    private function validateSsn(string $ssn): ?string
    {
        // Accepts 123456789 and 123-45-6789.
        if (!preg_match('/^\d{3}-?\d{2}-?\d{4}$/', trim($ssn))) {
            return 'ssn must be in 123-45-6789 format';
        }

        return null;
    }

    private function validateCountry(string $country): ?string
    {
        $normalizedCountry = strtolower(trim($country));
        $allowedCountries = ['usa'];

        if (!in_array($normalizedCountry, $allowedCountries, true)) {
            return 'country must be USA';
        }

        return null;
    }
}
