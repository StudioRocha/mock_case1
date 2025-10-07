<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'verification_code' => 'required|string|size:6|regex:/^[0-9]{6}$/',
        ];
    }

    public function attributes()
    {
        return [
            'verification_code' => '認証コード',
        ];
    }

    public function messages()
    {
        return [
            'verification_code.required' => '認証コードを入力してください。',
            'verification_code.size' => '認証コードは6桁で入力してください。',
            'verification_code.regex' => '認証コードは6桁の数字で入力してください。',
        ];
    }
}