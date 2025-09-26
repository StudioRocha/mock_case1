<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'avatar' => 'nullable|mimes:jpeg,png',
            'username' => 'required|string|max:20',
            'postal_code' => ['required','regex:/^\d{3}-\d{4}$/'],
            'address' => 'required|string',
            'building_name' => 'nullable|string',
        ];
    }

    public function attributes()
    {
        return [
            'avatar' => 'プロフィール画像',
            'username' => 'ユーザー名',
            'postal_code' => '郵便番号',
            'address' => '住所',
            'building_name' => '建物名',
        ];
    }
}
