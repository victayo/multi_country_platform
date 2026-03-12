<?php

namespace App\Domain\Checklist\Services;

use App\Domain\Checklist\Facades\CountryCache;
use App\Domain\Checklist\Factory\ChecklistValidatorFactory;
use App\Domain\Employees\Contracts\EmployeeRepositoryInterface;

class ChecklistService
{
    public function __construct(
        private ChecklistValidatorFactory $factory,
        private EmployeeRepositoryInterface $employeeRepository,
        private ChecklistAggregator $aggregator

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
                'missing_fields' => $validation['missing'],
                'completed_fields' => $validation['completed']
            ];
        }

        return $results;
    }

    public function countrySummary(string $country): array
    {
        $country = strtolower($country);
        $key = "checklist:$country";
        if (CountryCache::hasCountry($country)) {
            return CountryCache::get($country);
        }

        $employees = $this->employeeRepository
                    ->findByCountry($country)
                    ->get();
        $evaluations = $this->evaluate($employees->toArray(), $country);

        $result = $this->aggregator
                    ->aggregate($evaluations);

        CountryCache::put($country, $result);

        return $result;
    }
}
