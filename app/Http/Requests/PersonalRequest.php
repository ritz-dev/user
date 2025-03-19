<?php

namespace App\Http\Requests;

use App\Models\Personal;
use Illuminate\Foundation\Http\FormRequest;

class PersonalRequest extends FormRequest
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
            "name" => "required|string",
            "gender" => "required|string",
            "dob" => "required|date",
            "address" => "required|string",
            'state' => 'nullable',
            'district' => 'nullable',
            'register_code' => 'nullable',
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
