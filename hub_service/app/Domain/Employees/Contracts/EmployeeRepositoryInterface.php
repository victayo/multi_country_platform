<?php

namespace App\Domain\Employees\Contracts;


interface EmployeeRepositoryInterface
{
    public function storeFromEvent(array $employee): void;

    public function updateFromEvent(array $employee): void;

    public function deleteFromEvent(int $employeeId): void;

    public function findByCountry(string $country): \Illuminate\Database\Eloquent\Builder;
}
