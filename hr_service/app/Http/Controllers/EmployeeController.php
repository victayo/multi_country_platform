<?php

namespace App\Http\Controllers;

use App\Contracts\EmployeeServiceInterface;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Symfony\Component\HttpFoundation\Response;

class EmployeeController extends ApiController
{
    public function __construct(private EmployeeServiceInterface $employeeService)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateEmployeeRequest $request)
    {
        $data = $request->validated();
        $employee = $this->employeeService->create($data);
        return $this->respondSuccess($employee, 'Employee created successfully', Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employee = $this->employeeService->findById($id);
        if (!$employee) {
            return $this->respondError('Employee not found', null, Response::HTTP_NOT_FOUND);
        }
        return $this->respondSuccess($employee, 'Employee retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();
        $updatedEmployee = $this->employeeService->update($employee, $data);
        return $this->respondSuccess($updatedEmployee, 'Employee updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $this->employeeService->delete($employee);
        return $this->respondSuccess(null, 'Employee deleted successfully');
    }
}
