<?php

namespace App\Http\Requests;

// use Illuminate\Foundation\Http\FormRequest;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;


class LoginRequest extends FortifyLoginRequest
{
   
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メール形式で入力してください',
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは８文字以上で入力してください',
            'email.exists' => 'ログイン情報が登録されていません'
        ];
    }
}
