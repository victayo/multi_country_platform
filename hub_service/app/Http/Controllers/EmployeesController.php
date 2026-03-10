<?php

namespace App\Http\Controllers;

use App\Domain\Employees\Contracts\EmployeeRepositoryInterface;
use App\Http\Requests\CountryRequest;
use App\Domain\UI\Factories\CountryUiFactory;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    public function __construct(
        private EmployeeRepositoryInterface $repository,
        private CountryUiFactory $factory
    ) {}

    public function index(CountryRequest $request)
    {
        $country = $request->query('country');

        $schema = $this->factory->employeeTable($country);

        $employees = $this->repository
            ->findByCountry($country)
            ->paginate(10);

        return response()->json([
            "columns" => $schema->columns(),
            "data" => $employees->items(),
            "pagination" => [
                "total" => $employees->total(),
                "page" => $employees->currentPage()
            ]
        ]);
    }
}
