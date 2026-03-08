<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEmployeeRequest extends FormRequest
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
        return [
            'name' => 'required',
            'last_name' => 'required',
            'salary' => 'required|numeric|min:1',
            'country' => 'required|in:USA,Germany',
            'ssn' => 'nullable|required_if:country,USA|unique:employees,ssn',
            'address' => 'nullable|required_if:country,USA',
            'goal' => 'nullable|required_if:country,Germany',
            'tax_id' => ['nullable', 'required_if:country,Germany', 'regex:/^DE\d{9}$/', 'unique:employees,tax_id'],
        ];
    }
}
