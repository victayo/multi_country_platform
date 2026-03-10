<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employee = $this->route('employee');
        $employeeId = is_object($employee) ? $employee->id : $employee;

        return [
            'name' => 'sometimes|required|string',
            'last_name' => 'sometimes|required|string',
            'salary' => 'sometimes|required|numeric|min:1',
            'country' => 'sometimes|required|in:USA,Germany',
            'ssn' => ['sometimes', 'nullable', 'required_if:country,USA', Rule::unique('employees', 'ssn')->ignore($employeeId)],
            'address' => 'sometimes|nullable|required_if:country,USA',
            'goal' => 'sometimes|nullable|required_if:country,Germany',
            'tax_id' => [
                'sometimes',
                'nullable',
                'required_if:country,Germany',
                'regex:/^DE\d{9}$/',
                Rule::unique('employees', 'tax_id')->ignore($employeeId),
            ],
        ];
    }
}
