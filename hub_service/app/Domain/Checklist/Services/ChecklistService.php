<?php

namespace App\Domain\Checklist\Services;

use App\Domain\Checklist\Factory\ChecklistValidatorFactory;

class ChecklistService
{
    public function __construct(
        private ChecklistValidatorFactory $factory
    ) {
    }

    public function evaluate(array $employees, string $country): array
    {
        $validator = $this->factory->make($country);

        $results = [];

        foreach ($employees as $employee) {

            $validation = $validator->validate($employee);

            $results[] = [
                'employee_id' => $employee['id'],
                'complete' => $validation['complete'],
                'missing_fields' => $validation['missing']
            ];
        }

        return $results;
    }
}
