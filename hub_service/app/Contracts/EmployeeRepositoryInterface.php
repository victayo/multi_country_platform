<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface EmployeeRepositoryInterface
{
    public function storeFromEvent(array $employee): void;

    public function updateFromEvent(array $employee): void;

    public function deleteFromEvent(int $employeeId): void;

    public function findByCountry(string $country): Collection;
}
