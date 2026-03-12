<?php

namespace App\Http\Controllers;

use App\Contracts\EmployeeServiceInterface;
use App\DTO\ApiResponse;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\ApiResponseResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeServiceInterface $employeeService)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $country = $request->query('country');
        $page = max((int) $request->query('page', 1), 1);
        $perPage = (int) $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $employees = $this->employeeService->list($country, $page, $perPage);

        return $this->apiResponse(
            ApiResponse::success('Employees retrieved successfully', ['employees' => $employees], Response::HTTP_OK)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateEmployeeRequest $request)
    {
        $data = $request->validated();
        $employee = $this->employeeService->create($data);

        return $this->apiResponse(
            ApiResponse::success('Employee created successfully', ['employee' => $employee], Response::HTTP_CREATED)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employee = $this->employeeService->findById($id);
        if (!$employee) {
            return $this->apiResponse(
                ApiResponse::error('Employee not found', null, Response::HTTP_NOT_FOUND)
            );
        }

        return $this->apiResponse(
            ApiResponse::success('Employee retrieved successfully', ['employee' => $employee], Response::HTTP_OK)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();
        $updatedEmployee = $this->employeeService->update($employee, $data);

        return $this->apiResponse(
            ApiResponse::success('Employee updated successfully', ['employee' => $updatedEmployee], Response::HTTP_OK)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $this->employeeService->delete($employee);

        return $this->apiResponse(
            ApiResponse::success('Employee deleted successfully', null, Response::HTTP_OK)
        );
    }

    private function apiResponse(ApiResponse $apiResponse): JsonResponse
    {
        return (new ApiResponseResource($apiResponse))
            ->response()
            ->setStatusCode($apiResponse->statusCode ?? Response::HTTP_OK);
    }
}
