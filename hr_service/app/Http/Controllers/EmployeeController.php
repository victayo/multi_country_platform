<?php

namespace App\Http\Controllers;

use App\Contracts\EmployeeServiceInterface;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
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

        return response()->json(['employees' => $employees]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateEmployeeRequest $request)
    {
        $data = $request->validated();
        $employee = $this->employeeService->create($data);
        return response()->json(['employee' => $employee], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employee = $this->employeeService->findById($id);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['employee' => $employee]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();
        $updatedEmployee = $this->employeeService->update($employee, $data);
        return response()->json(['employee' => $updatedEmployee], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $this->employeeService->delete($employee);
        return response()->json(['message' => 'Employee deleted successfully'], Response::HTTP_OK);
    }
}
