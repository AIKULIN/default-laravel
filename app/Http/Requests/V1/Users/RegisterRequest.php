<?php

namespace App\Http\Requests\V1\Users;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:6'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => '請輸入電子郵件地址。 ',
            'email.email' => '請輸入有效的電子郵件地址。 ',
            'email.unique' => '該電子郵件地址已被使用。 ',
            'phone.unique' => '該手機號碼已被使用。 ',
            'password.required' => '請輸入密碼。 ',
            'password.min' => '密碼不能少於6個字符。 ',
            'password.confirmed' => '兩次輸入的密碼不一致。 ',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->error('0x00000001', $validator->errors()));
    }
}
