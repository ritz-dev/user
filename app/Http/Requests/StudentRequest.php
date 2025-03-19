<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
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
            'name' => 'required|string',
            'email' => 'required|email',
            'phonenumber' => 'required|digits_between:10,15|regex:/^\d+$/',
            'address' => 'required|string',
            'state' => 'required|string',
            'district' => 'required|string',
            'registerCode' => 'required|integer|gt:0',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'pob' => 'required|string',
            'nationality' => 'required|string',
            'religion' => 'required|string',
            'blood_type' => 'required|string',
            'academic_level' => 'required|string',
            'academic_year' => 'required|string',
            'enrollment_date' => 'required|date',
            'graduation_date' => 'nullable|date',
            'status' => 'required|string',
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
