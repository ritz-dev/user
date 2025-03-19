<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
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
        $employeeId = $this->route('employee');

        return [
            'name' => 'required|string|max:255',
            'gender' => 'required|string|in:male,female',
            'dob' => 'required|date',
            'address' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'registerCode' => 'required|integer',
            'email' => 'required|email',
            'imageUrl' => 'nullable|mimes:jpg,jpeg,png,gif|image|max:2048',
            'phonenumber' => 'required|string',
            'salary' => 'required|numeric|min:0',
            'department' => 'required|string|max:255',
            'hireDate' => 'required|date',
            'status' => 'required|string|in:active,inactive',
        ];
    }

    // public function messages(): array
    // {
    //     return [
    //         'name.required' => 'The name is required.',
    //         'email.required' => 'The email is required.',
    //         'email.unique' => 'The email has already been taken.',
    //         'email.email' => 'The email must be a valid email address.',
    //         'phonenumber.required' => 'The phone number is required.',
    //         'phonenumber.unique' => 'The phone number has already been taken.',
    //         'phonenumber.string' => 'The phone number must be a valid string.',
    //         'password.min' => 'The password must be at least 8 characters.',
    //         'role.exists' => 'The selected role is invalid.',
    //         'status.in' => 'The status must be either active or inactive.',
    //         'hireDate.date' => 'The hire date must be a valid date.',
    //     ];
    // }
}
