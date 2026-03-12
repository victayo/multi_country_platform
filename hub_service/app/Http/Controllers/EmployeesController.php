<?php

namespace App\Http\Controllers;

use App\DTO\ApiResponse;
use App\Domain\Employees\Contracts\EmployeeRepositoryInterface;
use App\Http\Requests\CountryRequest;
use App\Domain\UI\Factories\CountryUiFactory;
use App\Http\Resources\ApiResponseResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class EmployeesController extends Controller
{
    public function __construct(
        private EmployeeRepositoryInterface $repository,
        private CountryUiFactory $factory
    ) {}

    public function index(CountryRequest $request): JsonResponse
    {
        $country = $request->query('country');

        $schema = $this->factory->employeeTable($country);

        $employees = $this->repository
            ->findByCountry($country)
            ->paginate(10);

        return $this->apiResponse(
            ApiResponse::success('Employees retrieved successfully', [
                'columns' => $schema->columns(),
                'data' => $employees->items(),
                'pagination' => [
                    'total' => $employees->total(),
                    'page' => $employees->currentPage(),
                ],
            ], Response::HTTP_OK)
        );
    }

    private function apiResponse(ApiResponse $apiResponse): JsonResponse
    {
        return (new ApiResponseResource($apiResponse))
            ->response()
            ->setStatusCode($apiResponse->statusCode ?? Response::HTTP_OK);
    }
}
