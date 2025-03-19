<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
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

        $teacherId = $this->route('teacher'); 

        return [
            'name' => 'required|string|max:255',
            'gender' => 'required|string|in:male,female,other',  
            'dob' => 'required|date',
            'address' => 'required|string|max:255',     
            'state' => 'required|string',
            'district' => 'required|string|max:255',       
            'registerCode' => 'required|integer',
            'email' => 'required|email|unique:employees,email,' . $teacherId . ',id',
            // 'password' => [
            //     'required',
            //     'string',
            //     'min:8',
            //     'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', // At least 1 upper, 1 lower, 1 numeric, 1 special character
            // ],
            'imageUrl' => 'nullable|mimes:jpg,jpeg,png,gif|image|max:2048',
            'phonenumber' => 'required|string|unique:employees,phonenumber,' . $teacherId . ',id',
            'hireDate' => 'required|date',
            'status' => 'required|string',
            'specialization' => 'required|string',
            'designation' => 'required|string',
        ];
    }
}
