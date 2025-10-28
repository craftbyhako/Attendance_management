<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {

        $rules = [
            'user_name' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'string', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'min:8', 'string', 'confirmed'],
        ];

        $messages = [
            'user_name.required' => 'お名前を入力してください',
            'user_name.string' => 'お名前は文字で入力してください',
            'user_name.max' => 'お名前は２０文字以内で入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メール形式で入力してください',
            'email.string' => 'メールアドレスは文字で入力してください',
            'email.max' => 'メールアドレスは２５５文字以内で入力してください',
            'email.unique' => 'こちらのメールアドレスはすでに登録されています',
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは８文字以上で入力してください',
            'password.string' => 'パスワードは文字列で入力してください',
            'password.confirmed' => 'パスワードと一致しません',
        ];
        Validator::make($input, $rules, $messages)->validate();
    

        return User::create([
            'user_name' => $input['user_name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
