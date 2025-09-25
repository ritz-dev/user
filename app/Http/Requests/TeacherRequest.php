<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherRequest extends FormRequest
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
        $imageUrlRegex = '/^.*\.(jpg|jpeg|png|gif|bmp|webp)$/i';
        $phoneRegex = '/^\d+$/';

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
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', // At least 1 upper, 1 lower, 1 numeric, 1 special character
            ],
            'imageUrl' => 'nullable|mimes:jpg,jpeg,png,gif|image|max:2048',
            'phonenumber' => [
                'required',
                'string',
                'min:10',
                'max:15',
                "regex:$phoneRegex",
            ],
            'phonenumber' => 'required|string|unique:employees,phonenumber,' . $teacherId . ',id',
            'hireDate' => 'required|date',
            'status' => 'required|string',
            'specialization' => 'required|string',
            'designation' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is Required!',
            'gender.required' => 'Gender is required',
            'dob.required' => 'Date of Birth is required',
            'address.required' => 'Address is required',
            'state.required' => 'State is required',
            'district.required' => 'District is required',
            'registerCode.required' => 'Register code is required',
            'registerCode.positive' => 'Register code must be a positive number',
            'registerCode.integer' => 'Register code must be an integer',
            'email.required' => 'You must provide your email address',
            'email.email' => 'The provided email address format is not valid',
            'password.required' => 'Password is Required!',
            'password.regex' => 'Please create a stronger password. Hint: Min 8 characters, 1 Upper case letter, 1 Lower case letter, 1 Numeric digit.',
            'imageUrl.file_name.required' => 'File Name is required!',
            'imageUrl.file_name.regex' => 'File name can only contain letters, numbers, underscores, dashes, and dots.',
            'imageUrl.id.required' => 'ID is required!',
            'imageUrl.id.positive' => 'ID must be a positive number.',
            'imageUrl.original.required' => 'Original image URL is required!',
            'imageUrl.original.url' => 'Invalid URL format for original image.',
            'imageUrl.original.regex' => 'Original image URL must end with .jpg, .jpeg, .png, .gif, .bmp, or .webp',
            'imageUrl.thumbnail.required' => 'Thumbnail image URL is required!',
            'imageUrl.thumbnail.url' => 'Invalid URL format for thumbnail image.',
            'imageUrl.thumbnail.regex' => 'Thumbnail image URL must end with .jpg, .jpeg, .png, .gif, .bmp, or .webp',
            'phonenumber.required' => 'Phone number is required',
            'phonenumber.regex' => 'Phone number must contain only digits',
            'phonenumber.min' => 'Phone number must be at least 10 digits',
            'phonenumber.max' => 'Phone number must be at most 15 digits',
            'salary.required' => 'Salary is required!',
            'hireDate.required' => 'Hire Date is required.',
            'status.required' => 'Status is required',
            'specialization.required' => 'Specialization is required!',
            'designation.required' => 'Designation is required!',
        ];
    }
}
