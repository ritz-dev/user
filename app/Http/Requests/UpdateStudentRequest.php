<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
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
        $id = $this->route('student');

        return [
            "name" => "required|string",
            "email" => 'required|email|unique:employees,email,' . $id . ',id',
            "phonenumber" => 'required|string|unique:employees,phonenumber,' . $id . ',id',
            'address' => 'required|string|max:500',
            'state' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'registerCode' => 'required|integer',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'pob' => 'required|string|max:255',
            'nationality' => 'required|string|max:100',
            'religion' => 'required|string|max:50',
            'blood_type' => 'required|in:A,B,AB,O',
            'academic_level' => 'required|string|max:100',
            'academic_year' => 'required|string|max:100',
            'image_url' => 'nullable|mimes:jpg,jpeg,png,gif|image|max:2048',
            'enrollment_date' => 'required|date',   
            'graduation_date' => 'nullable|date',
            'status' => 'required|in:active,graduated,suspended,dropped',
            'parent_info' => 'required|array|min:1',
            'parent_info.*.name' => 'required|string',
            'parent_info.*.title' => 'required|string',
            'parent_info.*.email' => 'required|email',
            'parent_info.*.phonenumber' => 'required|digits_between:10,15|regex:/^\d+$/',
            'parent_info.*.state' => 'required|string',
            'parent_info.*.district' => 'required|string',
            'parent_info.*.registerCode' => 'required|integer|gt:0',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->only(['state', 'district', 'register_code']);
            $filled = array_filter($data);

            // If not all are null or all are filled, add an error
            if (count($filled) > 0 && count($filled) < count($data)) {
                $validator->errors()->add('state', 'All fields (state, district, register_code) must be null or filled together.');
            }
        });
    }
}
